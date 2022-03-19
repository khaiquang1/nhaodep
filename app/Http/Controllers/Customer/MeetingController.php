<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\UserController;
use App\Repositories\CustomerRepository;
use App\Repositories\MeetingRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Efriandika\LaravelSettings\Facades\Settings;
use Yajra\Datatables\Datatables;

class MeetingController extends UserController
{
    /**
     * @var MeetingRepository
     */
    private $meetingRepository;

    private $userRepository;

    private $customerRepository;

    public function __construct(
        UserRepository $userRepository,
        CustomerRepository $customerRepository,
        MeetingRepository $meetingRepository
    )
    {

        parent::__construct();
        $this->userRepository = $userRepository;
        $this->customerRepository = $customerRepository;
        $this->meetingRepository = $meetingRepository;

        view()->share('type', 'customers/meeting');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $title = trans('meeting.meetings');

        return view('customers.meeting.index', compact('title'));
    }

    public function show($meeting){
        $meeting = $this->meetingRepository->getAll()->where('company_attendees',$this->userRepository->getUser()->id)->find($meeting);
        if (!isset($meeting)){
            abort(404);
        }
        $title = trans('meeting.show');
        $user = $this->userRepository->all();
        $action = 'show';
        return view('customers.meeting.show', compact('title', 'meeting','user','action'));
    }
    public function delete($meeting)
    {
        $meeting = $this->meetingRepository->getAll()->where('company_attendees',$this->userRepository->getUser()->id)->find($meeting);
        if (!isset($meeting)){
            abort(404);
        }
        $title = trans('meeting.delete');
        $user = $this->userRepository->all();
        return view('customers.meeting.delete', compact('title', 'meeting','user'));
    }

    public function destroy($meeting)
    {
        $meeting = $this->meetingRepository->find($meeting);
        $meeting->delete();
        return redirect('meeting');
    }

    public function data(Datatables $datatables)
    {
        $meetings = $this->meetingRepository->getAll()->where('company_attendees',$this->userRepository->getUser()->id)->get()
            ->map(function ($meeting) {
                return [
                    'id' => $meeting->id,
                    'meeting_subject' => $meeting->meeting_subject,
                    'starting_date' => $meeting->starting_date,
                    'ending_date' => $meeting->ending_date,
                    'responsible' => isset($meeting->responsible) ? $meeting->responsible->full_name : '',
                ];
            });
        return $datatables->collection($meetings)
            ->addColumn('actions', '
                                     @if(Sentinel::getUser()->hasAccess([\'meetings.delete\']) || Sentinel::inRole(\'admin\')|| Sentinel::inRole(\'customer\'))
                                     <a href="{{ url(\'customers/meeting/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                     @endif')
            ->removeColumn('id')
            ->rawColumns(['actions'])->make();
    }

    public function calendar()
    {
        $title = trans('meeting.meetings');
        return view('customers.meeting.calendar', compact('title', 'opportunity'));
    }

    public function calendar_data()
    {
        $dateTimeFormat = config('settings.date_format').' H:i';
        $events = [];
        $meetings = $this->meetingRepository->getAll()->where('company_attendees',$this->user->id)->get()
            ->map(function ($meeting) use ($dateTimeFormat){
                return [
                    'id' => $meeting->id,
                    'title' => $meeting->meeting_subject,
                    'start_date' => date($dateTimeFormat,strtotime($meeting->starting_date)),
                    'end_date' => date($dateTimeFormat,strtotime($meeting->ending_date)),
                    'type' => 'meeting'
                ];
            });
        foreach ($meetings as $d) {
            $event = [];
            $dateFormat = config('settings.date_format');
            $timeFormat = Settings::get('time_format');
            $start_date = Carbon::createFromFormat($dateFormat.' '.$timeFormat,$d['start_date'])->format('M d Y');
            $end_date = Carbon::createFromFormat($dateFormat.' '.$timeFormat,$d['end_date'])->addDay()->format('M d Y');
            $event['title'] = $d['title'];
            $event['id'] = $d['id'];
            $event['start'] = $start_date;
            $event['end'] = $end_date;
            $event['allDay'] = true;
            $event['description'] = $d['title'] . '&nbsp;<a href="' . url($d['type'] . '/' . $d['id'] . '/edit') . '" class="btn btn-sm btn-success"><i class="fa fa-pencil-square-o">&nbsp;</i></a>';
            array_push($events, $event);
        }
        return json_encode($events);
    }
}
