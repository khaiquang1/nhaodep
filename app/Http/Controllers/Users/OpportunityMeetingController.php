<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Http\Requests\MeetingRequest;
use App\Repositories\CompanyRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\MeetingRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\OptionRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\UserRepository;
use Yajra\Datatables\Datatables;

class OpportunityMeetingController extends UserController
{
    /**
     * @var MeetingRepository
     */
    private $meetingRepository;
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    private $opportunityRepository;

    private $customerRepository;

    private $salesTeamRepository;

	/**
     * OpportunityMeetingController constructor.
     * @param MeetingRepository $meetingRepository
     * @param CompanyRepository $companyRepository
     * @param UserRepository $userRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(MeetingRepository $meetingRepository,
                                CompanyRepository $companyRepository,
                                UserRepository $userRepository,
                                OptionRepository $optionRepository,
                                OpportunityRepository $opportunityRepository,
                                CustomerRepository $customerRepository,
                                SalesTeamRepository $salesTeamRepository
    )
    {
        parent::__construct();

        $this->meetingRepository = $meetingRepository;
        $this->companyRepository = $companyRepository;
        $this->userRepository = $userRepository;
        $this->optionRepository = $optionRepository;
        $this->opportunityRepository = $opportunityRepository;
        $this->customerRepository = $customerRepository;
        $this->salesTeamRepository = $salesTeamRepository;

        view()->share('type', 'opportunitymeeting');

    }

    public function index($opportunity)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $title = trans('meeting.opportunity_meetings');
        return view('user.opportunitymeeting.index', compact('title', 'opportunity'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($opportunity)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $title = trans('meeting.opportunity_new');

        $this->generateParams();
        $this->companyAttendees($opportunity);
        return view('user.opportunitymeeting.create', compact('title', 'opportunity'));
    }

    public function store($opportunity, MeetingRequest $request)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $company_attendees = implode(',',$request->company_attendees);
        if(isset($request->staff_attendees)){
            $staff_attendees = implode(',',$request->staff_attendees);
            $request->merge(['staff_attendees'=>$staff_attendees]);
        }
        $request->merge(['company_attendees'=>$company_attendees,'company_name'=>$opportunity->company_name]);
        $user = $this->userRepository->getUser();
        $opportunity->meetings()->create($request->all(), ['user_id' => $user->id]);

        return redirect("opportunitymeeting/" . $opportunity->id);
    }

    public function edit($opportunity, $meeting)
    {
        $meeting = $this->meetingRepository->find($meeting);
        $opportunity = $this->opportunityRepository->find($opportunity);
        $title = trans('meeting.opportunity_edit');
        $this->generateParams();
        $this->companyAttendees($opportunity);
        $company_attendees = $this->userRepository->all()->whereIn('id',explode(',',$meeting->company_attendees))->pluck('id','id')->all();
        $staff_attendees = explode(',', $meeting->staff_attendees);
        $staff_attendees = $this->userRepository->all()->whereIn('id', $staff_attendees)->pluck('id', 'id')->all();
        return view('user.opportunitymeeting.create', compact('title', 'meeting', 'opportunity','staff_attendees','company_attendees'));
    }

    public function update(MeetingRequest $request, $opportunity, $meeting)
    {
        $meeting = $this->meetingRepository->find($meeting);
        $opportunity = $this->opportunityRepository->find($opportunity);
        $company_attendees = implode(',',$request->company_attendees);
        if(isset($request->staff_attendees)){
            $staff_attendees = implode(',',$request->staff_attendees);
            $request->merge(['staff_attendees'=>$staff_attendees]);
        }
        $request->merge(['company_attendees'=>$company_attendees,'staff_attendees'=>$request->staff_attendees,'company_name'=>$opportunity->company_name]);
        $meeting->all_day = ($request->all_day) ? $request->all_day : 0;
        $meeting->update($request->all());

        return redirect("opportunitymeeting/" . $opportunity->id);
    }


    public function delete($opportunity, $meeting)
    {
        $meeting = $this->meetingRepository->find($meeting);
        $opportunity = $this->opportunityRepository->find($opportunity);
        $title = trans('meeting.opportunity_delete');
        $this->generateParams();
        $this->companyAttendees($opportunity);
        return view('user.opportunitymeeting.delete', compact('title', 'meeting', 'opportunity'));
    }

    public function destroy($opportunity, $meeting)
    {
        $meeting = $this->meetingRepository->find($meeting);
        $opportunity = $this->opportunityRepository->find($opportunity);
        $meeting->delete();
        return redirect('opportunitymeeting/' . $opportunity->id);
    }

    public function data($opportunity,Datatables $datatables)
    {
        $dateTimeFormat = config('settings.date_time_format');
        $opportunity = $this->opportunityRepository->find($opportunity);
        $user = $this->userRepository->getUser();
        $meetings = $opportunity->meetings()
            ->with('responsible')
            ->get()
            ->filter(function ($meeting) use ($user){
                return ($meeting->privacy=='Everyone' ||
                    ($meeting->privacy=='Main Staff' && $meeting->responsible_id==$user->id)
                    || $meeting->user_id == $user->id);
            })
            ->map(function ($meeting) use ($opportunity,$dateTimeFormat){
                $companies = $this->companyRepository->getAll()->orderBy("name", "asc")
                    ->pluck('name', 'id')
                    ->prepend(trans('company.company_name'), '');
                return [
                    'id' => $meeting->id,
                    'meeting_subject' => $meeting->meeting_subject,
                    'company_name' => $companies[$opportunity->company_name],
                    'starting_date' => date($dateTimeFormat,strtotime($meeting->starting_date)),
                    'ending_date' => date($dateTimeFormat,strtotime($meeting->ending_date)),
                    'meeting_type_id' => $opportunity->id,
                    'responsible' => isset($meeting->responsible) ? $meeting->responsible->full_name : 'N/A'
                ];
            });

        return $datatables->collection($meetings)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'meetings.write\']) || Sentinel::inRole(\'admin\'))
<a href="{{ url(\'opportunitymeeting/\' . $meeting_type_id . \'/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning "></i> </a>
                                            @endif
                                            @if(Sentinel::getUser()->hasAccess([\'meetings.delete\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'opportunitymeeting/\' . $meeting_type_id . \'/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                            @endif')
            ->removeColumn('id')
            ->removeColumn('meeting_type_id')
            ->rawColumns(['actions'])->make();
    }

    private function generateParams()
    {
        $companies = $this->companyRepository->getAll()->orderBy("name", "asc")->pluck('name', 'id');

        $staffs = $this->userRepository->getParentStaff()
	            ->pluck('full_name', 'id');

        $privacy = $this->optionRepository->getAll()
            ->where('category', 'privacy')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title', 'value');

        $show_times = $this->optionRepository->getAll()
            ->where('category', 'show_times')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title', 'value');

        view()->share('show_times', $show_times);
        view()->share('privacy', $privacy);
        view()->share('companies', $companies);
        view()->share('staffs', $staffs);
    }
    public function companyAttendees($opportunity)
    {
        $customers = $this->customerRepository->all()->where('company_id',$opportunity->company_name)->pluck('user_id','id');
        $company_customer = [];
        foreach ($customers as $customer){
            $company_customer[]=$customer;
        }
        $company_customer = $this->userRepository->all()->whereIn('id',$company_customer)->pluck('full_name','id');

        $sales_team = $this->salesTeamRepository->all()->where('id',$opportunity->sales_team_id)->pluck('team_leader','id');
        $sales_team_members = $this->salesTeamRepository->all()->where('id',$opportunity->sales_team_id)->pluck('team_members','id');
        $sales_team_members = $sales_team_members[$opportunity->sales_team_id];
        $sales_team_members = $sales_team->merge($sales_team_members);
        $main_staff = $this->userRepository->all()->whereIn('id',$sales_team_members)->pluck('full_name','id')->prepend(trans('salesteam.main_staff'),'');

        $staffs = $this->userRepository->getParentStaff()->pluck('full_name', 'id')->prepend(trans('salesteam.team_leader'), '');

        view()->share('company_customer',$company_customer);
        view()->share('sales_team',$sales_team);
        view()->share('main_staff',$main_staff);
        view()->share('staffs',$staffs);
    }

    public function calendar($opportunity)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $title = trans('meeting.opportunity_meetings');
        return view('user.opportunitymeeting.calendar', compact('title', 'opportunity'));
    }

    public function calendar_data($opportunity)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $user = $this->userRepository->getUser();
        $events = array();
        $meetings = $opportunity->meetings()
            ->with('responsible')
            ->get()
            ->filter(function ($meeting) use ($user) {
                return ($meeting->privacy=='Everyone' ||
                    ($meeting->privacy=='Main Staff' && $meeting->responsible_id==$user->id)
                    || $meeting->user_id == $user->id);
            })
            ->map(function ($meeting) use ($opportunity) {
                return [
                    'id' => $meeting->id,
                    'title' => $meeting->meeting_subject,
                    'start_date' => $meeting->starting_date,
                    'end_date' => $meeting->ending_date,
                    'meeting_type_id' => $opportunity->id,
                    'type' => 'meeting'
                ];
            });

        foreach ($meetings as $d) {
            $event = [];
            $start_date = date('Y-m-d', (is_numeric($d['start_date']) ? $d['start_date'] : strtotime($d['start_date'])));
            $end_date = date('Y-m-d', (is_numeric($d['end_date']) ? $d['end_date'] : strtotime($d['end_date']. ' +1 day')));
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
