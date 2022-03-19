<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Thumbnail;
use App\Http\Controllers\UserController;
use App\Http\Requests\InviteRequest;
use App\Http\Requests\StaffRequest;
use App\Models\Logs;
use App\Models\LeadAssignStatus;
use App\Models\User;
use App\Models\Partner;
use App\Models\PartnerUser;
use App\Models\Product;
use App\Models\GroupUserStaff;
use App\Models\GroupUser;
use App\Mail\InviteStaff;
use App\Repositories\InviteUserRepository;
use App\Repositories\UserRepository;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Yajra\Datatables\Datatables;
use App\Repositories\CallRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\ContractRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\LeadRepository;
use App\Repositories\MeetingRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\OptionRepository;
use App\Repositories\ProductRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\SalesOrderRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\TaskRepository;
use App\Models\Branch;
use App\Models\Tag;
use App\Models\LeadTags;
use App\Models\Lead;
use App\Models\Task;
use App\Models\Chatbox;
use App\Models\Leadmap;
use App\Models\Getdata;
use App\Models\UserPermission;
use App\Models\CallActionStatus;
use App\Models\ReportStaff;
use App\Models\LeadRouting;
use App\Models\ReportTags;
use App\Models\Salesteam;
use App\Models\SalesteamMember;



use Cache;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;
use Sentinel;

class ReportController extends UserController
{
    private $date_format = 'Y-m-d';
    private $emailSettings;
    private $siteNameSettings;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var InviteUserRepository
     */
    private $inviteUserRepository;
/**
     * @var LeadRepository
     */
    private $leadRepository;
    /**
     * @var OpportunityRepository
     */
    private $opportunityRepository;
    /**
     * @var CallRepository
     */
    private $callRepository;
    /**
     * @var MeetingRepository
     */
    private $meetingRepository;
    /**
     * @var QuotationRepository
     */
    private $quotationRepository;
    /**
     * @var SalesOrderRepository
     */
    private $salesOrderRepository;
    /**
     * @var ContractRepository
     */
    private $contractRepository;
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    /**
     * @var SalesTeamRepository
     */
    private $salesTeamRepository;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;
    private $taskRepository;
    /**
     * StaffController constructor.
     * @param UserRepository $userRepository
     * @param InviteUserRepository $inviteUserRepository
     */
    public function __construct(UserRepository $userRepository,
            InviteUserRepository $inviteUserRepository, LeadRepository $leadRepository,
            OpportunityRepository $opportunityRepository,
            CallRepository $callRepository,
            MeetingRepository $meetingRepository,
            QuotationRepository $quotationRepository,
            SalesOrderRepository $salesOrderRepository,
            ContractRepository $contractRepository,
            CompanyRepository $companyRepository,
            SalesTeamRepository $salesTeamRepository,
            ProductRepository $productRepository,
            InvoiceRepository $invoiceRepository,
            OptionRepository $optionRepository,
            TaskRepository $taskRepository)
    {


        $this->middleware('authorized:staff.read', ['only' => ['index', 'data']]);
        $this->middleware('authorized:staff.write', ['only' => ['create', 'store', 'update', 'edit']]);
        $this->middleware('authorized:staff.delete', ['only' => ['delete']]);

        parent::__construct();
        $this->inviteUserRepository = $inviteUserRepository;
        $this->leadRepository = $leadRepository;
        $this->opportunityRepository = $opportunityRepository;
        $this->userRepository = $userRepository;
        $this->callRepository = $callRepository;
        $this->meetingRepository = $meetingRepository;
        $this->quotationRepository = $quotationRepository;
        $this->salesOrderRepository = $salesOrderRepository;
        $this->contractRepository = $contractRepository;
        $this->companyRepository = $companyRepository;
        $this->salesTeamRepository = $salesTeamRepository;
        $this->productRepository = $productRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->optionRepository = $optionRepository;
        $this->taskRepository = $taskRepository;

        $this->date_format = config('settings.date_format');
        $this->emailSettings = Settings::get('site_email');
        $this->siteNameSettings = Settings::get('site_name');
       
        view()->share('type', 'staff');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(StaffRequest $request)
    {
        $title = trans('staff.staffs');
        $dateFormat = config('settings.date_format');
        $partner_id=0;
        if($request->partner_id){
            $partner_id=$request->partner_id;
        }
        $listUser="";
        $userData=$this->userRepository->getUser();
        $user_id=$userData->id;
        /*
        $listUserSales=$this->userRepository->getAllStaffOfUser($userData->id);
        
        if($user_id<=0 || $userData->partner_id==1){
            $listUser=$this->userRepository->getAllStaffOfUser($user_id);
        }else{
            if( $userData->partner_id>0){
                $listUserSales=$this->userRepository->getAllUserOnPartner($userData->partner_id);
                //$listUserParent=$this->userRepository->getAllStaffOfUser($user_id);
                if(isset($listUserSales) && count($listUserSales)>0){
                    //$listUser = array_unique (array_merge ($listUserParent, $listUserSales));
                    $listUser = array_unique ($listUserSales);

                }
                $partner_id=$userData->partner_id;
            }else{
                $listUser=null;
            }
        } */
        $listUser=$this->userRepository->getAllStaffOfUser($userData->id);

        $partnerList =null;
        $users=null;
        $staffData=null;
       if($listUser){
            $staffsQuery = User::select('users.*', 'partner.name as partner_name', 'partner_user.id as partner_user_id')
            ->join('partner_user','users.id','=','partner_user.user_id')
            ->join('partner','partner.id','=','partner_user.partner_id')
            ->where(function ($query)  use ($partner_id){
                if($partner_id!=0){
                    $query->where('partner_user.partner_id','=',$partner_id);
                }
            })
           ->whereIn('users.user_id',$listUser)->groupBy('users.id')
            ->orderBy('users.id', 'DESC');
            
            $totalUser=$staffsQuery->count();
            $staffData=$staffsQuery->paginate(15)->appends(request()->query());
            if($userData->user_id<=0){
                $partnerList =Partner::where('status','=',1)->orderBy("id", "asc")
                ->pluck('name', 'id')->prepend(trans('staff.select_partner'), '');
            }
            $users=$staffData->map( function ( $staff) use ($dateFormat){
                return [
                    'id'           => $staff->id,
                    'full_name'   => $staff->full_name,
                    'partner'   => $staff->partner_name,
                    'email' => $staff->email,
                    'created_at' => $staff->created_at,
                    'partner_user_id'=> $staff->partner_user_id,
                ];
            });
        }
        return view('user.staff.index', compact('title', 'users', 'staffData', 'partnerList'));
    }

    public function tags(Request $request){
        $title="Báo cáo Thẻ được tạo";
        $date  = addslashes($request->starting_date);
        if($date!=""){
			$dateArray=explode("-",trim($date));
			$starting_date=$this->convertDate(trim($dateArray[0]));
			$ending_date=$this->convertDate(trim($dateArray[1]));
			$starting_date=date("Y-m-d 00:01:00", strtotime($starting_date));
			$ending_date=date("Y-m-d 23:59:00", strtotime($ending_date));
			
			$date_select=$date;
		}else{
			$starting_date=date("Y-m-d",strtotime('today - 30 days'));
			$ending_date=date("Y-m-d",strtotime('today +1 days'));
			$date_select=date("m/d/Y",strtotime('today - 30 days'))." - ".date("m/d/Y");
        }
        
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $totalTag=Tag::where('partner_id',$partner_id)
       /* ->where(function ($query)  use ($starting_date, $ending_date){
            if($starting_date!=""){
                $query->where('created_at','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('created_at','<=',$ending_date);
            }
        }) */
        ->count();

        $totalTagAdd=LeadTags::where('partner_id',$partner_id)
        ->where(function ($query)  use ($starting_date, $ending_date){
            if($starting_date!=""){
                $query->where('created_at','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('created_at','<=',$ending_date);
            }
        })
        ->count();
        
        $totalTagGroup=Tag::select('tags.id', 'tags.title', 'tags.color_bg', 'tags.color_text', DB::raw('count(lead_tags.tag_id) as totalTagsGroup'))->join('lead_tags', 'lead_tags.tag_id', '=', 'tags.id')->where('tags.partner_id',$partner_id)
        ->where(function ($query)  use ($starting_date, $ending_date){
            if($starting_date!=""){
                $query->where('lead_tags.created_at','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('lead_tags.created_at','<=',$ending_date);
            }
        })->groupBy('tags.id')->get()->toArray();

        $tagDetail=Tag::select('tags.id', 'tags.title', 'tags.color_bg', 'tags.color_text', DB::raw('count(lead_tags.tag_id) as tagOnDay'), DB::raw('DATE_FORMAT(lead_tags.created_at, "%Y-%m-%d") as createdate'))->join('lead_tags', 'lead_tags.tag_id', '=', 'tags.id')->where('tags.partner_id',$partner_id)
        ->where(function ($query)  use ($starting_date, $ending_date){
            if($starting_date!=""){
                $query->where('lead_tags.created_at','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('lead_tags.created_at','<=',$ending_date);
            }
        })->groupBy('createdate', 'tags.id')->get()->toArray();
        $listNumberDate=null;
        if($tagDetail){
            foreach($tagDetail as $listData){
                $listNumberDate[$listData["id"]][$listData["createdate"]]=$listData["tagOnDay"];
            }
        }
        /*
        $tagDetail=$totalTagGroup->map( function ( $totalTagGroupData) use ($starting_date, $ending_date){
            return [
                'id'=> $totalTagGroupData->id,
                'number_tags'=>LeadTags::select(DB::raw('count(lead_tags.tag_id) as tagOnDay'), DB::raw('DATE_FORMAT(lead_tags.created_at, "%Y-%m-%d") as createdate'))->where('lead_tags.created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->groupBy('createdate', 'lead_tags.tag_id')->get(),
            ];
        }); */
        $dateNumber = (int)(abs(strtotime($ending_date) - strtotime($starting_date))/(60*60*24));
        return view( 'user.report.tags', compact( 'title', 'totalTag', 'totalTagGroup', 'totalTagAdd', 'tagDetail', 'starting_date', 'ending_date', 'dateNumber', 'listNumberDate') );

    }

    public function staff(Request $request){
        $title="Báo cáo nhân viên";
        $date  = addslashes($request->starting_date);
        if($date!=""){
			$dateArray=explode("-",trim($date));
			$starting_date=$this->convertDate(trim($dateArray[0]));
			$ending_date=$this->convertDate(trim($dateArray[1]));
			$starting_date=date("Y-m-d 00:01:00", strtotime($starting_date));
			$ending_date=date("Y-m-d 23:59:00", strtotime($ending_date));
			$date_select=$date;
		}else{
            //$starting_date=date("Y-m-d",strtotime('today - 30 days'));
            $starting_date=date("Y-m-d");
			$ending_date=date("Y-m-d",strtotime('today +30 days'));
			$date_select=date("m/d/Y",strtotime('today +30 days'))." - ".date("m/d/Y");
        }
        $daterange  = addslashes($request->daterange);
        if(isset($daterange) && $daterange!="" && $daterange!=0){
			$starting_date=date("Y-m-d",strtotime("-".$daterange." days"));
            $ending_date=date("Y-m-d");
            $starting_date_search=date("Y/m/d",strtotime("-".$daterange." days"));
			$ending_date_search=date("Ym/d");
            $date_select=$starting_date_search." - ".$ending_date_search;
		}

        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $totalUserCache = cache('totalUserCache'.$partner_id.$starting_date.$ending_date);
        $userListCache = cache('listUserCache'.$partner_id.$starting_date.$ending_date);
		if(isset($totalUserCache) && $totalUserCache!="" && isset($userListCache) && $userListCache!=""){
            $userList=$userListCache;
            $totalUser=$totalUserCache;
		}else{
            $totalUser=User::select('full_name', 'first_name', 'last_name', 'id')->where('partner_id',$partner_id)
            ->where('customer_care',1)->get();

            $userList=$totalUser->map( function ( $userListData) use ($starting_date, $ending_date){
                return [
                    'id'=> $userListData->id,
                    'fullname' => $userListData->first_name." ".$userListData->last_name,
                    'number_lead'=>Lead::select('id')->where('sales_person_id',$userListData->id)->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->count(),
                ];
            });
            Cache::put('listUserCache'.$partner_id.$starting_date.$ending_date, $userList, now()->addMinutes(600));
            Cache::put('totalUserCache'.$partner_id.$starting_date.$ending_date, $totalUser, now()->addMinutes(600));
        }
        
        $timeTracking_cache = cache('timeTracking_cache'.$starting_date.$ending_date);
        if(isset($timeTracking_cache) && $timeTracking_cache!=""){
            $timeTracking=$timeTracking_cache;
        }else{
            $timeTracking=$totalUser->map( function ( $userListData) use ($starting_date, $ending_date){
                return [
                    'id'=> $userListData->id,
                    'fullname' => $userListData->first_name." ".$userListData->last_name,
                    'number_lead'=>Task::select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE,task_start,task_end)) as timeadv'))->where('work_status',10)->where('user_id',$userListData->id)->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->first(),
                ];
            }); 

            Cache::put('timeTracking_cache'.$starting_date.$ending_date, $timeTracking, now()->addMinutes(30));
        }




        $timeDetailStaff_cache = cache('detailStaff'.$starting_date.$ending_date);
        if(isset($timeDetailStaff_cache) && $timeDetailStaff_cache!=""){
            $detailStaff=$timeDetailStaff_cache;
        }else{
            $detailStaff=$totalUser->map( function ( $userListData) use ($starting_date, $ending_date){
                return [
                    'id'=> $userListData->id,
                    'fullname' => $userListData->first_name." ".$userListData->last_name,
                    'agvprocess'=>Task::select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE,task_start,task_end)) as timeadv'))->where('work_status',10)->where('user_id',$userListData->id)->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->first(),
                    'totalLeadAll'=>Lead::join('chat_box', 'chat_box.sender_id', '=', 'leads.psid')->where('leads.sales_person_id',$userListData->id)->where('date_create','>=',$starting_date)->where('date_create','<=',$ending_date)->count(),
                    'tags'=>LeadTags::select(DB::raw('count(id) as totaltag'))->where('user_id',$userListData->id)->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->first(),
                    'totalMess'=>Chatbox::join('leads','leads.psid','=','chat_box.sender_id')->select(DB::raw('count(chat_box.id) as totalMess'))->where('leads.sales_person_id',$userListData->id)->where('chat_box.date_create','>=',$starting_date)->where('chat_box.date_create','<=',$ending_date)->first(),
                    'totalLead'=>Lead::where('sales_person_id',$userListData->id)->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->count(),
                    'phoneGet'=>Leadmap::join('leads','lead_maps.lead_id','=','leads.id')->select(DB::raw('count(lead_maps.id) as totalPhone'))->where('lead_maps.psid','!=',0)->where('leads.sales_person_id',$userListData->id)->where('lead_maps.created_at','>=',$starting_date)->where('lead_maps.created_at','<=',$ending_date)->first(),
    
                ];
            }); 
            Cache::put('detailStaff'.$starting_date.$ending_date, $detailStaff, now()->addMinutes(30));
        }

//Task::select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE,task_start,task_end)) as timeadv'))->where('work_status',10)->where('user_id',$userListData->id)->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->first(),
        return view( 'user.report.staff', compact( 'title', 'userList', 'timeTracking', 'detailStaff', 'starting_date', 'ending_date', 'daterange') );

    }


    public function inbox(Request $request){
        $title="Báo cáo Inbox";
        $date  = addslashes($request->starting_date);
        $page_id  = addslashes($request->page_id);
        $sales_id=addslashes($request->sales_id);

        if($date!=""){
			$dateArray=explode("-",trim($date));
			$starting_date=$this->convertDate(trim($dateArray[0]));
			$ending_date=$this->convertDate(trim($dateArray[1]));
			$starting_date=date("Y-m-d 00:01:00", strtotime($starting_date));
			$ending_date=date("Y-m-d 23:59:00", strtotime($ending_date));
			
			$date_select=$date;
		}else{
			$starting_date=date("Y-m-d",strtotime('today - 30 days'));
			$ending_date=date("Y-m-d",strtotime('today +1 days'));
			$date_select=date("m/d/Y",strtotime('today - 30 days'))." - ".date("m/d/Y");
        }
        $userData=$this->userRepository->getUser();
        $this->partner_id=$userData->partner_id;
        
        $listUserAssignCache = cache('listUserAssignCache5'.$this->partner_id.$userData->id);
		$listUserCache = cache('listUserCache5'.$this->partner_id.$userData->id);
        
		if(isset($listUserAssignCache) && $listUserAssignCache!=""){
			$listUserAssign=$listUserAssignCache;
			$listUser=$listUserCache;

		}else{
			$grouppermission=GroupUser::getGroup();
			$listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
			$listUserAssign=$this->userRepository->getAllUserOfPermissionOfStaff($userData, array("messenger.view_other", "messenger.full", "messenger.view_person"));
			Cache::put('listUserAssignCache5'.$this->partner_id.$userData->id, $listUserAssign, now()->addMinutes(10));
			Cache::put('listUserCache5'.$this->partner_id.$userData->id, $listUser, now()->addMinutes(10));
        } 
        
        //salesList
		$listUserCache="1";
		$usercache=$userData->id;
        $salesListCache = cache('salesListCache_2'.$this->partner_id.$listUserCache.$usercache);
		if(isset($salesListCache) && $salesListCache!=""){
			$salesList=$salesListCache;
		}else{

			if(isset($listUser) && $listUser!=""){
				$salesList=User::join('partner_user','partner_user.user_id','=','users.id')
							->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
							->where('partner_user.partner_id','=',$this->partner_id)
							->whereIn('users.id',$listUser)
							->get()
							->map( function ( $salesList ) {
								return [ 
									'title' => $salesList->first_name." ".$salesList->last_name,
									'value' => $salesList->id,
								];
							} )->pluck( 'title', 'value')
							->prepend(trans('lead.all'), '');
			}else{
				$salesList=User::join('partner_user','partner_user.user_id','=','users.id')
				->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
				->where('partner_user.partner_id','=',$this->partner_id)
				->get()
				->map( function ( $salesList ) {
					return [ 
						'title' => $salesList->first_name." ".$salesList->last_name,
						'value' => $salesList->id,
					];
				} )->pluck( 'title', 'value')
				->prepend(trans('lead.all'), '');
			}
			Cache::put('salesListCache_2'.$this->partner_id.$listUserCache.$salesListCache, $salesList, now()->addMinutes(10));
		}

        if(isset($sales_id) && $sales_id!=""){
            $id=$sales_id;
            $sales=array($sales_id);
        }else{
            $id=$userData->id;
            if($userData->user_id==1){
                $sales=User::select('id')->where('partner_id','=',$this->partner_id)->get()->pluck( 'id')->toArray();
            }else{
                $listSales=[];
                if(isset($salesList) && count($salesList)>0){
                    foreach($salesList as $key=>$value){
                        if($key!=""){
                            $listSales[]=$key;
                        }
                    }
                }
                $sales=$listSales;
            }
        }
        if(count($sales)>0){
            $salesSearch=implode(",",$sales);
        }else{
            $salesSearch=$sales;
        }
        $partner_id=$this->partner_id;
        /*
        if(isset($listUser) && $listUser!=""){
			$pageData=Getdata::select('config_datas.*')
			->join('user_control_page','user_control_page.page_id','=','config_datas.page_id')
			->where('config_datas.partner_id','=',$this->partner_id)
			->whereIn('user_control_page.user_id',$listUser)
			->where('config_datas.status',1)->orderBy('config_datas.id', 'desc')->groupBy('user_control_page.page_id')->get();
		}else{
			$pageData=Getdata::select('config_datas.*')
		//->join('user_control_page','user_control_page.page_id','=','config_datas.page_id')
		->where('config_datas.partner_id','=',$this->partner_id)
		->where('config_datas.status',1)->orderBy('config_datas.id', 'desc')->groupBy('user_control_page.page_id')->get();
        } */
        
        
        $pagedata_cache = cache('pagedata'.$partner_id);
        if(isset($pagedata_cache) && $pagedata_cache!=""){
            $pageData=$pagedata_cache;
        }else{
            $pageData=Getdata::select('config_datas.*')
            ->where('config_datas.partner_id','=',$partner_id)
            ->where('config_datas.status',1)->orderBy('config_datas.id', 'desc')->get();
            Cache::put('pagedata'.$partner_id, $pageData, now()->addMinutes(60));
        }

		$totalpage=0;
		$leadsList=null;
        $leadsPage=null;
        $today=date("Y-m-d");
        $this_week=date("Y-m-d",strtotime('this week'));

        $end_ago_week=date("Y-m-d",strtotime('today -1 week'));
        $start_ago_week=date("Y-m-d",strtotime("-7 day",strtotime(date("Y-m-d",strtotime('today -1 week')))));

        $this_month_to=date("Y-m-d");
        $this_month_from=date("Y-m")."-01";

        //get Month ago
        $number = cal_days_in_month(CAL_GREGORIAN, date('m', strtotime('-1 month')), date('Y', strtotime('-1 month')));
        $ago_month_to=date('Y-m', strtotime('-1 month'))."-".$number;
        $ago_month_from=date('Y-m', strtotime('-1 month'))."-01";
        //End

        //get Six month ago
        $numbersix = cal_days_in_month(CAL_GREGORIAN, date('m', strtotime('-1 month')), date('Y', strtotime('-1 month')));
        $six_month_to=date('Y-m-d');
        $six_month_from=date('Y-m', strtotime('-1 month'))."-".$numbersix;
        //End

        $today=date("Y-m-d");
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        //DB::raw('id as totalLead')
        //Client 
        

        $cacheCodeLead=md5($partner_id.implode(",",$sales).$ending_date.$starting_date);
        $totalClientcache=cache('totalClient'.$cacheCodeLead);
        if(isset($totalClientcache) && $totalClientcache!=""){
            $totalClient=$totalClientcache;
        }else{
            $totalClient=Lead::select('id')->where('partner_id',$partner_id)
            ->whereIn('leads.sales_person_id',$sales)
            ->count();
            Cache::put('totalClient'.$cacheCodeLead, $totalClient, now()->addMinutes(120));
        } 
        
        // $totalClient, $totalClientToday,  $totalClientThisMonth,  $totalClientAgoMonth, totalClientWeek, totalClientAgoWeek

        

        $totalClientThisWeekCache=cache('totalClientThisWeek'.$cacheCodeLead);
        if(isset($totalClientThisWeekCache) && $totalClientThisWeekCache!=""){
            $totalClientWeek=$totalClientThisWeekCache;
        }else{
            $totalClientWeek=Lead::select('id')->where('partner_id',$partner_id)
            ->whereIn('leads.sales_person_id',$sales)
            ->where(function ($query)  use ($today, $this_week, $page_id){
                $starting_date=$this_week." 00:00:00"; 
                $ending_date=$today." 23:59:59";
                if($starting_date!=""){
                    $query->where('created_at','>=',$starting_date);
                }
                if($ending_date!=""){
                    $query->where('created_at','<=',$ending_date);
                }
            })
            ->count();
            Cache::put('totalClientThisWeek'.$totalClientThisWeekCache, $totalClientWeek, now()->addMinutes(120));
        } 


        $totalClientAgoWeekCache=cache('totalClientAgoWeek'.$cacheCodeLead);
        if(isset($totalClientAgoWeekCache) && $totalClientAgoWeekCache!=""){
            $totalClientAgoWeek=$totalClientAgoWeekCache;
        }else{
            $totalClientAgoWeek=Lead::select('id')->where('partner_id',$partner_id)
            ->whereIn('leads.sales_person_id',$sales)
            ->where(function ($query)  use ($start_ago_week, $end_ago_week, $page_id){
                $starting_date=$start_ago_week; 
                $ending_date=$end_ago_week;
                if($starting_date!=""){
                    $query->where('leads.created_at','>=',$starting_date);
                }
                if($ending_date!=""){
                    $query->where('leads.created_at','<=',$ending_date);
                }
            })->count();
            Cache::put('totalClientAgoWeek'.$totalClientAgoWeekCache, $totalClientAgoWeek, now()->addMinutes(600));
        } 

        

        $totalClientToday=Lead::select('id')->where('partner_id',$partner_id)
        ->whereIn('leads.sales_person_id',$sales)
        ->where(function ($query)  use ($today, $page_id){
            $starting_date=$today." 00:00:00";
            $ending_date=$today." 23:59:59";
            if($starting_date!=""){
                $query->where('leads.created_at','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('leads.created_at','<=',$ending_date);
            }
        })->count();

        $totalClientThisMonth=Lead::select('id')->where('partner_id',$partner_id)
        ->whereIn('leads.sales_person_id',$sales)
        ->where(function ($query)  use ($this_month_to, $this_month_from, $page_id){
            $starting_date=$this_month_from;
            $ending_date=$this_month_to;
            if($starting_date!=""){
                $query->where('leads.created_at','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('leads.created_at','<=',$ending_date);
            }
        })->count();

        $totalClientAgoMonthCache=cache('totalClientAgoMonth'.$cacheCodeLead);
        if(isset($totalClientAgoMonthCache) && $totalClientAgoMonthCache!=""){
            $totalClientAgoMonth=$totalClientAgoMonthCache;
        }else{
            $totalClientAgoMonth=Lead::select('id')->where('partner_id',$partner_id)
            ->whereIn('leads.sales_person_id',$sales)
            ->where(function ($query)  use ($ago_month_to, $ago_month_from, $page_id){
                $starting_date=$ago_month_from;
                $ending_date=$ago_month_to;
                if($starting_date!=""){
                    $query->where('leads.created_at','>=',$starting_date);
                }
                if($ending_date!=""){
                    $query->where('leads.created_at','<=',$ending_date);
                }
            })->count();
            Cache::put('totalClientAgoMonth'.$cacheCodeLead, $totalClientAgoMonth, now()->addMinutes(1200));
        } 

        //end client

        //Inbox
        $cacheCode=md5($partner_id.implode(",",$sales).$ending_date.$starting_date);
        $totalInboxcache=cache('totalInboxCache'.$cacheCode);
        if(isset($totalInboxcache) && $totalInboxcache!=""){
            $totalInbox=$totalInboxcache;
        }else{
            $totalInbox=ReportStaff::whereIn('user_id',$sales)
            ->where('date_create','>=', $starting_date)
            ->where('date_create','<=',$ending_date)
            ->where('type','inbox')->sum('number');
            Cache::put('totalInboxCache'.$cacheCode, $totalInbox, now()->addMinutes(20));
        } 
        /*
        $totalInbox=Chatbox::select('id')->where('partner_id',$partner_id)
        ->where(function ($query)  use ($page_id){
            if($page_id!=""){
                $query->where('page_id', $page_id);
            }
        })
        ->count();
        
        ->where(function ($query)  use ($starting_date, $ending_date){
            if($starting_date!=""){
                $query->where('created_at','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('created_at','<=',$ending_date);
            }
        })->count();
        */
        $starting_date_today=$today." 00:00:00";
        $ending_date_today=$today." 23:59:59";
        $cacheCodeToday=md5($partner_id.implode(",",$sales).$starting_date_today.$ending_date_today);
        $totalInboxcache=cache('totalInboxToday'.$cacheCodeToday);
        if(isset($totalInboxcache) && $totalInboxcache!=""){
            $totalInboxToday=$totalInboxcache;
        }else{
            $totalInboxToday=ReportStaff::whereIn('user_id',$sales)
            ->where('date_create','>=', $starting_date_today)
            ->where('date_create','<=',$ending_date_today)
            ->where('type','inbox')->sum('number');
            Cache::put('totalInboxToday'.$cacheCodeToday, $totalInboxToday, now()->addMinutes(30));
        } 
        /*
        $totalInboxToday=Chatbox::select('id')->where('partner_id',$partner_id)
        ->where(function ($query)  use ($today, $page_id){
            $starting_date=$today." 00:00:00";
            $ending_date=$today." 23:59:59";
            if($starting_date!=""){
                $query->where('created_at','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('created_at','<=',$ending_date);
            }
            if($page_id!=""){
                $query->where('page_id', $page_id);
            }
        })->count(); */

        $cacheCodeThisMonth=md5($partner_id.implode(",",$sales).$this_month_to.$this_month_from);
        $totalInboxcacheThisMonth=cache('totalInboxThisMonth'.$cacheCodeThisMonth);
        if(isset($totalInboxcacheThisMonth) && $totalInboxcacheThisMonth!=""){
            $totalInboxThisMonth=$totalInboxcacheThisMonth;
        }else{
            $totalInboxThisMonth=ReportStaff::whereIn('user_id',$sales)
            ->where('date_create','>=', $this_month_from)
            ->where('date_create','<=',$this_month_to)
            ->where('type','inbox')->sum('number');
            Cache::put('totalInboxThisMonth'.$cacheCodeThisMonth, $totalInboxThisMonth, now()->addMinutes(600));
        } 
        /*
        $totalInboxThisMonth=Chatbox::select('id')->where('partner_id',$partner_id)
        ->where(function ($query)  use ($this_month_to, $this_month_from, $page_id){
            $starting_date=$this_month_from;
            $ending_date=$this_month_to;
            if($starting_date!=""){
                $query->where('created_at','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('created_at','<=',$ending_date);
            }
            if($page_id!=""){
                $query->where('page_id', $page_id);
            }
        })->count(); */

        $cacheCodeAgoMonth=md5($partner_id.implode(",",$sales).$ago_month_to.$ago_month_from);
        $totalInboxcachetotalInboxAgoMonth=cache('totalInboxAgoMonth'.$cacheCodeAgoMonth);
        if(isset($totalInboxcachetotalInboxAgoMonth) && $totalInboxcachetotalInboxAgoMonth!=""){
            $totalInboxAgoMonth=$totalInboxcachetotalInboxAgoMonth;
        }else{
            $totalInboxAgoMonth=ReportStaff::whereIn('user_id',$sales)
            ->where('date_create','>=', $this_month_from)
            ->where('date_create','<=',$this_month_to)
            ->where('type','inbox')->sum('number');
            Cache::put('totalInboxAgoMonth'.$cacheCodeAgoMonth, $totalInboxAgoMonth, now()->addMinutes(10000));
        } 
        /*
        $totalInboxAgoMonth=Chatbox::select('id')->where('partner_id',$partner_id)
        ->where(function ($query)  use ($ago_month_to, $ago_month_from, $page_id){
            $starting_date=$ago_month_from;
            $ending_date=$ago_month_to;
            if($starting_date!=""){
                $query->where('created_at','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('created_at','<=',$ending_date);
            }
            if($page_id!=""){
                $query->where('page_id', $page_id);
            }
        })->count();
        */
        $starting_date_thisweek=$this_week." 00:00:00"; 
        $ending_date_thisweek=$today." 23:59:59";
        $cacheCodeThisWeek=md5($partner_id.implode(",",$sales).$starting_date_thisweek.$ending_date_thisweek);
        $totalInboxcacheThisWeek=cache('totalThisWeeek'.$cacheCodeThisWeek);
        if(isset($totalInboxcacheThisWeek) && $totalInboxcacheThisWeek!=""){
            $totalInboxWeek=$totalInboxcacheThisWeek;
        }else{
            $totalInboxWeek=ReportStaff::whereIn('user_id',$sales)
            ->where('date_create','>=', $starting_date_thisweek)
            ->where('date_create','<=',$ending_date_thisweek)
            ->where('type','inbox')->sum('number');
            Cache::put('totalThisWeeek'.$cacheCodeThisWeek, $totalInboxWeek, now()->addMinutes(600));
        }
        /*
        $totalInboxWeek=Chatbox::select('id')->where('partner_id',$partner_id)
        ->where(function ($query)  use ($today, $this_week, $page_id){
            $starting_date=$this_week." 00:00:00"; 
            $ending_date=$today." 23:59:59";
            if($starting_date!=""){
                $query->where('created_at','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('created_at','<=',$ending_date);
            }
            if($page_id!=""){
                $query->where('page_id', $page_id);
            }
        })
        ->count();*/

        $cacheCodeThisWeekAgo=md5($partner_id.implode(",",$sales).$start_ago_week.$end_ago_week);
        $totalInboxcacheThisWeekAgo=cache('totalThisWeeekAgo'.$cacheCodeThisWeekAgo);
        if(isset($totalInboxcacheThisWeekAgo) && $totalInboxcacheThisWeekAgo!=""){
            $totalInboxAgoWeek=$totalInboxcacheThisWeekAgo;
        }else{
            $totalInboxAgoWeek=ReportStaff::whereIn('user_id',$sales)
            ->where('date_create','>=', $starting_date_thisweek)
            ->where('date_create','<=',$ending_date_thisweek)
            ->where('type','inbox')->sum('number');
            Cache::put('totalThisWeeekAgo'.$cacheCodeThisWeekAgo, $totalInboxAgoWeek, now()->addMinutes(600));
        }
        /*
        $totalInboxAgoWeek=Chatbox::select('id')->where('partner_id',$partner_id)
        ->where(function ($query)  use ($start_ago_week, $end_ago_week, $page_id){
            $starting_date=$start_ago_week; 
            $ending_date=$end_ago_week;
            if($starting_date!=""){
                $query->where('created_at','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('created_at','<=',$ending_date);
            }
            if($page_id!=""){
                $query->where('page_id', $page_id);
            }
        })->count();
        */
        //End Inbox

        //Top staff
        $starting_date_today=date("Y-m-d")." 00:00:00";
        $ending_date_today=date("Y-m-d")." 23:59:00";
        $cacheTopStaff=md5($partner_id.$starting_date_today.$ending_date_today);
        $totalInboxTodayTopStaff=cache('topStaffOnday1'.$cacheTopStaff);
        if(isset($totalInboxTodayTopStaff) && $totalInboxTodayTopStaff!=""){
            $chatStaff=$totalInboxTodayTopStaff;
        }else{
            $chatStaff=User::join('report_staff','report_staff.user_id','=','users.id')
            ->select('users.full_name', 'users.first_name', 'users.last_name', 'users.id', DB::raw('sum(report_staff.number) as totalInbox'))
            ->where('users.partner_id',$partner_id)
            ->where('report_staff.type',"inbox")
            ->where(function ($query) use($page_id, $starting_date_today,$ending_date_today){
                if($starting_date_today!=""){
                    $query->where('report_staff.date_create','>=',$starting_date_today);
                }
                if($ending_date_today!=""){
                    $query->where('report_staff.date_create','<=',$ending_date_today);
                }
            })
            ->groupBy('users.id')->get();

            Cache::put('topStaffOnday1'.$cacheTopStaff, $chatStaff, now()->addMinutes(30));
        }


        //End top staff
        //Top inBox in 6month listPhoneMonthData
        $arraySixMonth=null;
        for ($i=5; $i >=1; $i--)
        {
           $arraySixMonth[] = date("Y-m", strtotime( date('Y-m-01')." -$i months"));
        }
        $arraySixMonth[]=date("Y-m");


        $cacheSixmonthInbox=md5($partner_id.implode(",",$sales).$six_month_from.$six_month_to);
        $totalInboxSixMonth=cache('totalInboxSixMonth1'.$cacheSixmonthInbox);
        if(isset($totalInboxSixMonth) && $totalInboxSixMonth!=""){
            $totalInboxSixMonth=$totalInboxSixMonth;
        }else{
            $totalInboxSixMonth=ReportStaff::select(DB::raw('sum(number) as totalInbox'), DB::raw('DATE_FORMAT(date_create, "%Y-%m") as createdate'))
            ->whereIn('user_id',$sales)
            ->where('report_staff.type',"inbox")
            ->where(function ($query)  use ($six_month_from, $six_month_to, $page_id){
                $starting_date=$six_month_from;
                $ending_date=$six_month_to;
                if($starting_date!=""){
                    $query->where('date_create','>=',$starting_date);
    
                }
                if($ending_date!=""){
                    $query->where('date_create','<=',$ending_date);
                }
            })->groupBy('createdate')->orderBy('createdate')->get();

            Cache::put('totalInboxSixMonth1'.$cacheSixmonthInbox, $totalInboxSixMonth, now()->addMinutes(60));
        }

        //End
        $listSixMonth=null;
        $listSixMonthData=null;
        if($totalInboxSixMonth){
            foreach($totalInboxSixMonth as $listData){
                $listSixMonthData[$listData["createdate"]]=$listData["totalInbox"];
            }
        }
        //Summary Top inBox in 6month
        $cacheSixmonthInboxPhone=md5($partner_id.implode(",",$sales).$page_id.$six_month_from.$six_month_to);
        $totalInboxSixMonthPhone=cache('totalInboxPhoneMonthCache2'.$cacheSixmonthInboxPhone);
        if(isset($totalInboxSixMonthPhone) && $totalInboxSixMonthPhone!=""){
            $totalInboxPhoneMonth=$totalInboxSixMonthPhone;
        }else{
            $totalInboxPhoneMonth=Lead::select(DB::raw('count(id) as totalInbox'), DB::raw('DATE_FORMAT(leads.created_at, "%Y-%m") as createdate'))->where('partner_id',$partner_id)
            ->where('phone','!=','')
            ->where('group_id',45)
            ->whereIn('sales_person_id',$sales)
            ->where(function ($query)  use ($six_month_from, $six_month_to, $page_id){
                $starting_date=date("Y-m-d",strtotime($six_month_from));
                $ending_date=date("Y-m-d",strtotime($six_month_to));

                if($starting_date!=""){
                    //$query->where(DB::raw('DATE_FORMAT(chat_box.date_create, "%Y-%m")'),'>=',$starting_date);
                    $query->where('leads.updated_at','>=',$starting_date);

                }
                if($ending_date!=""){
                    //$query->where(DB::raw('DATE_FORMAT(chat_box.date_create, "%Y-%m")'),'<=',$ending_date);
                    $query->where('leads.updated_at','<=',$ending_date);

                }
            })->groupBy('createdate')->orderBy('createdate')->get();

            Cache::put('totalInboxPhoneMonthCache2'.$cacheSixmonthInboxPhone, $totalInboxPhoneMonth, now()->addMinutes(60));
        }
        $listPhoneMonthData=null;
        $totalPhone=0;
        if($totalInboxPhoneMonth){
            foreach($totalInboxPhoneMonth as $listData){
                $listPhoneMonthData[$listData["createdate"]]=$listData["totalInbox"];
                $totalPhone+=$listData["totalInbox"];
            }
        }

        


        $cacheSixNewClient=md5($partner_id.implode(",",$sales).$page_id.$six_month_from.$six_month_to);
        $totalInboxSixMonth=cache('cacheSixNewClient2'.$cacheSixmonthInboxPhone);
        if(isset($totalInboxSixMonth) && $totalInboxSixMonth!=""){
            $totalClientNewSixMonth=$totalInboxSixMonth;
        }else{
            $totalClientNewSixMonth=Lead::select(DB::raw('count(id) as totalClient'), DB::raw('DATE_FORMAT(leads.created_at, "%Y-%m") as createdate'))->where('leads.partner_id',$partner_id)
            ->whereIn('sales_person_id',$sales)
            ->where(function ($query)  use ($six_month_from, $six_month_to, $page_id){
                $starting_date=date("Y-m-d",strtotime($six_month_from));
                $ending_date=date("Y-m-d",strtotime($six_month_to));
                if($starting_date!=""){
                    $query->where(DB::raw('DATE_FORMAT(leads.created_at, "%Y-%m")'),'>=',$starting_date);
                }
                if($ending_date!=""){
                    $query->where(DB::raw('DATE_FORMAT(leads.created_at, "%Y-%m")'),'<=',$ending_date);
                }
            })->groupBy('createdate')->orderBy('createdate')->get();

            Cache::put('cacheSixNewClient2'.$cacheSixNewClient, $totalClientNewSixMonth, now()->addMinutes(1200));
        }

        
        $listClientNewSixData=null;
        $totalNewClient=0;
        if($totalClientNewSixMonth){
            foreach($totalClientNewSixMonth as $listData){
                $listClientNewSixData[$listData["createdate"]]=$listData["totalClient"];
                $totalNewClient+=$listData["totalClient"];
            }
        }
        

        $cacheSixNewClient=md5($partner_id.implode(",",$sales).$page_id.$six_month_from.$six_month_to);
        $totalInboxSixMonth=cache('cacheTotalInboxOnSixMonth4'.$cacheSixNewClient);
        if(isset($totalInboxSixMonth) && $totalInboxSixMonth!=""){
            $totalInboxNewSmsSixMonth=$totalInboxSixMonth;
        }else{
            $totalInboxNewSmsSixMonth=ReportStaff::select(DB::raw('sum(number) as totalInbox'), DB::raw('DATE_FORMAT(report_staff.date_create, "%Y-%m") as createdate'))
            ->whereIn('user_id',$sales)
            ->where('report_staff.type',"inbox")
            ->where(function ($query)  use ($six_month_from, $six_month_to, $page_id){
                $starting_date=date("Y-m-d",strtotime($six_month_from));
                $ending_date=date("Y-m-d",strtotime($six_month_to));

                if($starting_date!=""){
                    $query->where('report_staff.date_create','>=',$starting_date);
                }
                if($ending_date!=""){
                    $query->where('report_staff.date_create','<=',$ending_date);
                }
            })->groupBy('createdate')->orderBy('createdate')->get();
            Cache::put('cacheTotalInboxOnSixMonth4'.$cacheSixNewClient, $totalInboxNewSmsSixMonth, now()->addMinutes(1200));
        }



        $listSmsNewSixData=null;
        $totalSMS=0;
        if($totalInboxNewSmsSixMonth){
            foreach($totalInboxNewSmsSixMonth as $listData){
                $listSmsNewSixData[$listData["createdate"]]=$listData["totalInbox"];
                $totalSMS+=$listData["totalInbox"];
            }
        }
        $listSmsOldSixData=null;
        $totalSmsOld=0;
        /*
        $totalInboxOldSmsSixMonth=Chatbox::select(DB::raw('count(chat_box.id) as totalInbox'), DB::raw('DATE_FORMAT(chat_box.date_create, "%Y-%m") as createdate'))->join('leads','leads.psid','=','chat_box.sender_id')->where('leads.partner_id',$partner_id)
        ->where('extention','!=','')
        ->where(function ($query)  use ($six_month_from, $six_month_to, $page_id){
  
            $starting_date=date("Y-m-d",strtotime($six_month_from));
            $ending_date=date("Y-m-d",strtotime($six_month_to));
            $query->where('leads.created_at','<',$starting_date);
            if($starting_date!=""){
                $query->where(DB::raw('DATE_FORMAT(chat_box.date_create, "%Y-%m")'),'>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where(DB::raw('DATE_FORMAT(chat_box.date_create, "%Y-%m")'),'<=',$ending_date);
            }
            if($page_id!=""){
                $query->where('chat_box.page_id', $page_id);
            }
        })->groupBy('createdate')->orderBy('createdate')->get();

        if($totalInboxOldSmsSixMonth){
            foreach($totalInboxOldSmsSixMonth as $listData){
                $listSmsOldSixData[$listData["createdate"]]=$listData["totalInbox"];
                $totalSmsOld+=$listData["totalInbox"];
            }
        } */
        
        //End
        $pageList=$pageData->map(function ( $pageListData ) {
			return [
				'title' => $pageListData->title,
				'value' => $pageListData->page_id,
			];
		} )->pluck( 'title', 'value');

        return view( 'user.report.inbox', compact( 'title', 'totalClient', 'totalClientToday',  'totalClientThisMonth',  'totalClientAgoMonth', 'totalClientWeek', 'totalClientAgoWeek', 'totalInbox', 'totalInboxToday', 'totalInboxThisMonth', 'totalInboxAgoMonth', 'totalInboxWeek', 'totalInboxAgoWeek', 'chatStaff', 'listSixMonth', 'listSixMonthData', 'listPhoneMonthData', 'listClientNewSixData', 'listSmsNewSixData', 'listSmsOldSixData', 'arraySixMonth', 'totalPhone', 'totalNewClient', 'totalSMS', 'totalSmsOld', 'pageList', 'salesList') ); 
    }

    public function data(Datatables $datatables)
    {
        $dateFormat = config('settings.date_format');
            $staffs = $this->userRepository->getAllNew()->with('staffSalesTeam')
            ->get()
            ->filter(function ($user) {
                return ($user->inRole('staff') && $user->id!=$this->user->id);
            });
            $totalUser=$staffs->count();
			$userData=$staffs->paginate(15)->appends(request()->query());
			$user=$leadsData->map( function ( $lead) use ($dateFormat){
                return [
                    'id'           => $lead->id,
					'created_at'   => date($dateFormat,strtotime($lead->created_at)),
                    'opportunity' => $lead->opportunity,
                ];
            });
            return view( 'user.staff.index', compact( 'title', 'leads', 'leadsData', 'salesList', 'statusList', 'sourceList', 'leadGroupSource', 'totalLead', 'date_select') );

    }
 
    public function dashboard($id)
    {

        $date  = addslashes($request->starting_date);
        if($date!=""){
			$dateArray=explode("-",trim($date));
			$starting_date=$this->convertDate(trim($dateArray[0]));
			$ending_date=$this->convertDate(trim($dateArray[1]));
			$starting_date=date("Y-m-d 00:01:00", strtotime($starting_date));
			$ending_date=date("Y-m-d 23:59:00", strtotime($ending_date));
			$date_select=$date;
		}else{
            //$starting_date=date("Y-m-d",strtotime('today - 30 days'));
            $starting_date=date("Y-m-d");
			$ending_date=date("Y-m-d",strtotime('today +1 days'));
			$date_select=date("m/d/Y",strtotime('today +1 days'))." - ".date("m/d/Y");
        }
        $daterange  = addslashes($request->daterange);
        if(isset($daterange) && $daterange!="" && $daterange!=0){
			$starting_date=date("Y-m-d",strtotime("-".$daterange." days"));
            $ending_date=date("Y-m-d");
            $starting_date_search=date("Y/m/d",strtotime("-".$daterange." days"));
			$ending_date_search=date("Ym/d");
            $date_select=$starting_date_search." - ".$ending_date_search;
		}

        
        if (Sentinel::check()) {
            $userData=$this->userRepository->getUser();
            $this->partner_id=$userData->partner_id;
            $sales=array($userData->id);
            $staffDetail = $this->userRepository->find($id);
            //$customers = $this->companyRepository->getAll()->count();
            //$contracts = $this->contractRepository->getAll()->count();
            //$opportunities = $this->opportunityRepository->getAll()->count();
            //$products = Product::where('partner_id', $this->partner_id)->count();
            //Check tồng lead assign ->join('call_action_status','call_action_status.id','=')

            $totalLead=Lead::where('sales_person_id',$userData->id)->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->where('group_id',45)->count();

            $totalCustomer=Lead::where('sales_person_id',$userData->id)->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->where('group_id',44)->count();


            $leadAssign = LeadAssignStatus::selectRaw('DATE_FORMAT(lead_assign_status.date_create, "%Y-%m-%d") as date_assign, (select count(id) from lead_assign_status where status=1 and user_id='.$id.' and date_create>=date_assign and date_create<(date_assign+INTERVAL 1 DAY)) as leadAccept, (select count(id) from lead_assign_status where status=0 and user_id='.$id.' and date_create>=date_assign and date_create<(date_assign+INTERVAL 1 DAY)) as leadNoAccept')->join('leads','leads.id','=','lead_assign_status.lead_id')
            ->where('leads.partner_id',$this->partner_id)
            ->where('lead_assign_status.user_id','=',$id)->groupBy('date_assign')->get();

            //end
            /*
            $opportunity_leads = array();
            $stages = array();
            for($i=11;$i>=0;$i--)
            {
                $opportunity_leads[] =
                    [
                        'month' =>Carbon::now()->subMonth($i)->format('M'),
                        'year' =>Carbon::now()->subMonth($i)->format('Y'),
                        'opportunity'=>$this->leadRepository->getAll()->where('created_at','LIKE',
                                Carbon::now()->subMonth($i)->format('Y-m').'%')->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->count(),
                        'leads'=>$this->leadRepository->getAll()->where('created_at','LIKE',
                              Carbon::now()->subMonth($i)->format('Y-m').'%')->whereIn('status',array(6,7))->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->count()
                    ];
            } */
            $list_lead_group = array();
            $listGroupLead=CallActionStatus::where("parner_id",$this->partner_id)->where('group_id',45)->get();
            foreach($listGroupLead as $listData){
                $list_lead_group[] =
                    [
                        'id' =>$listData["id"],
                        'name' =>$listData["name"],
                        'leads'=>$this->leadRepository->getAll()->where('created_at','>=',
                        $starting_date)->where('created_at','<=',$ending_date)->whereIn('sales_person_id',$sales)->where('partner_id', $this->partner_id)->where('group_id', $listData["group_id"])->count()
                    ];
            }

            $list_customer_group = array();
            $listGroupCustomer=CallActionStatus::where("parner_id",$this->partner_id)->where('group_id',44)->get();
            foreach($listGroupCustomer as $listData){
                $list_customer_group[] =
                    [
                        'id' =>$listData["id"],
                        'name' =>$listData["name"],
                        'leads'=>$this->leadRepository->getAll()->where('created_at','>=',
                        $starting_date)->where('created_at','<=',$ending_date)->whereIn('sales_person_id',$sales)->where('partner_id', $this->partner_id)->where('group_id', $listData["group_id"])->count()
                    ];
            }
            $totalLeadOld=ReportStaff::whereIn('sales_person_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalLeadOld')->count();
            $totalLeadReply=ReportStaff::whereIn('sales_person_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalLeadOldHasReply')->count();

            $totalOrder=ReportStaff::whereIn('sales_person_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalInvoice')->sum('number');
            $totalRevenus=ReportStaff::whereIn('sales_person_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalRevenus')->sum('number');


            //Call
            //$callTotal= $this->callRepository->getAll()->where('user_id',$id)->count();
            //$callTotalMissing= $this->callRepository->getAll()->where('duration','<=', 3)->where('user_id',$id)->count();
            //$callTotalSuccess= $this->callRepository->getAll()->where('duration','>', 3)->where('user_id',$id)->count();
            //End call
            //$totalLead = $this->leadRepository->getAll()->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->count(); //New lead
            
            //$opportunity_new = $this->leadRepository->getAll()->where('status', 0)->where('partner_id', $this->partner_id)->where('sales_person_id',$id)->count(); //New lead
            //$opportunity_negotiation = $this->leadRepository->getAll()->where('sales_person_id',$id)->whereIn('status', array(2,3,4,5))->count();
           // $opportunity_won = $this->leadRepository->getAll()->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->whereIn('status', array(6,7))->count();
            //$opportunity_loss = $this->leadRepository->getAll()->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->whereIn('status', array(8,9,10,11))->count();
           // $opportunity_expired = $this->leadRepository->getAll()->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->where('status', 12)->count();
            // Tỷ lệ chuyển đổi
           // $ctrcall=0;
            //$ctrleadregister=0;
            //if($callTotalSuccess>0){
              //  $ctrcall=round(($opportunity_won/$callTotalSuccess)*100,2);

            //}
            //End ty lệ
            // Tỷ lệ chuyển đổi
            if($totalLead>0){
            //$ctrleadregister=round(($opportunity_won/$totalLead)*100,2);
            }
            //End ty lệ
            $staff=0;
            if($this->userRepository->getAllUserArray($id)){
         //       $staff=$this->userRepository->getAllUserArray($id)->count();

            }

            return view('user.staff.dashboard', compact('customers', 'totalLead', 'totalCustomer','totalLeadOld', 'totalLeadReply','totalOrder','totalRevenus','list_lead_group','list_customer_group', 'leadAssign', 'staffDetail'));
        }
    }
    public function taskuser($userid){
        $logData=Logs::where('user_id',$userid)->orderBy("id", "desc")->paginate(100)->appends(request()->query());
        $logshow=$logData->map( function ( $logs){
                return [
                    "id" => $logs->id,
                    "description" => $logs->logs,
                    "created_at" => date("d/m/Y H:i:s",strtotime($logs->created_at)),
                ];
            }
        );
        return $logshow;
    }

    private function generateParams()
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;

        $userStaffGroup = Groupuser::where('partner_id',$partner_id)->get();
        $branch=Branch::where('partner_id',$partner_id)->get()->map( function ( $branch) {
            return [
                'title' => $branch->name,
                'value' => $branch->id,
            ];
        } )->pluck( 'title', 'value' )
        ->prepend(trans('branch.branch_select'), ''); 
        //view()->share('staffs', $staffs);
        view()->share( 'userStaffGroup', $userStaffGroup );
        view()->share( 'branch', $branch );

        
    }
    public function user_list(Request $request){
        $branch=$request->branch_id;
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $staffsQuery=array();
        $staffs=array();
        if($branch!=""){
            $staffsQuery = User::select('users.*')
            ->where('partner_id',$partner_id)
            ->where('branch_id',$branch)
            ->groupBy('users.id')
            ->orderBy('users.id', 'DESC')->get();
            $staffs=$staffsQuery->map( function ( $staff){
                    return [
                        "id" => $staff->id,
                        "full_name" => $staff->first_name." ".$staff->last_name,
                    ];
                }
            );
        }
           
        return $staffs;
    }

    public function summary(Request $request)
    {
        $date  = addslashes($request->starting_date);
        $sales_id=addslashes($request->sales_id);
        if($date!=""){
            $dateArray=explode("-",trim($date));
            $starting_date=$this->convertDate(trim($dateArray[0]));
            $ending_date=$this->convertDate(trim($dateArray[1]));
            $starting_date=date("Y-m-d", strtotime($starting_date));
            $ending_date=date("Y-m-d 23:59:00", strtotime($ending_date));
            $date_select=$date;
        }else{
            $ending_date=date("Y-m-d")." 23:59:59";
            $starting_date=date("Y-m-d");
            $date_select=date("m/d/Y",strtotime('today -1 days'))." - ".date("m/d/Y");
        }
        $daterange  = addslashes($request->daterange);
        if($daterange=='thismonth'){ 
            $starting_date=date("Y-m-01");
            $ending_date=date("Y-m-d 23:59:00");
            $date_select=date("m/d/Y",strtotime($starting_date))." - ".date("m/d/Y",strtotime($ending_date));
        }elseif($daterange=='lastmonth'){
            $starting_date=date("Y-m-d", strtotime("first day of previous month"));
            $ending_date=date("Y-m-d 23:59:59", strtotime("last day of previous month"));
            $date_select=date("m/d/Y",strtotime($starting_date))." - ".date("m/d/Y",strtotime($ending_date));
        }else{
            if(isset($daterange) && $daterange==1){
                $starting_date=date("Y-m-d");
                $ending_date=date("Y-m-d 23:59:59");
                $starting_date_search=date("m/d/Y");
                $ending_date_search=date("m/d/Y");
                $date_select=$starting_date_search." - ".$ending_date_search;
            }elseif(isset($daterange) && $daterange==2){
                    $starting_date=date("Y-m-d", strtotime("-".($daterange-1)." days"));
                    $ending_date=date("Y-m-d 23:59:59", strtotime("-".($daterange-1)." days"));
                    $starting_date_search=date("m/d/Y", strtotime("-".($daterange-1)." days"));
                    $ending_date_search=date("m/d/Y", strtotime("-".($daterange-1)." days"));
                    $date_select=$starting_date_search." - ".$ending_date_search;

            }elseif(isset($daterange) && $daterange!=""){
                $starting_date=date("Y-m-d",strtotime("-".$daterange." days"));
                $ending_date=date("Y-m-d 23:59:59");
                $starting_date_search=date("m/d/Y",strtotime("-".$daterange." days"));
                $ending_date_search=date("m/d/Y");
                $date_select=$starting_date_search." - ".$ending_date_search;
            }
        }
        $userData=$this->userRepository->getUser();
        $this->partner_id=$userData->partner_id;
        if(!isset($sales_id) || $sales_id=="" || $sales_id==0){
            if($userData->user_id==1){
                $sales_id=0;
            }else{
                $sales_id=$userData->id;
            }
        }
        $listUserAssignCache = cache('listUserAssignCache5'.$this->partner_id.$userData->id);
        $listUserCache = cache('listUserCache5'.$this->partner_id.$userData->id);
        
        if(isset($listUserAssignCache) && $listUserAssignCache!=""){
            $listUserAssign=$listUserAssignCache;
            $listUser=$listUserCache;

        }else{
            $grouppermission=GroupUser::getGroup();
            $listUser=$this->memberUsers($sales_id);
           // $listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
            $listUserAssign=$this->userRepository->getAllUserOfPermissionOfStaff($userData, array("messenger.view_other", "messenger.full", "messenger.view_person"));
            Cache::put('listUserAssignCache5'.$this->partner_id.$userData->id, $listUserAssign, now()->addMinutes(10));
            Cache::put('listUserCache5'.$this->partner_id.$userData->id, $listUser, now()->addMinutes(10));
        } 


        //salesList
        $listUserCache="1";
        $usercache=$userData->id;
        $salesListCache = cache('salesListCache_2'.$this->partner_id.$listUserCache.$usercache);
        if(isset($salesListCache) && $salesListCache!=""){
            $salesList=$salesListCache;
        }else{

            if(isset($listUser) && $listUser!=""){
                $salesList=User::join('partner_user','partner_user.user_id','=','users.id')
                            ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
                            ->where('partner_user.partner_id','=',$this->partner_id)
                            ->whereIn('users.id',$listUser)
                            ->get()
                            ->map( function ( $salesList ) {
                                return [ 
                                    'title' => $salesList->first_name." ".$salesList->last_name,
                                    'value' => $salesList->id,
                                ];
                            } )->pluck( 'title', 'value')
                            ->prepend(trans('lead.all'), '');
            }else{
                $salesList=User::join('partner_user','partner_user.user_id','=','users.id')
                ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
                ->where('partner_user.partner_id','=',$this->partner_id)
                ->get()
                ->map( function ( $salesList ) {
                    return [ 
                        'title' => $salesList->first_name." ".$salesList->last_name,
                        'value' => $salesList->id,
                    ];
                } )->pluck( 'title', 'value')
                ->prepend(trans('lead.all'), '');
            }
            Cache::put('salesListCache_2'.$this->partner_id.$listUserCache.$salesListCache, $salesList, now()->addMinutes(10));
        }

        if (Sentinel::check()) {
            
            $userData=$this->userRepository->getUser();
            $this->partner_id=$userData->partner_id;

            if(isset($sales_id) && $sales_id!=""){
                $id=$sales_id;
                $sales=array($sales_id);
            }else{
                $id=$userData->id;
                if($userData->user_id==1){
                    $sales=User::select('id')->where('partner_id','=',$this->partner_id)->get()->pluck( 'id')->toArray();
                    array_push($sales,0);
                }else{
                    $listSales=[];
                    if(isset($salesList) && count($salesList)>0){
                        foreach($salesList as $key=>$value){
                            if($key!=""){
                                $listSales[]=$key;
                            }
                        }
                    }
                    $sales=$listSales;
                }
            }
            if($sales!="" && count($sales)>0){
                $salesSearch=implode(",",$sales);
            }else{
                $salesSearch=$sales_id;
            }

            $domain=env('SESSION_DOMAIN');
            $cacheKey=md5($domain.$this->partner_id.$starting_date.$ending_date.$salesSearch);

            $staffDetail = $this->userRepository->find($id);
            //$customers = $this->companyRepository->getAll()->count();
            //$contracts = $this->contractRepository->getAll()->count();
            //$opportunities = $this->opportunityRepository->getAll()->count();
            //$products = Product::where('partner_id', $this->partner_id)->count();
            //Check tồng lead assign ->join('call_action_status','call_action_status.id','=')


            $totalLeadCache=cache('totalLeadNewLead2'.$cacheKey);
            if(isset($totalLeadCache) && $totalLeadCache!=""){
                $totalLead=$totalLeadCache;
            }else{
                $totalLead=Lead::whereIn('sales_person_id',$sales)->where('messenger', '!=', '')->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->where('group_id',45)->count();
                Cache::put('totalLeadNewLead2'.$cacheKey, $totalLead, now()->addMinutes(10));
            } 

            $totalLeadNotCareCache=cache('totalLeadNotCare'.$cacheKey);
            if(isset($totalLeadNotCareCache) && $totalLeadNotCareCache!=""){
                $totalLeadNotCare=$totalLeadNotCareCache;
            }else{
                $totalLeadNotCare=Lead::where('sales_person_id',0)->where('messenger', '!=', '')->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->where('group_id',45)->count();
                Cache::put('totalLeadNotCare'.$cacheKey, $totalLeadNotCare, now()->addMinutes(10));
            } 
            
            $totalCustomerCache=cache('totalCustomerCare'.$cacheKey);
            if(isset($totalCustomerCache) && $totalCustomerCache!=""){
                $totalCustomer=$totalCustomerCache;
            }else{
                $totalCustomer=Lead::whereIn('sales_person_id',$sales)->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->where('group_id',44)->count();
                Cache::put('totalCustomerCare'.$cacheKey, $totalCustomer, now()->addMinutes(1));
            }

            
            
            $LeadRoutingDataCache=cache('LeadRoutingDataCache'.$cacheKey);
            if(isset($LeadRoutingDataCache) && $LeadRoutingDataCache!=""){
                $totalAssign=$LeadRoutingDataCache;
            }else{
                $totalAssign=LeadRouting::whereIn('user_id',$sales)->where('date','>=',$starting_date)->where('date','<=',$ending_date)->sum('number'); 
                Cache::put('LeadRoutingDataCache'.$cacheKey, $totalAssign, now()->addMinutes(30));
            } 
            /*
            $totalLead=Lead::whereIn('sales_person_id',$sales)->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->where('group_id',45)->count();

            $totalLeadNotCare=Lead::where('sales_person_id',0)->where('messenger', '!=', '')->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->where('group_id',45)->count();
    

            $totalCustomer=Lead::whereIn('sales_person_id',$sales)->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->where('group_id',44)->count();

            $totalAssign=LeadRouting::whereIn('user_id',$sales)->where('date','>=',$starting_date)->where('date','<=',$ending_date)->sum('number');  */
            
            $list_lead_group = array();
            $listGroupLead=CallActionStatus::where("partner_id",$this->partner_id)->where('type',45)->get();
            foreach($listGroupLead as $listData){
                $verson="8".$daterange;
                $cacheKey=md5($this->partner_id.$listData["id"].implode(",",$sales).$ending_date.$starting_date.$verson);
                $leadCache=cache('leadGroupLeadCache2'.$verson.$cacheKey);
                $leadReplyCache=cache('leadGroupLeadReplyCache2'.$verson.$cacheKey);
                $leadAllCache=cache('leadGroupLeadAllCache2'.$verson.$cacheKey);
                $leadcountReply=0;
                    // $leadcountReply=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('group_id',$listData["id"])->where('type','oldCareReply')->sum('number');

                if(isset($leadCache) && $leadCache!="" && $leadReplyCache!="" && $leadAllCache!=""){
                    $leadcount=$leadCache;
                    $leadcountReply=$leadReplyCache;
                    $leadcountAll=$leadAllCache;
                }else{

                    $leadcount=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('group_id',$listData["id"])->where('type','oldCare')->sum('number');

                    $leadcountReply=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('group_id',$listData["id"])->where('type','oldCareReply')->sum('number');

                    $leadcountAll=ReportStaff::whereIn('user_id',$sales)->where('group_id',$listData["id"])->where('type','oldCare')->sum('number');

                    //$leadcount=Lead::select("leads.id")->join('logs','logs.lead_id','=','leads.id')->where('logs.created_at','>=',$starting_date)->where('logs.created_at','<=',$ending_date)->where('leads.created_at','>',$starting_date)->where('leads.created_at','<',$ending_date)->whereIn('leads.sales_person_id',$sales)->where('leads.partner_id', $this->partner_id)->where('leads.status', $listData["id"])->groupBy('logs.lead_id')->count();
                    Cache::put('leadGroupLeadCache2'.$verson.$cacheKey, $leadcount, now()->addMinutes(20));
                    Cache::put('leadGroupLeadReplyCache2'.$verson.$cacheKey, $leadcountReply, now()->addMinutes(20));
                    Cache::put('leadGroupLeadAllCache2'.$verson.$cacheKey, $leadcountAll, now()->addMinutes(20));

                } 
                $list_lead_group[] =
                    [
                        'id' =>$listData["id"],
                        'name' =>$listData["title"],
                        'color' =>$listData["color_bg"],
                        'leads'=>$leadcount,
                        'leadsReply'=>$leadcountReply,
                        'leadsAll'=>$leadcountAll
                        
                    ];
            }
            $listLeadTitle=array();
            $groupLeadTitle=implode(",", $listLeadTitle);
            $list_customer_group = array();
            $listGroupCustomer=CallActionStatus::where("partner_id",$this->partner_id)->where('type',44)->get();
            foreach($listGroupCustomer as $listData){
                $verson="report_011".$daterange;
                $clientcount=0;
                $clientCountReply=0;
                $clientCountAll=0;
                $cacheKey2=md5($this->partner_id.$listData["id"].implode(",",$sales).$ending_date.$starting_date);
                $leadGroupClientCache=cache('leadGroupClient'.$verson.$cacheKey2);
                $leadGroupClientReplyCache=cache('leadGroupClientReply'.$verson.$cacheKey2);
                $leadGroupClientAllCache=cache('leadGroupClientAll'.$verson.$cacheKey2);
                /*
                if($leadGroupClientCache!="" && $leadGroupClientReplyCache!="" && $leadGroupClientAllCache!=""){
                    $clientCount=$leadGroupClientCache;
                    $clientCountReply=$leadGroupClientReplyCache;
                    $clientCountAll=$leadGroupClientAllCache;
                }else{
                    $leadcount=Lead::select("leads.id")->join('tasks','tasks.lead_id','=','leads.id')->where('tasks.task_start','>=',$starting_date)->where('tasks.task_start','<=',$ending_date)->whereIn('leads.sales_person_id',$sales)->where('leads.partner_id', $this->partner_id)->where('leads.status', $listData["id"])->count();

                    $clientCountReply=ReportStaff::whereIn('user_id',$sales)
                    ->where('date_create','>=', $starting_date)
                    ->where('date_create','<=',$ending_date)
                    ->where('group_id',$listData["id"])->where('type','totalClientOldHasReply')->sum('number');
                    
                    $clientcount=ReportStaff::whereIn('user_id',$sales)
                    ->where('date_create','>=', $starting_date)
                    ->where('date_create','<=',$ending_date)
                    ->where('group_id',$listData["id"])->where('type','totalClientOld')->sum('number');

                    $clientcountAll=ReportStaff::whereIn('user_id',$sales)->where('group_id',$listData["id"])->where('type','totalClientOld')->sum('number');

                    Cache::put('leadGroupClient'.$verson.$cacheKey2, $clientcount, now()->addMinutes(20));
                    Cache::put('leadGroupClientReply'.$verson.$cacheKey2, $clientCountReply, now()->addMinutes(20));
                    Cache::put('leadGroupClientAll'.$verson.$cacheKey2, $clientcountAll, now()->addMinutes(20));
                } 
                */
                $clientCountReply=ReportStaff::whereIn('user_id',$sales)
                    ->where('date_create','>=', $starting_date)
                    ->where('date_create','<=',$ending_date)
                    ->where('group_id',$listData["id"])->where('type','totalClientOldHasReply')->sum('number');

                $clientcount=ReportStaff::whereIn('user_id',$sales)
                    ->where('date_create','>=', $starting_date)
                    ->where('date_create','<=',$ending_date)
                    ->where('group_id',$listData["id"])->where('type','totalClientOld')->sum('number');

                $clientcountAll=ReportStaff::whereIn('user_id',$sales)->where('group_id',$listData["id"])->where('type','totalClientOld')->sum('number');
                
                $list_customer_group[] =
                    [
                        'id' =>$listData["id"],
                        'name' =>$listData["title"],
                        'color' =>$listData["color_bg"],
                        'leads'=>$clientcount,
                        'leadsReply'=>$clientCountReply, 
                        'leadsAll'=>$clientcountAll
                    ];
            }
            $listClientTitle=array();
            foreach($listGroupLead as $listData){
                $listClientTitle[]='"'.$listData["title"].'"';
            }
            $groupClientTitle=implode(",", $listClientTitle);
            $cacheCode="cache1".md5($this->partner_id.implode(",",$sales).$ending_date.$starting_date);


            //$cacheTotalOrder=md5($this->partner_id.implode(",",$sales).$ending_date.$starting_date);
            $cacheTotalLeadOld=cache('TotalLeadOld1'.$cacheCode);
            if(isset($cacheTotalLeadOld) && $cacheTotalLeadOld!=""){
                $totalLeadOld=$cacheTotalLeadOld;
            }else{
                $totalLeadOld=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','oldCare')->sum('number');
                Cache::put('TotalLeadOld'.$cacheCode, $totalLeadOld, now()->addMinutes(20));
            } 

            $cacheTotalLeadOld=cache('TotalLeadOldReply1'.$cacheCode);
            if(isset($cacheTotalLeadOld) && $cacheTotalLeadOld!=""){
                $totalLeadReply=$cacheTotalLeadOld;
            }else{
                $totalLeadReply=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','oldCareReply')->sum('number');
                Cache::put('TotalLeadOldReply'.$cacheCode, $totalLeadReply, now()->addMinutes(20));
            } 
            
            
            $cacheReportTags=cache('ReportTags2'.$cacheCode);
            if(isset($cacheReportTags) && $cacheReportTags!=""){
                $totalReportTags=$cacheReportTags;
            }else{
                $totalReportTags=ReportTags::whereIn('user_id',$sales)->where('created_at','>=', $starting_date)->where('created_at','<=',$ending_date)->count('id');
                Cache::put('ReportTags2'.$cacheCode, $totalReportTags, now()->addMinutes(20));
            }

            $totalCustomerOldCache=cache('totalCustomerOldCare'.$cacheKey);
            if(isset($totalCustomerOldCache) && $totalCustomerOldCache!=""){
                $totalOldCustomer=$totalCustomerOldCache;
            }else{
                $totalOldCustomer=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','oldClientToBuy')->sum('number');
                Cache::put('totalCustomerOldCare'.$cacheKey, $totalOldCustomer, now()->addMinutes(30));
            }

            $totalCustomerNewCache=cache('totalCustomerNewCare2'.$cacheKey);
            if(isset($totalCustomerNewCache) && $totalCustomerNewCache!=""){
                $totalNewCustomer=$totalCustomerNewCache;
            }else{
                $totalNewCustomer=ReportStaff::whereIn('user_id',$sales)->whereDate('date_create','>=', $starting_date)->whereDate('date_create','<=',$ending_date)->where('type',"newClientToBuy")->sum('number');
                Cache::put('totalCustomerNewCare2'.$cacheKey, $totalNewCustomer, now()->addMinutes(30));
            }
            
            
            $totalLeadOldDate = cache('totalLeadReplyCache3'.$cacheCode);
            $totalLeadReplyDay = cache('totalLeadCache3'.$cacheCode);
            $start_date_list = cache('start_date2'.$cacheCode);;

            if(isset($totalLeadReplyDay) && $totalLeadReplyDay!="" && $totalLeadOldDate!="" && $start_date_list!=""){
                $totalLeadReplyData = $totalLeadReplyDay;
                $totalLeadOldData = $totalLeadOldDate;
                $daysListData = $start_date_list;
            }else{ 
                $start = strtotime(date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d') ))));
                $end = strtotime(date('Y-m-d', strtotime('+1 day')));
                $totalLeadReplyDataList = null;
                $totalLeadOldDataList = null;
                $daysListDataList = null;
                while($start < $end)
                {
                    $start_date=date("Y-m-d",$start);
                    $end_date=$start_date." 23:59:59";//date("Y-m-d",$end);

                    $totalLeadReplyDate=ReportStaff::whereIn('user_id',$sales)
                    ->where('date_create','>=', $start_date)
                    ->where('date_create','<=',$end_date)->where('type','totalLeadOldHasReply')->sum('number');

                    $totalLeadOldDate=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $start_date)->where('date_create','<=',$end_date)->where('type','totalLeadOld')->sum('number');

                    // $totalLeadOldAll=ReportStaff::whereIn('user_id',$sales)->where('type','totalLeadOld')->sum('number');

                    $totalLeadReplyDataList[] = $totalLeadReplyDate;
                    $totalLeadOldDataList[] = $totalLeadOldDate;
                    $daysListDataList[] = $start_date;
                    $start = strtotime("+1 day", $start);
                }
                $totalLeadReplyData = $totalLeadReplyDataList;
                $totalLeadOldData = $totalLeadOldDataList;
                $daysListData = $daysListDataList;

                Cache::put('totalLeadReplyCache3'.$cacheCode, $totalLeadReplyData, now()->addMinutes(300));
                Cache::put('totalLeadCache3'.$cacheCode, $totalLeadOldData, now()->addMinutes(300));
                Cache::put('start_date3'.$cacheCode, $daysListData, now()->addMinutes(300));
            }

            if(count($daysListData)>0){
                for($i=0;$i<count($daysListData);$i++){
                    $oldLeadOldDays[] = $totalLeadOldData[$i];
                    $newLeadReplyDays[] = $totalLeadReplyData[$i];
                    $daysList[] =$daysListData[$i];
                }
            }
            /*
            var_dump($newLeadReplyDays[0]);

            var_dump($daysList[0]);
            var_dump($oldLeadOldDays[0]);
            die(); */
            $cacheTotalOrder=md5($this->partner_id.implode(",",$sales).$ending_date.$starting_date);
            $cacheTotalOrderFile=cache('TotalOrder'.$cacheTotalOrder);
            if(isset($cacheTotalOrderFile) && $cacheTotalOrderFile!=""){
                $totalOrder=$cacheTotalOrderFile;
            }else{
                $totalOrder=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalInvoice')->sum('number');
                Cache::put('TotalOrder'.$cacheTotalOrder, $totalOrder, now()->addMinutes(20));
            } 


            $cacheTotalRevenue=md5($this->partner_id.implode(",",$sales).$ending_date.$starting_date);
            $cacheTotalRevenueFile=cache('TotalRevenus1'.$cacheTotalRevenue);
            if(isset($cacheTotalRevenueFile) && $cacheTotalRevenueFile!=""){
                $totalRevenus=$cacheTotalRevenueFile;
            }else{
                $totalRevenus=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalInvoice')->sum('summary_invoice');
                Cache::put('TotalRevenus1'.$cacheTotalRevenue, $totalRevenus, now()->addMinutes(20));
            } 

            $cacheTotalInvoice=md5($this->partner_id.implode(",",$sales).$ending_date.$starting_date);
            $cacheTotalInvoiceFile=cache('TotalInvoice'.$cacheTotalInvoice);
            if(isset($cacheTotalInvoiceFile) && $cacheTotalInvoiceFile!=""){
                $totalInvoice=$cacheTotalInvoiceFile;
            }else{
                $totalInvoice=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalInvoice')->sum('number');
                Cache::put('TotalInvoice'.$cacheTotalInvoice, $totalInvoice, now()->addMinutes(20));
            } 

            
            $list_client_group_order = array();
            $verson="9".$daterange;

            foreach($listGroupCustomer as $listData){
                $cacheKey=md5($this->partner_id.$listData["id"].implode(",",$sales).$ending_date.$starting_date);
                $leadOldCache=cache('leadOldOrderCount2'.$verson.$cacheKey);
                $leadNewCache=cache('leadNewOrderCount2'.$verson.$cacheKey);
                $leadAllCache=cache('leadOrderCountAll2'.$verson.$cacheKey);
                
                if(isset($leadOldCache) && $leadOldCache!="" && $leadAllCache!=""  && $leadNewCache!=""){
                    $orderOldCount=$leadOldCache;
                    $orderNewCount=$leadNewCache;
                    $orderCountAll=$leadAllCache;
                }else{ //select("leads.id")->
                    $orderOldCount=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('group_id',$listData["id"])->where('type','oldClientToBuy')->sum('number');
                    $orderNewCount=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('group_id',$listData["id"])->where('type','newClientToBuy')->sum('number');
                    $orderCountAll=ReportStaff::whereIn('user_id',$sales)->where('group_id',$listData["id"])->whereIn('type',array('oldClientToBuy','newClientToBuy'))->sum('number');
                    Cache::put('leadNewOrderCount2'.$verson.$cacheKey, $orderNewCount, now()->addMinutes(60));
                    Cache::put('leadOldOrderCount2'.$verson.$cacheKey, $orderOldCount, now()->addMinutes(60));
                    Cache::put('leadOrderCountAll2'.$verson.$cacheKey, $orderCountAll, now()->addMinutes(60));

                } 
                $list_client_group_order[] =
                    [
                        'id' =>$listData["id"],
                        'name' =>$listData["title"],
                        'orderOld'=>$orderOldCount,
                        'orderNew'=>$orderNewCount,
                        'orderAll'=>$orderCountAll
                    ];
                    
            }
            /*
            $list_client_group_comnunicate = array();
            foreach($listGroupCustomer as $listData){
                $cacheKey=md5($this->partner_id.$listData["id"].implode(",",$sales).$ending_date.$starting_date);
                $leadCache=cache('leadTuongTac5'.$cacheKey);
                if(isset($leadCache) && $leadCache!=""){
                    $orderCount=$leadCache;
                }else{ //select("leads.id")->
                    $tuongtacCount=Lead::join('tasks','tasks.lead_id','=','leads.id')->where('tasks.task_start','>=',$starting_date)->where('tasks.task_start','<=',$ending_date)->where('leads.created_at','<',$starting_date)->whereIn('leads.sales_person_id',$sales)->where('leads.partner_id', $this->partner_id)->where('leads.status', $listData["id"])->count('leads.id');
                    Cache::put('leadTuongTac5'.$cacheKey, $tuongtacCount, now()->addMinutes(20));
                } 
                $list_client_group_comnunicate[] =
                    [
                        'id' =>$listData["id"],
                        'name' =>$listData["title"],
                        'totalComnuicate'=>$tuongtacCount
                    ];
            } */

            //Thoi gian xu lý lead

            $cacheTotalLeadBung=cache('TotalLeadBung'.$cacheCode);
            if(isset($cacheTotalLeadBung) && $cacheTotalLeadBung!=""){
                $totalLeadBung=$cacheTotalLeadBung;
            }else{
                $totalLeadBung=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalLeadBung')->sum('number');
                Cache::put('TotalLeadBung'.$cacheCode, $totalLeadBung, now()->addMinutes(120));
            } 

            $cacheTotalClientBung=cache('TotalClientBung'.$cacheCode);
            if(isset($cacheTotalClientBung) && $cacheTotalClientBung!=""){
                $totalClientBung=$cacheTotalClientBung;
            }else{
                $totalClientBung=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalClientBung')->sum('number');
                Cache::put('TotalClientBung'.$cacheCode, $totalClientBung, now()->addMinutes(120));
            } 

            $cacheTimeAcceptLeadB=cache('TimeAcceptLead'.$cacheCode);
            if(isset($cacheTimeAcceptLeadB) && $cacheTimeAcceptLeadB!=""){
                $timeAcceptLead=$cacheTimeAcceptLeadB;
            }else{
                $timeAcceptLead=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','timeAcceptLead')->avg('number');
                Cache::put('TimeAcceptLead'.$cacheCode, $timeAcceptLead, now()->addMinutes(120));
            }
            
            $cacheTimeProcssLead=cache('TimeProcessLead'.$cacheCode);
            if(isset($cacheTimeProcssLead) && $cacheTimeProcssLead!=""){
                $timeProcessLead=$cacheTimeProcssLead;
            }else{
                $timeProcessLead=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','timeProcessLead')->avg('number');
                Cache::put('TimeProcessLead'.$cacheCode, $timeProcessLead, now()->addMinutes(120));
            }
                /* Lead to client */
                $cacheleadToClient=cache('leadToClient1'.$cacheCode);
            if(isset($cacheleadToClient) && $cacheleadToClient!=""){
                $leadToClient=$cacheleadToClient;
            }else{
                $leadToClient = Lead::select('id')
                ->whereIn('sales_person_id',$sales)
                ->where('partner_id', $this->partner_id)
                ->where('convert_customer', 1)
                ->where(function ($query3) use($starting_date, $ending_date) {
                        if($starting_date!=""){
                            $query3->where('created_at','>=',$starting_date);
                        }
                        if($ending_date!=""){
                            $query3->where('created_at','<=',$ending_date);
                        }
                })->count();
                Cache::put('leadToClient1'.$cacheCode, $leadToClient, now()->addMinutes(120));
            }

                /* Lead to client */
                $cacheleadToClientBuy=cache('leadToClientBuy'.$cacheCode);
            if(isset($cacheleadToClientBuy) && $cacheleadToClientBuy!=""){
                $leadToClientBuy=$cacheleadToClientBuy;
            }else{
                $leadToClientBuy = Lead::select('id')
                ->whereIn('sales_person_id',$sales)
                ->where('partner_id', $this->partner_id)
                ->where('convert_customer', 1)
                ->where('group_id', 44)
                ->where(function ($query3) use($starting_date, $ending_date) {
                        if($starting_date!=""){
                            $query3->where('created_at','>=',$starting_date);
                        }
                        if($ending_date!=""){
                            $query3->where('created_at','<=',$ending_date);
                        }
                })->count();
                Cache::put('leadToClientBuy'.$cacheCode, $leadToClientBuy, now()->addMinutes(120));
            }
            /* new lead group on day */

                /* Lead đã tiếp nhận */
                $cacheTotalLeadAccept=cache('totalLeadAccept'.$cacheCode);
            if(isset($cacheTotalLeadAccept) && $cacheTotalLeadAccept!=""){
                $totalLeadAccept=$cacheTotalLeadAccept;
            }else{
                $totalLeadAccept = LeadAssignStatus::select('id')
                ->whereIn('user_id',$sales)
                ->where('status', 1)
                ->where(function ($query3) use($starting_date, $ending_date) {
                        if($starting_date!=""){
                            $query3->where('created_at','>=',$starting_date);
                        }
                        if($ending_date!=""){
                            $query3->where('created_at','<=',$ending_date);
                        }
                })->count();
                Cache::put('totalLeadAccept'.$cacheCode, $totalLeadAccept, now()->addMinutes(120));
            }
            /* new lead group on day */
            $cacheKeyGroup=md5("v12".$this->partner_id.implode(",",$sales).$ending_date.$starting_date);
            $leadCache=cache('leadGroupNewLeadCache3'.$cacheKeyGroup);
            $leadCacheAll=cache('leadGroupNewLeadAllCache3'.$cacheKeyGroup);
            if(isset($leadCache) && $leadCache!="" && isset($leadCacheAll) && $leadCacheAll!=""){
                $totalLeadCare=$leadCache;
                $totalLeadCareAll=$leadCacheAll;
            }else{
                $totalLeadCare=Lead::select('leads.status', DB::raw('COUNT(lead_assign_status.id) as totalLeadCare'))
                ->join('lead_assign_status','lead_assign_status.lead_id','leads.id')
                ->whereIn('lead_assign_status.user_id',$sales)
                ->where('leads.created_at','>=',$starting_date)
                ->where('leads.created_at','<=',$ending_date)
                ->where('lead_assign_status.status',1)
                //->where('status', $listData["id"])
                ->groupBy('leads.status')->get();//
                //->pluck('status', 'totalLeadCare')->toArray();

                $totalLeadCareAll=Lead::select('status', DB::raw('COUNT(id) as totalLeadCare'))
                ->whereIn('sales_person_id',$sales)
                ->groupBy('status')->get();//->pluck('status', 'totalLeadCare')->toArray();
                // ->where('status', $listData["id"])
                //->count("id");
                Cache::put('leadGroupNewLeadCache3'.$cacheKeyGroup, $totalLeadCare, now()->addMinutes(60));
                Cache::put('leadGroupNewLeadAllCache3'.$cacheKeyGroup, $totalLeadCareAll, now()->addMinutes(60));

            } 
            //$listDataLeadCare=null;
            //$listDataTotalLeadCare=$totalLeadCareAll;
            
            $listDataLeadCare=array();
            $listDataTotalLeadCare=array();
            
            if(isset($totalLeadCare) && $totalLeadCare!=""){
                foreach($totalLeadCare as $listLeadCareData){
                    $listDataLeadCare[$listLeadCareData["status"]]=$listLeadCareData["totalLeadCare"];
                }
            }
        
            if(isset($totalLeadCareAll) && $totalLeadCareAll!=""){
                foreach($totalLeadCareAll as $listLeadAllCareData){
                    $listDataTotalLeadCare[$listLeadAllCareData["status"]]=$listLeadAllCareData["totalLeadCare"];
                }
                
            }
            
            $list_lead_group_new = array();
            if(isset($listDataLeadCare[0]) && $listDataLeadCare[0]!="" && isset($listDataTotalLeadCare[0]) && $listDataTotalLeadCare[0]!=""){
                $list_lead_group_new[]=
                [
                    'id' =>0,
                    'name' =>"Chưa phân nhóm",
                    'color' =>"#ff0000",
                    'leads'=>$listDataLeadCare[0],
                    'leadsAll'=>$listDataTotalLeadCare[0]
                ];
            }
            
            foreach($listGroupLead as $listData){
                $dataLead=0;
                if(isset($listDataLeadCare[$listData["id"]]) && $listDataLeadCare[$listData["id"]]!=0){
                    $dataLead=$listDataLeadCare[$listData["id"]];
                }
                $dataTotalLead=0;
                if(isset($listDataTotalLeadCare[$listData["id"]]) && $listDataTotalLeadCare[$listData["id"]]!=0){
                    $dataTotalLead=$listDataTotalLeadCare[$listData["id"]];
                }
                $list_lead_group_new[]=
                [
                    'id' =>$listData["id"],
                    'name' =>$listData["title"],
                    'color' =>$listData["color_bg"],
                    'leads'=>$dataLead,
                    'leadsAll'=>$dataTotalLead
                ];
            }
            $listLeadTitle=array();
           
            $cacheTotalMessengerOld=cache('TotalMessengerOld'.$cacheCode);
            if(isset($cacheTotalMessengerOld) && $cacheTotalMessengerOld!=""){
                $totalMessengerOld=$cacheTotalMessengerOld;
            }else{
                $totalMessengerOld=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalMessOld')->sum('number');
                Cache::put('TotalMessengerOld'.$cacheCode, $totalMessengerOld, now()->addMinutes(120));
            } 
            $cacheTotalMessengerNew=cache('TotalMessengerNew'.$cacheCode);
            if(isset($cacheTotalMessengerNew) && $cacheTotalMessengerNew!=""){
                $totalMessengerNew=$cacheTotalMessengerNew;
            }else{
                $totalMessengerNew=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalMessNews')->sum('number');
                Cache::put('TotalMessengerNew'.$cacheCode, $totalMessengerNew, now()->addMinutes(120));
            } 

            $cacheTotalVistor=cache('TotaltotalVistor1'.$cacheCode);
            if(isset($cacheTotalVistor) && $cacheTotalVistor!=""){
                $totalVistor=$cacheTotalVistor;
            }else{
                $totalVistor=ReportStaff::where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','visictorStore')->sum('number');
                Cache::put('TotaltotalVistor1'.$cacheCode, $totalVistor, now()->addMinutes(120));
            } 

            
            /*
            $cacheGroupClient=md5($this->partner_id.implode(",",$sales).$ending_date.$starting_date);
            $cacheGroupClientFile=cache('GroupClient'.$cacheGroupClient);
            if(isset($cacheGroupClientFile) && $cacheGroupClientFile!=""){
                $groupClient=$cacheGroupClientFile;
            }else{
                $totalInvoice=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalInvoice')->sum('number');
                Cache::put('TotalInvoice'.$cacheTotalInvoice, $totalInvoice, now()->addMinutes(20));
            } */
            
            //Call
            //$callTotal= $this->callRepository->getAll()->where('user_id',$id)->count();
            //$callTotalMissing= $this->callRepository->getAll()->where('duration','<=', 3)->where('user_id',$id)->count();
            //$callTotalSuccess= $this->callRepository->getAll()->where('duration','>', 3)->where('user_id',$id)->count();
            //End call
            //$totalLead = $this->leadRepository->getAll()->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->count(); //New lead
            
            //$opportunity_new = $this->leadRepository->getAll()->where('status', 0)->where('partner_id', $this->partner_id)->where('sales_person_id',$id)->count(); //New lead
            //$opportunity_negotiation = $this->leadRepository->getAll()->where('sales_person_id',$id)->whereIn('status', array(2,3,4,5))->count();
            // $opportunity_won = $this->leadRepository->getAll()->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->whereIn('status', array(6,7))->count();
            //$opportunity_loss = $this->leadRepository->getAll()->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->whereIn('status', array(8,9,10,11))->count();
            // $opportunity_expired = $this->leadRepository->getAll()->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->where('status', 12)->count();
            // Tỷ lệ chuyển đổi
            // $ctrcall=0;
            //$ctrleadregister=0;
            //if($callTotalSuccess>0){
                //  $ctrcall=round(($opportunity_won/$callTotalSuccess)*100,2);

            //}
            //End ty lệ
            // Tỷ lệ chuyển đổi
            //$ctrleadregister=round(($opportunity_won/$totalLead)*100,2);
            //End ty lệ
            $staff=0;
            // if($this->userRepository->getAllUserArray($id)){
            //       $staff=$this->userRepository->getAllUserArray($id)->count();

            //   }
            return view('user.report.summary', compact('totalLead', 'totalCustomer','totalLeadOld', 'totalLeadReply','totalOrder','totalRevenus','list_lead_group','list_customer_group', 'staffDetail', 'groupLeadTitle', 'groupClientTitle', 'salesList', 'listGroupLead', 'daterange', 'totalLeadNotCare', 'totalAssign', 'salesSearch', 'list_client_group_order', 'totalLeadBung', 'totalClientBung', 'timeAcceptLead', 'timeProcessLead', 'oldLeadOldDays', 'newLeadReplyDays', 'daysList', 'leadToClient', 'date_select', 'list_lead_group_new', 'totalReportTags', 'totalMessengerNew', 'totalMessengerOld', 'leadToClientBuy', 'totalLeadAccept', 'totalOldCustomer', 'totalNewCustomer', 'starting_date', 'ending_date', 'totalVistor'));

        }
    }
	public function memberUsers($user_id){
		$memberTeam=array($user_id);
        if($user_id!=0 && $user_id!=0){
            $dataTeam=Salesteam::where('team_leader',$user_id)->first();
			if($dataTeam!=""){
				$team_id=$dataTeam["id"];
				$memberTeam=SalesteamMember::where('salesteam_id', $team_id)->get()->pluck( 'user_id')->toArray();
				array_push($memberTeam, $user_id);
			}
		}
		return $memberTeam;
        //return $this->belongsToMany(User::class,'sales_team_members');
    }
}
