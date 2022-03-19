<?php

namespace App\Listeners\Email;

use App\Events\Email\EmailSentEvent;
use App\Mail\Mailbox;
use App\Repositories\EmailRepository;
use App\Repositories\EmailTemplateRepository;
use App\Repositories\UserRepository;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Pusher\Pusher;
use Settings;

class EmailSentListener
{
    private $userRepository;

    private $emailTemplateRepository;

    private $emailRepository;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        UserRepository $userRepository,
        EmailTemplateRepository $emailTemplateRepository,
        EmailRepository $emailRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->emailTemplateRepository = $emailTemplateRepository;
        $this->emailRepository = $emailRepository;
    }

    /**
     * Handle the event.
     *
     * @param  EmailSentEvent  $event
     * @return void
     */
    public function handle(EmailSentEvent $event)
    {
        $email = $this->emailRepository->find($event->email);
        $user = $this->userRepository->find($email->to);
        $userFrom = $this->userRepository->find($email->from);

        if (!filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
            Mail::to($user->email)->send(new Mailbox([
                'from' => $userFrom->email,
                'subject' => $email->subject,
                'message' => $email->message,
                'userFrom' => $userFrom,
            ]));
        }
    }
}
