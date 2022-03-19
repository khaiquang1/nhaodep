<?php

namespace App\Http\Controllers\Users;

use App\Events\Call\CallCreated;
use App\Http\Controllers\UserController;
use App\Http\Requests\CallRequest;
use App\Models\Call;
use App\Models\LogsCall;
use App\Models\User;

use Illuminate\Pagination\Paginator;
use App\Repositories\CallRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\LeadRepository;
use App\Repositories\UserRepository;
use Yajra\Datatables\Datatables;
use Illuminate\Http\Request;


class CallController extends UserController
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var CallRepository
     */
    private $callRepository;
    /**
     * @var CompanyRepository
     */
    private $companyRepository;

    private $leadRepository;

    public function __construct(UserRepository $userRepository,
                                CallRepository $callRepository,
                                CompanyRepository $companyRepository,
                                LeadRepository $leadRepository
    )
    {
        parent::__construct();

        $this->middleware('authorized:logged_calls.read', ['only' => ['index', 'data']]);
        $this->middleware('authorized:logged_calls.write', ['only' => ['create', 'store', 'update', 'edit']]);
        $this->middleware('authorized:logged_calls.delete', ['only' => ['delete']]);

        $this->userRepository = $userRepository;
        $this->callRepository = $callRepository;
        $this->companyRepository = $companyRepository;
        $this->leadRepository = $leadRepository;

        view()->share('type', 'call');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('call.calls_title');
        $user=$this->userRepository->getUser();
        $user_id=$user->id;
        $this->partner_id=$user->partner_id;
        $date  = '';//addslashes($request->report_date) ;
        if($date!=""){
            $dateArray=explode("-",trim($date));
            $starting_date=$this->convertDate(trim($dateArray[0]));
            $ending_date=$this->convertDate(trim($dateArray[1]));
            $date_select=$date;
        }else{
            $starting_date=date("Y-m-d",strtotime('today - 30 days'));
            $ending_date=date("Y-m-d",strtotime('today +1 days'));
            $date_select=date("m/d/Y",strtotime('today - 30 days'))." - ".date("m/d/Y");
        }
        $listUser=$this->userRepository->getAllUserOnPartner($this->partner_id);
        $totalLead = $this->leadRepository->getAll()->where('partner_id', $this->partner_id)->where('created_at','>=', $starting_date)->where('created_at','<=', $ending_date)->count();
        /* $callsData= User::selectRaw('users.id, users.first_name, users.last_name, sum((TIME_TO_SEC(logs_call.end_time)-TIME_TO_SEC(logs_call.start_time))/60) as totalCallLead, (select count(id) as totalLeadUser from leads where status=1 and sales_person_id=users.id group by sales_person_id) as leadAccept, (select count(id) from lead_assign_status where (status=0 or status=2) and user_id=users.user_id group by user_id) as totalLeadAssignNotAccept, (select count(id) from lead_assign_status where user_id=users.id group by user_id) as totalLeadAssign, (select count(id) from leads where status in(2,3,4,5) and sales_person_id=users.id group by sales_person_id) as totalLeadFollow, (select COUNT(id) from lead_assign_status where status=1 and user_id=users.id and lead_id NOT IN (select lead_id from lead_assign_status where (status=0 or status=2) and user_id=users.id)) as totalLeadAcceptResponTime, (select count(id) from logs_call where user_id=users.id and lead_id>0 and (status=1 or status=2) group by user_id) as totalTimeCall, (select count(id) as totalLead from leads where sales_person_id=users.id group by sales_person_id) as totalLead') 
                            ->leftJoin('logs_call','users.id','=','logs_call.user_id')
                            ->leftJoin('leads','users.id','=','logs_call.user_id')
                            ->whereIn('users.id',$listUser)
                            ->where('logs_call.date_create','>=', $starting_date)
                            ->where('logs_call.date_create','<=', $ending_date)
                            ->groupBy('users.id')->get();  */
        $callsData= User::selectRaw('users.id, users.first_name, users.last_name, (select count(id) as totalLeadUser from leads where status=1 and sales_person_id=users.id group by sales_person_id) as leadAccept, (select count(id) from lead_assign_status where (status=0 or status=2) and user_id=users.user_id group by user_id) as totalLeadAssignNotAccept, (select count(id) from lead_assign_status where user_id=users.id group by user_id) as totalLeadAssign, (select count(id) from leads where status in(2,3,4,5) and sales_person_id=users.id group by sales_person_id) as totalLeadFollow, (select COUNT(id) from lead_assign_status where status=1 and user_id=users.id and lead_id NOT IN (select lead_id from lead_assign_status where (status=0 or status=2) and user_id=users.id)) as totalLeadAcceptResponTime, (select count(id) from logs_call where user_id=users.id and lead_id>0 and (status=1 or status=2) group by user_id) as totalTimeCall, (select count(id) as totalLead from leads where sales_person_id=users.id group by sales_person_id) as totalLead') 
                            ->whereIn('users.id',$listUser)
                            ->groupBy('users.id')->get(); 
        return view('user.call.index', compact('title', 'callsData'));
    }

         

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('call.new');

        $this->generateParams();

        return view('user.call.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(CallRequest $request)
    {
        $call = $this->callRepository->create($request->all());

        event(new CallCreated($call));

        return redirect("call");
    }

    public function edit($call)
    {
        $call = $this->callRepository->find($call);
        $title = trans('call.edit');

        $this->generateParams();

        return view('user.call.create', compact('title', 'call'));
    }

    public function update(CallRequest $request, $call)
    {
        $call = $this->callRepository->find($call);
        $call->update($request->all());

        return redirect("call");
    }


    public function show($call)
    {
        $call = $this->callRepository->find($call);
        $title = trans('call.show');
        $this->generateParams();
        $action = "show";
        return view('user.call.show', compact('title', 'call','action'));
    }

    public function delete($call)
    {
        $call = $this->callRepository->find($call);
        $title = trans('call.delete');
        $this->generateParams();
        return view('user.call.delete', compact('title', 'call'));
    }

    public function destroy($call)
    {
        $call = $this->callRepository->find($call);
        $call->delete();
        return redirect('call');
    }

    public function data(Datatables $datatables)
    {
        $user=$this->userRepository->getUser();
        $user_id=$user->id;
        $this->partner_id=$user->partner_id;
        $listUser=$this->userRepository->getAllUserOnPartner($this->partner_id);
        $callsData = LogsCall::selectRaw('logs_call.*, users.first_name, users.last_name, leads.opportunity')
                            ->join('leads','leads.id','=','logs_call.lead_id')
                            ->leftJoin('users','users.id','=','logs_call.user_id')
                            ->whereIn('logs_call.user_id',$listUser)
                            ->orderBy("logs_call.id", "desc")->get(); 
        $calls = $callsData->map(function ($call){
                return [
                    'id' => $call->id,
                    'phone' => $call->phone,
                    'date' => $call->date_create,
                    'time_start' => $call->start_time,
                    'time_end' => $call->end_time,
                    'salesperson' => $call->salesperson,
                    'lead_name' => $call->opportunity,
                    'salesperson'=>"<a href='{{ url(\'staff/\' . $call->user_id . \'/dashboard\' ) }}'>".$call->first_name." ".$call->last_name."</a>",
                ];
            });
        return $datatables->collection($calls)      
            ->removeColumn('id')
            ->make();
    }
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function user($call, Request $request)
    {
        $userid=$call;
        $sales=$this->userRepository->findByField('id',$userid)->first();
        $fullname=$sales["first_name"]." ".$sales["last_name"];

        $title = trans('call.calls_of_user');
        $starting_date  = addslashes($request->starting_date) ;
        $ending_date = addslashes($request->ending_date) ;
        return view('user.call.user_call_list', compact('title', 'userid', 'fullname', 'starting_date', 'ending_date'));
    }
    //Show data
    public function dataUser($userid, Datatables $datatables, Request $request)
    {
        if($userid>0){
            $starting_date  = addslashes($request->starting_date) ;
            $ending_date = addslashes($request->ending_date) ;
            $calls = Call::select('calls.*', 'leads.opportunity', 'leads.id as lead_id')
            ->leftJoin('callables','callables.call_id','=','calls.id')
            ->leftJoin('leads','leads.id','=','callables.callable_id')
            ->where('calls.resp_staff_id','=',$userid)
            ->where(function ($query)  use ($starting_date,$ending_date){
                    if($starting_date!=""){
                        $query->where('calls.date','>=',$starting_date);
                    }
                    if($ending_date!=""){
                        $query->where('calls.date','<=',$ending_date);
                    }
            })
			->orderBy('calls.id', 'DESC')
            ->get()
            ->map( function ( $call ){
                    return [
                        'id' => $call->id,
                        'date' => $call->date,
                        'lead_name' => $call->opportunity,
                        'lead_id' => $call->lead_id,
                        'call_summary' => $call->call_summary,
                        'duration' => $call->duration,
                        'user' => isset($call->resp_staff) ? $call->resp_staff->full_name : '',
                    ];
			    }
            );
            return $datatables->collection($calls)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'logged_calls.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'call/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning "></i> </a>
                                     @endif
                                     <a href="{{ url(\'call/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.show\') }}">
                                            <i class="fa fa-fw fa-eye text-primary "></i> </a>
                                     @if(Sentinel::getUser()->hasAccess([\'logged_calls.delete\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'call/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                     @endif')
            ->addColumn('lead_name', '
                                  <a href="{{ url(\'lead/\' . $lead_id . \'/show\' ) }}" title="{{ trans(\'table.show\') }}">
                                  {{$lead_name}}
                                  </a>')
            ->removeColumn('id')
            ->rawColumns([ 'actions', 'lead_name'])
            ->make();
        }else{
            return null;
        }
        
    }

    private function generateParams()
    {
        $companies = $this->companyRepository->getAll()->orderBy("name", "asc")
	            ->pluck('name', 'id')->prepend(trans('dashboard.select_company'), '');

        $staffs =$this->userRepository->getStaff()
	            ->pluck('full_name', 'id')->prepend(trans('dashboard.select_staff'), '');

        view()->share('staffs', $staffs);
        view()->share('companies', $companies);
    }

}
