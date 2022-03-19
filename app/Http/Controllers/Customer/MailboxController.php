<?php

namespace App\Http\Controllers\Customer;

use App\Events\Email\EmailSentEvent;
use App\Http\Controllers\UserController;
use App\Http\Requests\MailboxRequest;
use App\Mail\Mailbox;
use App\Repositories\EmailRepository;
use App\Repositories\EmailTemplateRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailboxController extends UserController
{

    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var EmailTemplateRepository
     */
    private $emailTemplateRepository;

    private $emailRepository;

    /**
     * @param UserRepository $userRepository
     * @param EmailTemplateRepository $emailTemplateRepository
     * @internal param CompanyRepository $
     */
    public function __construct(
        UserRepository $userRepository,
        EmailTemplateRepository $emailTemplateRepository,
        EmailRepository $emailRepository
    )
    {
        parent::__construct();

        $this->userRepository = $userRepository;
        $this->emailTemplateRepository = $emailTemplateRepository;
        $this->emailRepository = $emailRepository;

        view()->share('type', 'mailbox');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('mailbox.mailbox');


        return view('customers.mailbox.index', compact('title'));
    }

    public function getAllData()
    {
        $email_list = $this->emailRepository->getAll()->where('to', $this->user->id)->where('delete_receiver',0)->orderBy('id','desc')->get();
        $sent_email_list = $this->emailRepository->getAll()->where('from', $this->user->id)->where('delete_sender',0)->orderBy('id','desc')->get();

        $users = $this->userRepository->getUsersAndStaffs()
	        ->map(function ($user) {
		        return [
			        'id'   => $user->id,
			        'text' => $user->full_name.' ('. $user->email.')',
		        ];
	        })->values();

        $orig_users_list = $this->userRepository->getUsersAndStaffs()
            ->map(function ($user) {
                return [
                    'full_name' => $user->full_name.' ('. $user->email.')',
                    'user_avatar' => $user->user_avatar,
                    'active' => (isset($user->last_login) && $user->last_login >= Carbon::now()->subMinutes('15')->toDateTimeString()) ? '1' : '0',
                ];
            });
            $users_list=[];
        foreach($orig_users_list as $newuser)
        {
            $users_list[]=$newuser;
        }
        $have_email_template = false;
        return response()->json(compact('email_list', 'sent_email_list', 'users','users_list','have_email_template'), 200);
    }


    public function getMail($id)
    {
        $email = $this->emailRepository->getAll()->with('sender')->find($id);
        $email->read = 1;
        $email->save();

        return response()->json(compact('email'), 200);
    }

    public function getSentMail($id)
    {
        $email = $this->emailRepository->getAll()->with('receiver')->find($id);
        $email->save();

        return response()->json(compact('email'), 200);
    }

    public function getMailTemplate($id)
    {
        $template = $this->emailTemplateRepository->find($id);

        return response()->json(compact('template'), 200);
    }

    function sendEmail(MailboxRequest $request)
    {
        $message_return = '<div class="alert alert-danger">' . trans('mailbox.danger') . '</div>';
        if (!empty($request->recipients)) {
            foreach ($request->recipients as $item) {
                if ($item != "0" && $item != "") {
                    $email = $this->emailRepository->create($request->except('recipients','emailTemplate'));
                    $email->to = $item;
                    $email->from = $this->user->id;
                    $email->save();
                    event(new EmailSentEvent($email->id));
                    $message_return = '<div class="alert alert-success">' . trans('mailbox.success') . '</div>';
                }

            }
        }
        echo $message_return;

    }

    function deleteMail($mail)
    {
        $mail = $this->emailRepository->find($mail);
        if($mail->to == $this->user->id){
            $mail->delete_receiver= 1;
        }
        else{
            $mail->delete_sender= 1;
        }
        $mail->save();
    }


    public function postRead(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
        ]);

        $model = $this->emailRepository->find($request->get('id'));
        $model->read = true;
        $model->save();

        return response()->json(['message' => trans('mailbox.update_status')], 200);
    }


    public function getData()
    {
        $emails_list = $this->emailRepository->getAll()->where('to', $this->user->id)
	        ->where('delete_receiver',0)
            ->where('read', 0)
            ->with('sender')
            ->orderBy('id', 'desc');

        $total = $emails_list->count();
        $emails = $emails_list->latest()->take(5)->get();

        return response()->json(compact('total', 'emails'), 200);
    }

    public function postMarkAsRead(Request $request)
    {
        if ($ids = $request->get('ids')) {
            if (is_array($ids)) {
                $messages = $this->emailRepository->getAll()->whereIn('id', $ids)->get();
                foreach ($messages as $message) {
                    $message->read = true;
                    $message->save();
                }
            } else {
                $message = $this->emailRepository->getAll()->find($ids);
                $message->read = true;
                $message->save();
            }
        }
    }

    public function getSent()
    {

        $sent = $this->emailRepository->getAll()->where('from', $this->user->id)
	        ->where('delete_sender',0)
            ->with('receiver')
            ->orderBy('id', 'desc')->get();

        return response()->json(compact('sent'), 200);
    }

    public function getReceived(Request $request)
    {
        $received_list = $this->emailRepository->getAll()->where('to', $this->user->id)
            ->where('delete_receiver',0)
            ->where('subject', 'like', '%' . $request->get('query', '') . '%')
            ->where('message', 'like', '%' . $request->get('query', '') . '%')
            ->with('sender');
        $received = $received_list->orderBy('id', 'desc')->get();
        $received_count = $received_list->count();
        return response()->json(compact('received','received_count'), 200);
    }

    public function postDelete(Request $request)
    {
        if ($ids = $request->get('ids')) {
            if (is_array($ids)) {
                $messages = $this->emailRepository->getAll()->whereIn('id', $ids)->get();
                foreach ($messages as $message) {
                    $message->delete_receiver = 1;
                    $message->save();
                }
            } else {
                $message = $this->emailRepository->getAll()->find($ids);
                $message->delete_sender = 1;
                $message->save();
            }
        }
    }

    public function postReply($id, Request $request)
    {
        $orgMail = $this->emailRepository->find($id);
        $subject = ('Re:' === substr($orgMail->subject, 0, strlen('Re:')))
            ?
            ($orgMail->subject)
            :
            ('Re: '.$orgMail->subject);
        $request->merge([
            'subject' => $subject,
        ]);

        $email = $this->emailRepository->create($request->all());
        $email->to = $orgMail->from;
        $email->from = $this->userRepository->getUser()->id;
        $email->save();

        event(new EmailSentEvent($email->id));

    }

}
