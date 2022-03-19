<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Http\Requests\OpportunityLostReason;
use App\Http\Requests\OpportunityRequest;
use App\Repositories\CallRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\OptionRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\UserRepository;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class OpportunityController extends UserController
{

    public $companyRepository;
    public $userRepository;
    /**
     * @var OpportunityRepository
     */
    private $opportunityRepository;
    /**
     * @var SalesTeamRepository
     */
    private $salesTeamRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    private $customerRepository;

    private $quotationRepository;

    private $callRepository;

    /**
     * OpportunityController constructor.
     * @param CompanyRepository $companyRepository
     * @param UserRepository $userRepository
     * @param OpportunityRepository $opportunityRepository
     * @param SalesTeamRepository $salesTeamRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(CompanyRepository $companyRepository,
                                UserRepository $userRepository,
                                OpportunityRepository $opportunityRepository,
                                SalesTeamRepository $salesTeamRepository,
                                OptionRepository $optionRepository,
                                CustomerRepository $customerRepository,
                                QuotationRepository $quotationRepository,
                                CallRepository $callRepository
    )
    {
        $this->middleware('authorized:opportunities.read', ['only' => ['index', 'data']]);
        $this->middleware('authorized:opportunities.write', ['only' => ['create', 'store', 'update', 'edit']]);
        $this->middleware('authorized:opportunities.delete', ['only' => ['delete']]);

        parent::__construct();

        $this->opportunityRepository = $opportunityRepository;
        $this->companyRepository = $companyRepository;
        $this->userRepository = $userRepository;
        $this->salesTeamRepository = $salesTeamRepository;
        $this->optionRepository = $optionRepository;
        $this->customerRepository = $customerRepository;
        $this->quotationRepository = $quotationRepository;
        $this->callRepository = $callRepository;

        view()->share('type', 'opportunity');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('opportunity.opportunities');
        return view('user.opportunity.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('opportunity.new');
        $calls = 0;
        $meetings = 0;
        $this->generateParams();
        if(!isset($user)){
            $user=array();
        }
        if(!isset($salesteam)){
            $salesteam=array();
        }
        return view('user.opportunity.create', compact('title', 'meetings', 'calls' , 'user','salesteam'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(OpportunityRequest $request)
    {
        $request->merge(['customer_id'=>$request->customer_id]);
        $this->opportunityRepository->create($request->all());
        return redirect("opportunity");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($opportunity)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $sales_team=array();
        $team_leader=array();
        $sales_team_members = "";
        $main_staff=array();
        $customer_company= $this->customerRepository->all()->where( 'company_id', $opportunity->company_name )->pluck( 'user_id', 'id' );
        $agent_name = $this->userRepository->all()->whereIn('id',$customer_company)->pluck('full_name','id')->all();
        if($opportunity->sales_team_id){
            $sales_team = $this->salesTeamRepository->find($opportunity->sales_team_id);
        }
        if(isset($sales_team) && $sales_team!="" && count($sales_team)>0){
            $team_leader = $this->userRepository->all()->where('id',$sales_team->team_leader)->pluck('full_name','id')->toArray();
            $sales_team_members = $sales_team->members->pluck('full_name','id')->toArray();
            $main_staff = $team_leader+$sales_team_members;
        }

        $calls = $opportunity->calls()->count();
        $meetings =  $opportunity->meetings()->count();

        $title = trans('opportunity.edit');

        $this->generateParams();
        return view('user.opportunity.edit', compact('title', 'calls', 'meetings', 'opportunity','agent_name','main_staff'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(OpportunityRequest $request, $opportunity)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $opportunity->update($request->all());
        return redirect("opportunity");
    }

    public function show($opportunity)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $title = trans('opportunity.show');
        $action = 'show';
        $this->generateParams();
        return view('user.opportunity.show', compact('title', 'opportunity','action'));
    }

    public function won($opportunity)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $title = trans('opportunity.won');
        $this->generateParams();
        $action = 'won';
        return view('user.opportunity.lost_won', compact('title', 'opportunity','action'));
    }

    public function lost($opportunity)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $title = trans('opportunity.lost');
        $this->generateParams();
        $action = 'lost';
        return view('user.opportunity.lost_won', compact('title', 'opportunity','action'));
    }

    public function updateLost(Request $request, $opportunity)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $request->merge([
            'stages' => 'Lost',
        ]);
        $opportunity->update($request->all());

        return redirect("opportunity");
    }

    public function delete($opportunity)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $title = trans('opportunity.delete');
        $this->generateParams();
        return view('user.opportunity.delete', compact('title', 'opportunity'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($opportunity)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
	    $opportunity->calls()->delete();
	    $opportunity->meetings()->delete();
        return redirect('opportunity');
    }

    public function data(Datatables $datatables)
    {
        $dateFormat = config('settings.date_format');
        $listUser=$this->userRepository->getAllStaffOfUser(0);
        
        $opportunities = $this->opportunityRepository->getAll()->whereIn('sales_person_id',$listUser)
            ->orderBy("id", "desc")
            ->with('salesTeam', 'customer', 'calls', 'meetings','user')
            ->get()
            ->map(function ($opportunity) use ($dateFormat){
                return [
                    'id' => $opportunity->id,
                    'opportunity' => $opportunity->opportunity,
                    'company' => isset($opportunity->companies->name)?$opportunity->companies->name:null,
                    'next_action' => date($dateFormat,strtotime($opportunity->next_action)),
                    'stages' => $opportunity->stages,
                    'expected_revenue' => $opportunity->expected_revenue,
                    'probability' => $opportunity->probability,
                    'source' => $opportunity->source,
                    'sales_team_id' => isset($opportunity->salesTeam) ? $opportunity->salesTeam->salesteam : '',
                    'salesteam' =>  isset($opportunity->staffs->full_name)?$opportunity->staffs->full_name:null,
                    'calls' => $opportunity->calls->count(),
                    'meetings' => $opportunity->meetings->count(),
                ];
            });
        
        return $datatables->collection($opportunities)
            ->addColumn('actions', ' 
 @if(Sentinel::getUser()->hasAccess([\'opportunities.write\']) || Sentinel::inRole(\'admin\'))
                                         <a href="{{ url(\'opportunity/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                                <i class="fa fa-fw fa-pencil text-warning "></i></a>                                     
                                       
                                      @endif
                                      <a href="{{ url(\'opportunitycall/\' . $id .\'/\' ) }}" title="{{ trans(\'table.calls\') }}">
                                                <i class="fa fa-phone text-primary"></i><sup>{{ $calls }}</sup> </a>
                                         <a href="{{ url(\'opportunitymeeting/\' . $id .\'/calendar\' ) }}" title="{{ trans(\'table.meeting\') }}">
                                                <i class="fa fa-fw fa-users text-primary"></i> <sup>{{ $meetings }}</sup></a>
                                      @if(Sentinel::getUser()->hasAccess([\'opportunities.read\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'opportunity/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                    @endif                                   
                                      @if(Sentinel::getUser()->hasAccess([\'opportunities.delete\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'opportunity/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i></a>
                                      @endif')
            ->addColumn('options', ' @if(Sentinel::getUser()->hasAccess([\'opportunities.write\']) || Sentinel::inRole(\'admin\'))
                                    
                                         <a href="{{ url(\'opportunity/\' . $id .\'/lost\' ) }}" class="btn btn-danger" title="{{ trans(\'opportunity.lost\') }}">
                                                Lost</a>
                                      @endif
                                       @if(Sentinel::getUser()->hasAccess([\'quotations.write\']) && Sentinel::getUser()->hasAccess([\'opportunities.write\']) || Sentinel::getUser()->inRole(\'admin\'))
                                       <a href="{{ url(\'opportunity/\' . $id .\'/won\' ) }}" class="btn btn-success" title="{{ trans(\'opportunity.won\') }}">
                                                Won</a>
                                       @endif
                                    ')
            ->removeColumn('calls')
            ->removeColumn('meetings')
            ->rawColumns(['actions','options'])->make();
    }



    private function generateParams()
    {
        $stages = $this->optionRepository->getAll()
            ->where('category', 'stages')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title', 'value')
            ->prepend(trans('dashboard.select_stage'), '');

        $priority = $this->optionRepository->getAll()
            ->where('category', 'priority')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title', 'value')
            ->prepend(trans('dashboard.select_priority'), '');
        $lost_reason = $this->optionRepository->getAll()->where('category','lost_reason')->pluck('title','value')
            ->prepend(trans('opportunity.lost_reason'),'');

        $agents = $this->userRepository->getCustomers()
                                          ->pluck('full_name', 'id')
                                          ->prepend(trans('dashboard.select_customer'), '');

        $staffs = $this->userRepository->getStaff()
	            ->pluck('full_name', 'id')->prepend(trans('dashboard.select_staff'), '');

        $salesteams = $this->salesTeamRepository->getAll()
                ->orderBy("id", "asc")
                ->pluck('salesteam', 'id')
	            ->prepend(trans('dashboard.select_sales_team'), '');
        $companies = $this->companyRepository->getAll()->orderBy("name", "asc")
            ->pluck('name', 'id')
            ->prepend(trans('company.company_name'), '');

        view()->share('salesteams', $salesteams);
        view()->share('stages', $stages);
        view()->share('priority', $priority);
        view()->share('lost_reason', $lost_reason);
        view()->share('staffs', $staffs);
        view()->share('agents', $agents);
        view()->share('companies', $companies);
    }

    public function convertToQuotation($opportunity)
    {
        $user = $this->userRepository->getUser();
        $opportunity = $this->opportunityRepository->find($opportunity);
        if (!$opportunity){
            abort(404);
        }
        $quotation = $this->quotationRepository->all()->count();;
        if($quotation == 0){
            $total_fields = 0;
        }else{
            $total_fields = $this->quotationRepository->all()->last()->id;
        }

        $start_number = Settings::get('quotation_start_number');
        $quotation_no = Settings::get('quotation_prefix') . (is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields : 0) + 1);
        $expected_closing_date=$opportunity->expected_closing_date;
        if($opportunity->expected_closing_date=="" || $opportunity->expected_closing_date==0){
            $expected_closing_date=date("Y-m-d H:i:s", strtotime("+30 days"));
        }
        $this->quotationRepository->create([
            'quotations_number' => $quotation_no,
            'customer_id' => $opportunity->customer_id,
            'date' => date("Y-m-d"),
            'exp_date' => $expected_closing_date,
            'payment_term' => Settings::get('payment_term1')." Days",
            'sales_person_id' => $opportunity->salesteam,
            'sales_team_id' => $opportunity->sales_team_id,
            'status' => trans('opportunity.quotation_status_draff'),
            'user_id' => $user->id,
            'discount' => 0,
            'opportunity_id' => $opportunity->id
        ]);
        
        $opportunity->update(['stages' => 'Won','is_converted_list' =>1]);
        return redirect('quotation/draft_quotations');
    }

//    convert to archive
    public function convertToArchive($opportunity, OpportunityLostReason $request){
        $opportunity = $this->opportunityRepository->find($opportunity);
        $opportunity->update(['stages' => 'Loss','is_archived' => 1,'lost_reason' => $request->lost_reason]);
        return redirect('opportunity_archive');
    }

    //    convert to delete list
    public function convertToDeleteList($opportunity){
        $opportunity = $this->opportunityRepository->find($opportunity);
        $this->generateParams();
        $opportunity->update(['is_delete_list' => 1]);
        return redirect('opportunity_delete_list');
    }
    public function ajaxAgentList( Request $request ) {
        $customers_list = $this->customerRepository->all()->where('company_id',$request->id);
        $agent_name=[];
        foreach ($customers_list as $customer){
            $agent_name[$customer->user->id]=$customer->user->full_name;
        }
        return $agent_name;
    }
    public function ajaxMainStaffList( Request $request){
        $userData=$this->userRepository->getUser();
        $this->partner_id=$userData->partner_id;
        $sales_team = $this->salesTeamRepository->find($request->id);
        $team_leader = $this->userRepository->find($sales_team->team_leader)->id;
        $sales_team_members = $this->salesTeamRepository->find($request->id);
        $sales_team_members = $sales_team_members->members->pluck('full_name','id')->toArray();
        $team_members = $this->userRepository->all()->where('partner_id',$this->partner_id)->where('id',$sales_team->team_leader)->pluck('full_name','id')->toArray();
        $main_staff = $sales_team_members + $team_members;
        return ['main_staff'=>$main_staff,'team_leader'=>$team_leader];
    }
}
