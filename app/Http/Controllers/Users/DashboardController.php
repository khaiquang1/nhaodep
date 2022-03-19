<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
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
use App\Repositories\UserRepository;
use App\Models\LeadAssignStatus;
use App\Models\Product;
use App\Models\Task;
use App\Models\User;
use Cache;


use App\Models\Logs;
use App\Models\Lead;
use App\Models\Cookie;

use App\Models\Partner;
use App\Models\PartnerUser;

use App\Models\LogsCall;
use App\Models\UserLogin;

use Illuminate\Pagination\Paginator;

use Carbon\Carbon;
use Illuminate\Http\Request;

use Sentinel;

class DashboardController extends UserController
{
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

    /**
     * DashboardController constructor.
     * @param LeadRepository $leadRepository
     * @param OpportunityRepository $opportunityRepository
     * @param UserRepository $userRepository
     * @param CallRepository $callRepository
     * @param MeetingRepository $meetingRepository
     * @param QuotationRepository $quotationRepository
     * @param SalesOrderRepository $salesOrderRepository
     * @param ContractRepository $contractRepository
     * @param CompanyRepository $companyRepository
     * @param SalesTeamRepository $salesTeamRepository
     * @param ProductRepository $productRepository
     * @param InvoiceRepository $invoiceRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(LeadRepository $leadRepository,
                                OpportunityRepository $opportunityRepository,
                                UserRepository $userRepository,
                                CallRepository $callRepository,
                                MeetingRepository $meetingRepository,
                                QuotationRepository $quotationRepository,
                                SalesOrderRepository $salesOrderRepository,
                                ContractRepository $contractRepository,
                                CompanyRepository $companyRepository,
                                SalesTeamRepository $salesTeamRepository,
                                ProductRepository $productRepository,
                                InvoiceRepository $invoiceRepository,
                                OptionRepository $optionRepository)
    {
        parent::__construct();
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
    }

    public function index(Request $request)
    {
        $userData=$this->userRepository->getUser();

        if($userData->customer_care_old==1){
            return redirect( "deal");
        }else{
            return redirect( "lead");
        }

        if($userData->user_id!=1){
            return redirect( "lead");
        }else{
            $userData=$this->userRepository->getUser();
            $this->partner_id=$userData->partner_id;
            $reporttype=1;
            if( $this->partner_id){
            $partner=Partner::where('id',$this->partner_id)->first();
            if($partner){
                    $reporttype=$partner["report_type"];
            }else{
                die("Vui lòng liên hê với chúng tôi để được hổ trợ");
            }
            }
            if($reporttype==2){
                return redirect( "index2" );
            }else{
                return redirect( "index1" );
            }
        }
    }

    public function index1(Request $request){
        $userData=$this->userRepository->getUser();
        return redirect( "lead");
        if($userData->user_id!=1){
            return redirect( "lead");
        }else{
            $userData=$this->userRepository->getUser();
            $this->partner_id=$userData->partner_id;
            if (Sentinel::check()) {
                $date  = addslashes($request->report_date) ;
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
                $product_id=$request->product_id;
                $utm_source=$request->UTM_Source;
    
                $customers = $this->companyRepository->getAll()->count();
                $contracts = $this->contractRepository->getAll()->count();
                $opportunities = $this->opportunityRepository->getAll()->count();
                //Product list
                $productListSearch=Product::where('partner_id','=',$this->partner_id)
                ->where(function ($query) use ($product_id){
                    if($product_id){
                        $query->where('id',$product_id);
                    }
                })
                ->orderBy('product_name', 'desc')->get();
    
                $productList=Product::where('partner_id','=',$this->partner_id)->orderBy('product_name', 'desc')->get();
                $productSelectData=$productList->map( function ( $productList ) {
                    return [
                        'title' => $productList->product_name,
                        'value' => $productList->id,
                    ];
                } )->pluck( 'title', 'value')
                ->prepend(trans('lead.all'), '');
                //Get list ID product of partner
                $productData=array();
                if($productListSearch){
                    foreach($productListSearch as $product){
                        $productData[]=$product->id;
                       
                    }
                }
                // Get Product Of User
                // Lead  sourc
               // $products = $this->productRepository->getAll()->count();
                $products = Product::where('partner_id', $this->partner_id)->count();
                
                $opportunity_leads = array();
                $stages = array();
                for($i=11;$i>=0;$i--)
                {
                    $opportunity_leads[] =
                        [
                            'month' =>Carbon::now()->subMonth($i)->format('M'),
                            'year' =>Carbon::now()->subMonth($i)->format('Y'),
                            'opportunity'=>$this->leadRepository->getAll()->where('partner_id', $this->partner_id)->where('created_at','LIKE',
                                    Carbon::now()->subMonth($i)->format('Y-m').'%')->count(),
                            'leads'=>$this->leadRepository->getAll()->where('partner_id', $this->partner_id)->where('created_at','LIKE',
                                  Carbon::now()->subMonth($i)->format('Y-m').'%')->whereIn('status',array(6,7))->count()
                        ];
                } 	
    
                //Call
                $callTotal= LogsCall::leftJoin('leads','leads.id','=','logs_call.lead_id')->where('logs_call.created_at','>=', $starting_date)->where('leads.locked',0)->where('logs_call.created_at','<=', $ending_date)->where('leads.partner_id','=',$this->partner_id)->count();
                $callTotalMissing=LogsCall::leftJoin('leads','leads.id','=','logs_call.lead_id')->where('logs_call.created_at','>=', $starting_date)->where('logs_call.created_at','<=', $ending_date)->where('leads.partner_id','=',$this->partner_id)->whereRaw('logs_call.end_time-logs_call.start_time<=5')->count();
                //$callTotalMissing= $this->callRepository->getAll()->where('date','>=', $starting_date)->where('date','<=', $ending_date)->where('duration','<=', 3)->count();
              //  $callTotalSuccess= $this->callRepository->getAll()->where('date','>=', $starting_date)->where('date','<=', $ending_date)->where('duration','>', 3)->count();
                $callTotalSuccess=LogsCall::leftJoin('leads','leads.id','=','logs_call.lead_id')->where('logs_call.created_at','>=', $starting_date)->where('logs_call.created_at','<=', $ending_date)->where('leads.partner_id','=',$this->partner_id)->where('leads.locked',0)->whereRaw('logs_call.end_time-logs_call.start_time>5')->count();
                //End call
                $totalLead = $this->leadRepository->getAll()->where('partner_id', $this->partner_id)->where('locked',0)->where('created_at','>=', $starting_date)->where('created_at','<=', $ending_date)->count(); //New lead
                
                $opportunity_new = $this->leadRepository->getAll()->where('partner_id', $this->partner_id)->where('created_at','>=', $starting_date)->where('locked',0)->where('created_at','<=', $ending_date)->where('status', 0)->count(); //New lead
                $opportunity_negotiation = $this->leadRepository->getAll()->where('partner_id', $this->partner_id)->where('created_at','>=', $starting_date)->where('locked',0)->where('created_at','<=', $ending_date)->whereIn('status', array(2,3,4,5))->count();
                $opportunity_won = $this->leadRepository->getAll()->where('partner_id', $this->partner_id)->where('created_at','>=', $starting_date)->where('locked',0)->where('created_at','<=', $ending_date)->whereIn('status', array(6,7))->count();
                $opportunity_loss = $this->leadRepository->getAll()->where('locked',0)->where('partner_id', $this->partner_id)->where('created_at','>=', $starting_date)->where('created_at','<=', $ending_date)->whereIn('status', array(8,9,10,11))->count();
                $opportunity_expired = $this->leadRepository->getAll()->where('locked',0)->where('partner_id', $this->partner_id)->where('created_at','>=', $starting_date)->where('created_at','<=', $ending_date)->where('status', 12)->count();
                $leadGroupQuery =Lead::selectRaw('count(id) AS totalUTM, UTM_Source')->where('partner_id', $this->partner_id)->where('locked',0)
                ->where(function ($query) use ($product_id){
                    if($product_id){
                        $query->where('product_id',$product_id);
                    }
                })
                ->groupBy('UTM_Source');
                $leadGroupSource =$leadGroupQuery->get();
                $leadGroupListSearch =$leadGroupQuery->get()
                ->map( function ( $leadGroupSource){
                    return [
                        'title' => $leadGroupSource->UTM_Source,
                        'value' => $leadGroupSource->UTM_Source,
                    ];
                    }
                )->pluck( 'title', 'value')
                ->prepend(trans('lead.all'), '');
                // Tỷ lệ chuyển đổi
                $ctrcall=0;
                $ctrleadregister=0;
                if($callTotalSuccess>0){
                    $ctrcall=round(($opportunity_won/$callTotalSuccess)*100,2);
                }
                //End ty lệ
                // Tỷ lệ chuyển đổi
                if($totalLead>0){
                    $ctrleadregister=round(($opportunity_won/$totalLead)*100,2);
                }
                //End ty lệ
                //$staff=$this->userRepository->getAllUserArray()->count();
                $staff = User::where('partner_id', $this->partner_id)->count();
                
                //Check tồng lead assign ->join('call_action_status','call_action_status.id','=')
                $leadAssign =array(); 
                $from = date("Y-m-d", strtotime("-7 day"));
                $to = date('Y-m-d');
               // $leadAssign = LeadAssignStatus::selectRaw('DATE_FORMAT(lead_assign_status.date_create, "%Y-%m-%d") as date_assign, (select count(id) from lead_assign_status where status=1 and date_create>=date_assign and date_create<(date_assign+INTERVAL 1 DAY)) as leadAccept, (select count(id) from lead_assign_status where status=0 and date_create>=date_assign and date_create<(date_assign+INTERVAL 1 DAY)) as leadNoAccept')->join('leads','leads.id','=','lead_assign_status.lead_id')->where('leads.partner_id',$this->partner_id)->whereBetween('lead_assign_status.date_create', [$from, $to])->groupBy('date_assign')->get();
                $cookieSummary='';
                //Total Cookie on Day
                /*
                if($productData &&  count($productData)>0){
                  $cookieSummary = Cookie::selectRaw('DATE_FORMAT(cookie.create_date, "%Y-%m-%d") as date_sum, (select count(id) from cookie where create_date>=date_sum and create_date<(date_sum+INTERVAL 1 DAY) and product_id in('.implode(",",$productData).')) as totalCookie, (select count(id) from leads where created_at>=date_sum and created_at<(date_sum+INTERVAL 1 DAY) and cookie_id!="" and product_id in('.implode(",",$productData).')) as leadTotal, (select count(id) from leads where status in(6,7) and created_at>=date_sum and created_at<(date_sum+INTERVAL 1 DAY) and cookie_id!="" and product_id in('.implode(",",$productData).')) as leadTotalSuccess')->whereIn('cookie.product_id',$productData)->whereBetween('cookie.create_date', [$from, $to])->groupBy('date_sum')->get();
                }else{
                    $cookieSummary = "";
                } */
                
                //end
                return view('user.index', compact('customers', 'contracts', 'opportunities','products', 'staff','opportunity_leads','stages','opportunity_new','opportunity_negotiation','opportunity_won','opportunity_loss', 'callTotal', 'callTotalMissing', 'callTotalSuccess', 'totalLead', 'ctrcall','ctrleadregister','leadGroupSource', 'leadAssign', 'date_select', 'productSelectData', 'cookieSummary', 'leadGroupListSearch', 'opportunity_expired'));
            }
        }

    }
    public function index2(Request $request)
    {
        $userData=$this->userRepository->getUser();
        
        if(!Sentinel::getUser()->hasAccess(['dashboard.read'])){
            return redirect( "staff/".$userData->id."/dashboard");
        }

        $this->partner_id=$userData->partner_id;
        if (Sentinel::check()) {
            $date  = addslashes($request->report_date) ;
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
            $user_id=$request->sales_id;
            $utm_source=$request->function;

            $customers = $this->companyRepository->getAll()->count();
            $contracts = $this->contractRepository->getAll()->count();
            $opportunities = $this->opportunityRepository->getAll()->count();
            //Product list
            /*
            $productListSearch=Product::where('partner_id','=',$this->partner_id)
            ->where(function ($query) use ($product_id){
                if($product_id){
                    $query->where('id',$product_id);
                }
            })
            ->orderBy('product_name', 'desc')->get();
            

            $productList=Product::where('partner_id','=',$this->partner_id)->orderBy('product_name', 'desc')->get();
            $productSelectData=$productList->map( function ( $productList ) {
                return [
                    'title' => $productList->product_name,
                    'value' => $productList->id,
                ];
            } )->pluck( 'title', 'value')
            ->prepend(trans('lead.all'), ''); 
            //Get list ID product of partner
            $productData=array();
            if($productListSearch){
                foreach($productListSearch as $product){
                    $productData[]=$product->id;
                   
                }
            }*/
            // Get Product Of User
            // Lead  sourc
            $products = Product::where('partner_id', $this->partner_id)->count();
            
            $opportunity_leads = array();
            $stages = array();
            for($i=11;$i>=0;$i--)
            {
                $opportunity_leads[] =
                    [
                        'month' =>Carbon::now()->subMonth($i)->format('M'),
                        'year' =>Carbon::now()->subMonth($i)->format('Y'),
                        'opportunity'=>$this->leadRepository->getAll()->where('partner_id', $this->partner_id)->where('created_at','LIKE',
                                Carbon::now()->subMonth($i)->format('Y-m').'%')->count(),
                        'leads'=>$this->leadRepository->getAll()->where('partner_id', $this->partner_id)->where('created_at','LIKE',
                              Carbon::now()->subMonth($i)->format('Y-m').'%')->whereIn('status',array(6,7))->count()
                    ];
            }
            //Call
            /*
            $callTotal= LogsCall::leftJoin('leads','leads.id','=','logs_call.lead_id')->where('logs_call.created_at','>=', $starting_date)->where('logs_call.created_at','<=', $ending_date)->where('leads.partner_id','=',$this->partner_id)->count();
            $callTotalMissing=LogsCall::leftJoin('leads','leads.id','=','logs_call.lead_id')->where('logs_call.created_at','>=', $starting_date)->where('logs_call.created_at','<=', $ending_date)->where('leads.partner_id','=',$this->partner_id)->whereRaw('logs_call.end_time-logs_call.start_time<=5')->count();
            $callTotalSuccess=LogsCall::leftJoin('leads','leads.id','=','logs_call.lead_id')->where('logs_call.created_at','>=', $starting_date)->where('logs_call.created_at','<=', $ending_date)->where('leads.partner_id','=',$this->partner_id)->whereRaw('logs_call.end_time-logs_call.start_time>5')->count();
            */
            //End call
            $totalLead = $this->leadRepository->getAll()->where('locked',0)->where('partner_id', $this->partner_id)->where('created_at','>=', $starting_date)->where('created_at','<=', $ending_date)
            ->where(function ($query)  use ($user_id){
                if($user_id!="" && $user_id!="0"){
					$query->where('sales_person_id','=',$user_id);
					$query->whereOr('user_id','=',$user_id);
				}
            })
            ->where(function ($query)  use ($utm_source){
                if($utm_source!=""){
					$query->where('UTM_Source','like','%'.$utm_source.'%');
					$query->whereOr('function','like','%'.$utm_source.'%');
				}
            })
            ->count(); //New lead

            
            $opportunity_new = $this->leadRepository->getAll()->where('locked',0)->where('partner_id', $this->partner_id)->where('created_at','>=', $starting_date)->where('created_at','<=', $ending_date)->where('status', 0)
            ->where(function ($query)  use ($user_id){
                if($user_id!="" && $user_id!="0"){
					$query->where('sales_person_id','=',$user_id);
					$query->whereOr('user_id','=',$user_id);
				}
            })
            ->where(function ($query)  use ($utm_source){
                if($utm_source!=""){
					$query->where('UTM_Source','like','%'.$utm_source.'%');
					$query->whereOr('function','like','%'.$utm_source.'%');
				}
            })
            ->count(); //New lead
            

            $opportunity_negotiation = Lead::join("call_action_status", "call_action_status.id","=","leads.status")->where('leads.partner_id', $this->partner_id)->where('leads.locked',0)->where('leads.created_at','>=', $starting_date)->where('leads.created_at','<=', $ending_date)->where('call_action_status.type', 1)
            ->where(function ($query)  use ($user_id){
                if($user_id!="" && $user_id!="0"){
					$query->where('leads.sales_person_id','=',$user_id);
					$query->whereOr('leads.user_id','=',$user_id);
				}
            })
            ->where(function ($query)  use ($utm_source){
                if($utm_source!=""){
					$query->where('leads.UTM_Source','like','%'.$utm_source.'%');
					$query->whereOr('leads.function','like','%'.$utm_source.'%');
				}
            })
            ->count();

            //$opportunity_won = $this->leadRepository->getAll()->where('partner_id', $this->partner_id)->where('created_at','>=', $starting_date)->where('created_at','<=', $ending_date)->whereIn('status', array(6,7))->count();
            $opportunity_won = Lead::join("call_action_status", "call_action_status.id","=","leads.status")->where('leads.partner_id', $this->partner_id)->where('leads.locked',0)->where('leads.created_at','>=', $starting_date)->where('leads.created_at','<=', $ending_date)->whereIn('call_action_status.type', array(2,4))
            ->where(function ($query)  use ($user_id){
                if($user_id!="" && $user_id!="0"){
					$query->where('leads.sales_person_id','=',$user_id);
					$query->whereOr('leads.user_id','=',$user_id);
				}
            })
            ->where(function ($query)  use ($utm_source){
                if($utm_source!=""){
					$query->where('leads.UTM_Source','like','%'.$utm_source.'%');
					$query->whereOr('leads.function','like','%'.$utm_source.'%');
				}
            })
            ->count();

            $opportunity_loss = Lead::join("call_action_status", "call_action_status.id","=","leads.status")->where('leads.partner_id', $this->partner_id)->where('leads.locked',0)->where('leads.created_at','>=', $starting_date)->where('leads.created_at','<=', $ending_date)->where('call_action_status.type', 3)
            ->where(function ($query)  use ($user_id){
                if($user_id!="" && $user_id!="0"){
					$query->where('leads.sales_person_id','=',$user_id);
					$query->whereOr('leads.user_id','=',$user_id);
				}
            })
            ->where(function ($query)  use ($utm_source){
                if($utm_source!=""){
					$query->where('leads.UTM_Source','like','%'.$utm_source.'%');
					$query->whereOr('leads.function','like','%'.$utm_source.'%');
				}
            })
            ->count();

            $opportunity_frendly = Lead::join("call_action_status", "call_action_status.id","=","leads.status")->where('leads.partner_id', $this->partner_id)->where('leads.locked',0)->where('leads.created_at','>=', $starting_date)->where('leads.created_at','<=', $ending_date)->where('call_action_status.type', 4)
            ->where(function ($query)  use ($user_id){
                if($user_id!="" && $user_id!="0"){
					$query->where('leads.sales_person_id','=',$user_id);
					$query->whereOr('leads.user_id','=',$user_id);
				}
            })
            ->where(function ($query)  use ($utm_source){
                if($utm_source!=""){
					$query->where('leads.UTM_Source','like','%'.$utm_source.'%');
					$query->whereOr('leads.function','like','%'.$utm_source.'%');
				}
            })
            ->count();

            $opportunity_notUpdateYet = Lead::where('leads.partner_id', $this->partner_id)->where('leads.locked',0)->where('leads.created_at','>=', $starting_date)->where('leads.created_at','<=', $ending_date)->whereIn('leads.status', [0,1])
            ->where(function ($query)  use ($user_id){
                if($user_id!="" && $user_id!="0"){
					$query->where('leads.sales_person_id','=',$user_id);
					$query->whereOr('leads.user_id','=',$user_id);
				}
            })
            ->where(function ($query)  use ($utm_source){
                if($utm_source!=""){
					$query->where('leads.UTM_Source','like','%'.$utm_source.'%');
					$query->whereOr('leads.function','like','%'.$utm_source.'%');
				}
            })
            ->count();

            $workNotFinsh = Task::where('tasks.partner_id', $this->partner_id)->where('tasks.task_deadline','>=', $starting_date)->where('tasks.task_deadline','<=', $ending_date)->whereIn('tasks.work_status', array(0,2))
            ->where(function ($query)  use ($user_id){
                if($user_id!="" && $user_id!="0"){
					$query->where('tasks.user_id','=',$user_id);
					$query->whereOr('tasks.task_from_user','=',$user_id);
				}
            })
            ->count();
            // Tỷ lệ chuyển đổi
            $ctrcall=0;
            $ctrleadregister=0;
            /*
            if($callTotalSuccess>0){
                $ctrcall=round(($opportunity_won/$callTotalSuccess)*100,2);
            } */
            //End ty lệ
            // Tỷ lệ chuyển đổi
            if($totalLead>0){
                $ctrleadregister=round(($opportunity_won/$totalLead)*100,2);
            }
            //End ty lệ
            //$staff=$this->userRepository->getAllUserArray()->count();
            $staff = PartnerUser::where('partner_id', $this->partner_id)->count();
            
            //Check tồng lead assign ->join('call_action_status','call_action_status.id','=')
            $leadAssign =array(); 
            $from = date("Y-m-d", strtotime("-7 day"));
            $to = date('Y-m-d');
            //Source

		$sourceList = $this->optionRepository->getAll()->where('partner_id','=',$this->partner_id)->where( 'category', 'function_type' )->get()
        ->map( function ( $title ) {
            return [
                'title' => $title->title,
                'value' => $title->value,
            ];
        } )->pluck( 'title', 'value')
        ->prepend(trans('lead.all'), '');

        $leadGroupQuery =Lead::selectRaw('count(id) AS totalUTM, UTM_Source')->where('leads.locked',0)->where('partner_id', $this->partner_id)
        ->where(function ($query) use ($user_id){
            if($user_id!="" && $user_id!="0"){
                $query->where('leads.sales_person_id','=',$user_id);
                $query->whereOr('leads.user_id','=',$user_id);
			}
        })
        ->groupBy('UTM_Source');
        $leadGroupSource =$leadGroupQuery->get();
        $leadGroupListSearch =$leadGroupQuery->get()
        ->map( function ( $leadGroupSource){
            return [
                'title' => $leadGroupSource->UTM_Source,
                'value' => $leadGroupSource->UTM_Source,
            ];
            }
        )->pluck( 'title', 'value')
        ->prepend(trans('lead.all'), '');

            //Lead sales
            $listUserSales=$this->userRepository->getAllStaffOfUser($userData->id);
            $staffs="";
            if($listUserSales){
                $staffs=User::join('partner_user','partner_user.user_id','=','users.id')
                ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
                //->where('partner_user.partner_id','=',$user->partner_id)
                ->whereIn('users.id',$listUserSales)
                ->get()
                ->map( function ( $salesList ) {
                    return [ 
                        'title' => $salesList->first_name." ".$salesList->last_name,
                        'value' => $salesList->id,
                    ];
                } )->pluck( 'title', 'value')
                ->prepend(trans('dashboard.select_staff'), '');
            }else{
                $staffs=array(''=>trans('dashboard.select_staff'));
            }
            //end 
            return view('user.dashboard.indexstyle1', compact('customers', 'contracts', 'opportunities', 'staff','opportunity_leads','stages','opportunity_new','opportunity_won','opportunity_loss', 'totalLead', 'ctrleadregister','sourceList', 'leadAssign', 'date_select', 'leadGroupListSearch', 'workNotFinsh', 'opportunity_notUpdateYet', 'opportunity_frendly','opportunity_negotiation', 'leadGroupSource', 'staffs', 'user_id'));  
        }
    }
    public function logs(){
        $user_id = $this->userRepository->getUser()->id;
        $userData=$this->userRepository->getUser();

        $listUserSales=$this->userRepository->getAllStaffOfUser($userData->id);
        $logData=Logs::whereIn('user_id',$listUserSales)->orderBy("id", "desc")->paginate(30)->appends(request()->query());
        $logshow=$logData->map( function ( $logs){
                return [
                    'id' => $logs->id,
                    "description" => $logs->logs,
                ];
            }
        );
        return $logshow;
    }

    public function logsaccess(Request $request){
        $title="Truy cập quản trị";
        $date  = addslashes($request->starting_date);
        $sales_id = addslashes($request->sales_id) ;
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
        $user_id = $this->userRepository->getUser()->id;
        $userData=$this->userRepository->getUser();
        $listUserSales=$this->userRepository->getAllStaffOfUser($userData->id);


        $staffs="";
        if($listUserSales){
            $staffs=User::join('partner_user','partner_user.user_id','=','users.id')
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
            //->where('partner_user.partner_id','=',$user->partner_id)
            ->whereIn('users.id',$listUserSales)
            ->get()
            ->map( function ( $salesList ) {
                return [ 
                    'title' => $salesList->first_name." ".$salesList->last_name,
                    'value' => $salesList->id,
                ];
            } )->pluck( 'title', 'value')
            ->prepend(trans('dashboard.select_staff'), '');
        }else{
            $staffs=array(''=>trans('dashboard.select_staff'));
        }
        $salesList=$staffs;

        $logData=UserLogin::select('user_login.*', 'users.first_name', 'users.last_name')->join('users','users.id','=','user_login.user_id')
        ->whereIn('user_login.user_id',$listUserSales)
        ->where(function ($query)  use ($starting_date, $ending_date, $sales_id){
                if($starting_date!=""){
                    $query->where('user_login.created_at','>=',$starting_date);
                }
                if($ending_date!=""){
                    $query->where('user_login.created_at','<=',$ending_date);
                }
                if($sales_id!=""){
                    $query->where('user_login.user_id','=',$sales_id);
                }
        })
        ->distinct()->orderBy("user_login.id", "desc");
        $logaccessPage=$logData->paginate(50)->appends(request()->query());
        $logshow=$logaccessPage->map( function ( $logs){
                return [
                    'id' => $logs->id,
                    'user_id' => $logs->user_id,
                    'fullname' => $logs->first_name.' '.$logs->last_name,
                    'ip' => $logs->ip_address,
                    'browser' => $logs->browser,
                    'user_agent' => $logs->user_agent,
                    "date" => $logs->created_at,
                ];
            }
        );
        
        return view('user.dashboard.logsaccess', compact('logshow', 'logaccessPage', 'title', 'salesList')); 
    }


}