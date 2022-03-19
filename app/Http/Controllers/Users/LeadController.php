<?php

namespace App\Http\Controllers\Users;

//use App\Helpers\ExcelfileValidator;
use App\Http\Controllers\UserController;
use App\Http\Requests\LeadImportRequest;
use App\Http\Requests\LeadRequest;
use App\Repositories\CityRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\CountryRepository;
use App\Repositories\LeadRepository;
use App\Repositories\OptionRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\StateRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Repositories\ExcelRepository;
use App\Repositories\SalesOrderRepository;
use App\Models\CallActionStatus;
use App\Models\Lead;
use App\Models\Logs;
use App\Models\Tag;
use App\Models\LeadsTags;
use Cache;
use App\Models\User;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\City;
use App\Models\District;
use App\Models\Ward;
use App\Models\State;
use App\Models\LogsCall;
use App\Models\CallLogs;
use App\Models\GroupLead;
use App\Models\GroupUser;
use App\Models\Saleorder;
use App\Models\SaleorderProduct;
use App\Models\LeadProduct;
use App\Models\EmailCheck;
use App\Models\EmailCheckStatus;
use App\Models\LeadsTemp;
use App\Models\CustomFields;
use App\Models\CustomFieldsData;
use App\Models\Brand;
use App\Models\Task;
use App\Models\Cookie;
use App\Models\Chatbox;
use App\Models\Leadmap;
use App\Models\Getdata;
use App\Models\Smsdesc;
use App\Models\SmsDescReply;
use App\Models\PartnerDevice;
use App\Models\LeadAssignStatus;
use App\Models\Notification;
use App\Models\LeadTags;
use App\Models\Option;
use App\Models\LeadTemp;
use App\Models\Comment;
use App\Models\ReportTags;
use App\Models\LeadStatus;
use App\Models\SalesteamMember;
use App\Models\Salesteam;



use Efriandika\LaravelSettings\Facades\Settings;

use Excel;
use Sentinel;

use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;
class LeadController extends UserController {
	/**
	 * @var CompanyRepository
	 */
	private $companyRepository;
	/**
	 * @var UserRepository
	 */
	private $userRepository;
	/**
	 * @var LeadRepository
	 */
	private $leadRepository;
	/**
	 * @var SalesTeamRepository
	 */
	private $salesTeamRepository;
	/**
	 * @var OptionRepository
	 */
	private $optionRepository;

	/**
	 * @var ExcelRepository
	 */
	private $excelRepository;

    private $countryRepository;

    private $stateRepository;

	private $productRepository;
	private $salesOrderRepository;

	/**
	 * SalesTeamController constructor.
	 *
	 * @param CompanyRepository $companyRepository
	 * @param UserRepository $userRepository
	 * @param LeadRepository $leadRepository
	 * @param SalesTeamRepository $salesTeamRepository
	 * @param OptionRepository $optionRepository
	 */
	public function __construct(
		CompanyRepository $companyRepository,
		UserRepository $userRepository,
		LeadRepository $leadRepository,
		SalesTeamRepository $salesTeamRepository,
		OptionRepository $optionRepository,
		ExcelRepository $excelRepository,
        CountryRepository $countryRepository,
        StateRepository $stateRepository,
		CityRepository $cityRepository,
		ProductRepository $productRepository,
		SalesOrderRepository $salesOrderRepository
	) {
		$this->middleware( 'authorized:leads.read', [ 'only' => [ 'index', 'data' ] ] );
		$this->middleware( 'authorized:leads.write', [ 'only' => [ 'create', 'store', 'update', 'edit' ] ] );
		$this->middleware( 'authorized:leads.delete', [ 'only' => [ 'delete' ] ] );

		parent::__construct();

		$this->companyRepository   = $companyRepository;
		$this->userRepository      = $userRepository;
		$this->companyRepository   = $companyRepository;
		$this->leadRepository      = $leadRepository;
		$this->salesTeamRepository = $salesTeamRepository;
		$this->optionRepository    = $optionRepository;
		$this->excelRepository     = $excelRepository;
        $this->countryRepository = $countryRepository;
        $this->stateRepository = $stateRepository;
		$this->cityRepository = $cityRepository;
		$this->productRepository = $productRepository;
		$this->salesOrderRepository = $salesOrderRepository;
		view()->share( 'type', 'lead' );
	}

	public function index(Request $request) {
		$title = trans( 'lead.leads' );
		$dateFormat = "d/m/Y H:i:s";
		$userData=$this->userRepository->getUser();
		$processstatus=0;
		$date  = addslashes($request->starting_date);
		$sales_id = addslashes($request->sales_id) ;
		$product_id = addslashes($request->product_id);
		$status  =  $request->input('status');
		$function  = addslashes($request->function);
		$UTM_Source = addslashes($request->UTM_Source);
		$tags = addslashes($request->tags);
		$fileamthanh = addslashes($request->fileamthanh);
		$keyword = addslashes($request->keyword);
		$group_id = addslashes($request->group_id); 
		
		$type_status = addslashes($request->type_status);
		$page = addslashes($request->page);
		$locked = addslashes($request->locked);
		$brand_id = addslashes($request->brand_id);

		$processstatus = addslashes($request->process);

		$daterange  = addslashes($request->daterange);
		$approve = addslashes($request->approve);

		$tuongtac = addslashes($request->tuongtac);

		$leadDetail="";
		if(isset($approve) && $approve>0){
			$leadDetail=Lead::select('leads.id', 'leads.created_at', 'leads.updated_at', 'leads.created_at', 'leads.opportunity', 'leads.company_name', 'leads.client_name', 'leads.sales_person_id', 'leads.partner_id', 'leads.email', 'leads.phone', 'leads.function', 
			'leads.UTM_Source', 'leads.UTM_Campaign', 'leads.UTM_Medium', 'leads.psid', 'leads.URL', 'leads.status', 'leads.title', 'leads.next_follow_up', 'lead_assign_status.assign_from_name', 'call_action_status.title as statusclient', 'leads.product_name', 'leads.psid', 'leads.page_id') 
			->leftJoin('call_action_status','call_action_status.id','=','leads.status')
			->join('lead_assign_status','lead_assign_status.lead_id','=','leads.id')
			->where('lead_assign_status.id',$approve)
			->where('lead_assign_status.status',0)
			->where('lead_assign_status.user_id',$userData["id"])->first();
		}
		$start=0;
		if(isset($page) && $page>1){
			$start=($page-1);
		}
		$limit=20; 
		$statar_item=$start*$limit;
		if(isset($daterange) && $daterange!="" && $daterange!=0){


			switch ($daterange) {
				case 1:
					$starting_date=date("Y-m-d 00:00:00", strtotime('-7 days'));
					$ending_date=date("Y-m-d 23:59:59", strtotime('-2 days'));
					break;
				case 2:
					$starting_date=date("Y-m-d 00:00:00", strtotime('-30 days'));
					$ending_date=date("Y-m-d 23:59:59", strtotime('-8 days'));
					break;
				case 3:
					$starting_date=date("Y-m-d 00:00:00", strtotime('-120 days'));
					$ending_date=date("Y-m-d 23:59:59", strtotime('-31 days'));
					break;
				case 4:
					$starting_date=date("Y-m-d 00:00:00", strtotime('-720 days'));
					$ending_date=date("Y-m-d 23:59:59", strtotime('-121 days'));
					break;
				default:
					$starting_date=date("Y-m-d 00:00:00", strtotime('-90 days'));
					$ending_date=date("Y-m-d 23:59:59", strtotime('-2 days'));
			}
			$date_select=date("m/d/Y",strtotime($starting_date))." - ".date("m/d/Y",strtotime($ending_date));

			/*
			if($daterange==1){
				$starting_date=date("Y-m-d 00:00:00");
				$ending_date=date("Y-m-d 23:59:59");
			}else{
				$starting_date=date("Y-m-d",strtotime("-".$daterange." days"));
				$ending_date=date("Y-m-d");
				$date_select=date("m/d/Y",strtotime('today - '.$daterange.' days'))." - ".date("m/d/Y");
			} */
		}else{
			if($date!=""){
				$dateArray=explode("-",trim($date));
				$starting_date=$this->convertDate(trim($dateArray[0]));
				$ending_date=$this->convertDate(trim($dateArray[1]));
				$starting_date=date("Y-m-d 00:01:00", strtotime($starting_date));
				$ending_date=date("Y-m-d 23:59:00", strtotime($ending_date));
				$date_select=$date;
			}else{
				$starting_date=date("Y-m-d",strtotime('today-30 days'));
				$ending_date=date("Y-m-d");
				$date_select=date("m/d/Y",strtotime('today-30 days'))." - ".date("m/d/Y");
			}
		}


		$this->partner_id=$userData->partner_id;
		if(isset($_GET["demo1"])){
		//
			$grouppermission=GroupUser::getGroup();
			var_dump($grouppermission);
			$listUser=$this->userRepository->getUserListSearch($grouppermission, $userData);
			var_dump($listUser);
			die();
		}
		/*
		$grouppermissionCache = cache('grouppermission'.$this->partner_id);
		if(isset($grouppermissionCache) && $grouppermissionCache!=""){
			$grouppermission=$grouppermissionCache;
		}else{
			$grouppermission=GroupUser::getGroup();
			Cache::put('grouppermission'.$this->partner_id, $grouppermission, now()->addMinutes(20));
		} */ 
		$grouppermission=GroupUser::getGroup();
		//$listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
		if(!isset($locked) || $locked==""){
			$locked=0;
		} 

		$partner_id=$this->partner_id;
		$listStatusSearch=$status;

		if(!isset($sales_id) || $sales_id=="" || $sales_id==0){
			if($userData->user_id==1){
				$sales_id=0;
			}else{
				$sales_id=$userData->id;
			}
		}
		$listUser=$this->memberUsers($sales_id);

		/*
		if(!isset($sales_id) || $sales_id=="" || $sales_id==0){
			if($userData->user_id==1){
				$sales_id=0;
			}else{
				$sales_id=$userData->id;
			}
		} */
		if(!isset($group_id) || $group_id==""){
			$groupFirstCache = cache('groupFirst'.$this->partner_id);
			if(isset($groupFirstCache) && $groupFirstCache!=""){
				$group_id=$groupFirstCache;
			}else{
				$groupFirst=GroupLead::where([['partner_id',$this->partner_id],["status",1], ["default",1]])->orderBy('position','asc')->first();
				if($groupFirst){
					$group_id=$groupFirst["id"];
				}
				Cache::put('groupFirst'.$this->partner_id, $group_id, now()->addMinutes(20));
			}
		}
		$this->generateParams(array("group_id"=>$group_id, "status"=>$status));		
		/*
		$listStatusSearch=[];
		if($status!="" && count($status)>0){
			for($j=0; $j<count($status);$j++){
				$listStatusSearch[]=addslashes((int)$status[$j]);
			} 
		}  */
		//$listUser=$this->userRepository->getAllUserOnPartner($this->partner_id);
		//die();
			$listStatusSearchList=0;
			if($listStatusSearch!="" && $listStatusSearch!="0" && $listStatusSearch!="1"){
				$listStatusSearchList=$listStatusSearch;
			}
			$type_statusList=0;
			if($type_status!="" && count($type_status)>0){
				$type_statusList=implode(",",$type_status);
			}
			
			$paramaterCacheLead=md5($starting_date.$ending_date.$sales_id.$listStatusSearchList.$function.$UTM_Source.$keyword. $product_id.$tags.$fileamthanh.$group_id.$type_statusList.$brand_id.$partner_id.$page.$tuongtac.$status);
			$cachename="leadsQuery13";
			$cachtotalname="leadsTotal13";
			$cachLeadsLastPage="cachLeadsLastPage13";
			$leadsQueryCache = cache($cachename.$paramaterCacheLead.$processstatus);
			$leadsTotalCache = cache($cachtotalname.$paramaterCacheLead.$processstatus);
			$leadsLastPage = cache($cachLeadsLastPage.$paramaterCacheLead.$processstatus);
			
			if(isset($leadsQueryCache) && $leadsQueryCache!="" && $leadsLastPage!=""){
				$leadsPage=$leadsQueryCache;
				$totalLead=$leadsTotalCache;
				$lastPage=$leadsLastPage;
				
			}else{ 
				if(isset($processstatus) && $processstatus==1){
					$leadsQuery = Lead::select('leads.id', 'leads.created_at', 'leads.updated_at', 'leads.created_at', 'leads.opportunity', 'leads.company_name', 'leads.client_name', 'leads.sales_person_id', 'leads.product_id', 'leads.partner_id', 'leads.email', 'leads.phone', 'leads.function', 'leads.UTM_Source', 'leads.UTM_Campaign', 'leads.UTM_Medium', 'leads.psid', 'leads.URL', 'leads.status', 'leads.title', 'leads.next_follow_up', 'leads.group_id', 'call_action_status.title', 'call_action_status.type', 'users.first_name', 'call_action_status.icons', 'group_client.name as group_name')
					->join('chat_box','chat_box.sender_id','=','leads.psid')
					->leftJoin('call_action_status','call_action_status.id','=','leads.status')
					->join('users','users.id','=','leads.sales_person_id')
					->leftJoin('group_client','group_client.id','=','leads.group_id')
					->leftJoin('lead_tags','lead_tags.lead_id','=','leads.id') 
					//->leftJoin('logs','logs.lead_id','=','leads.id') 
					->where('leads.partner_id',$partner_id)
					->where('leads.locked',$locked)
					->where(function ($query)  use ($starting_date, $ending_date, $sales_id, $listStatusSearch, $function,$UTM_Source, $keyword, $product_id, $tags, $fileamthanh, $group_id, $type_status, $brand_id, $tuongtac){
						/*
						if($tuongtac!="" && $tuongtac!="0"){
							if($tuongtac!="" && $tuongtac=="3"){
								$query->where('leads.sales_person_id',0);
							}elseif($tuongtac!="" && $tuongtac=="1"){
								$query->where('leads.sales_person_id','>',0);
							} 
						} */
						if($tuongtac!="" && $tuongtac!="0"){
							$date60daysago=date("Y-m-d H:i:s", strtotime("-60 days"));
							$today=date("Y-m-d");
							if($tuongtac!="" && $tuongtac=="3"){
								$query->where('leads.date_last_update','<=', $date60daysago);
								$query->orWhereIsNull('leads.date_last_update');
								//$query->where('leads.date_last_update','<=', $ending_date);
							}elseif($tuongtac!="" && $tuongtac=="1"){
								$query->where('leads.date_last_update','>=', $starting_date);
								$query->where('leads.date_last_update','<=', $ending_date);
							}elseif($tuongtac!="" && $tuongtac=="2"){
								$query->where('leads.date_last_update','>', $starting_date);
							}
						}

						if($listStatusSearch!="" && $listStatusSearch!="0" && $listStatusSearch!="1"){
							$query->whereIn('leads.status',explode(",",$listStatusSearch));
						}
						if($type_status!=""){
							$query->whereIn('call_action_status.type',explode(",",$type_status));
						}
						if($product_id!="" && $product_id!="0"){
							$query->where('leads.product_id','=',$product_id);
						}
						if($UTM_Source!="" && $UTM_Source!="0"){
							$query->where('leads.UTM_Source','=',$UTM_Source);
						}
						if($tags!="" && $tags!="0"){
							//$query->where('lead_tags.tag_id','=',$tags);
							$query->where('lead_tags.tag_id','=',$tags);
		
						}
						if($group_id!="" && $group_id!="0"){
							$query->where('leads.group_id','=',$group_id);
						}
						if($function!="" && $function!="0"){
							$query->where('leads.function','=',$function);
						}
						if($brand_id!="" && $brand_id!="0"){
							$query->where('leads.brand_id','=',$brand_id);
						}
						if($keyword!=""){
							$query->where(function ($query1)  use ($keyword){
								$query1->where('leads.opportunity', 'LIKE', "%{$keyword}%");
								$query1->orWhere('leads.email','LIKE', "%{$keyword}%");
								$query1->orWhere('leads.phone', 'LIKE', "%{$keyword}%");
								$query1->orWhere('leads.messenger', 'LIKE', "%{$keyword}%");

							});
						} 
					})->where(function ($query)  use ($listUser){
							if($listUser!="" && $listUser!="0"){
								$query->where(function ($query1)  use ($listUser){
									$query1->whereIn('leads.sales_person_id',$listUser);
									$query1->orWhereIn('leads.customer_care_id',$listUser);
									$query1->orWhereIn('leads.user_id',$listUser);
								});
							}
					})->where(function ($query)  use ($starting_date, $ending_date, $status){
						
						if($starting_date!=""){
							$query->where(function ($query1) use($starting_date){
									$query1->where('leads.created_at','>=',$starting_date);
								//	$query1->orWhere('leads.updated_at','>=',$starting_date);
							});
							
						}
						if($ending_date!=""){
							$query->where(function ($query1) use($ending_date){
								$query1->where('leads.created_at','<=',$ending_date);
								//$query1->orWhere('leads.updated_at','<=',$ending_date);
							});
						}
					})
					->groupBy('leads.psid')
					->havingRaw("count(chat_box.receive_id)<2")
					->distinct()
					->orderBy('leads.updated_at', 'DESC');
					$totalLead=count($leadsQuery->get()->toArray());
					$leadsPage=$leadsQuery->paginate(20)->appends(request()->query());
					Cache::put($cachename.$paramaterCacheLead.$processstatus, $leadsPage, now()->addMinutes(5));
					Cache::put($cachtotalname.$paramaterCacheLead.$processstatus, $totalLead, now()->addMinutes(5));
				}else{
					$leadsQuery = Lead::select('leads.id', 'leads.created_at', 'leads.updated_at', 'leads.created_at', 'leads.opportunity', 'leads.company_name', 'leads.client_name', 'leads.sales_person_id', 'leads.product_id', 'leads.partner_id', 'leads.email', 'leads.phone', 'leads.function', 'leads.UTM_Source', 'leads.UTM_Campaign', 'leads.UTM_Medium', 'leads.psid', 'leads.URL', 'leads.status', 'leads.title', 'leads.next_follow_up', 'leads.group_id', 'call_action_status.title as call_title', 'call_action_status.type', 'users.first_name', 'call_action_status.icons', 'group_client.name as group_name')
					->leftJoin('call_action_status','call_action_status.id','=','leads.status')
					->leftJoin('users','users.id','=','leads.sales_person_id')
					->leftJoin('group_client','group_client.id','=','leads.group_id');
					
					if($tags!="" && $tags!="0"){
						$leadsQuery->join('lead_tags','lead_tags.lead_id','=','leads.id');
					}
					$leadsQuery->where('leads.locked',$locked)->where('leads.partner_id',$partner_id)->where(function ($query) use ($starting_date, $ending_date, $sales_id, $listStatusSearch, $function,$UTM_Source, $keyword, $product_id, $tags, $group_id, $type_status, $brand_id, $tuongtac){
						if($tuongtac!="" && $tuongtac!="0"){
							$date60daysago=date("Y-m-d H:i:s", strtotime("-60 days"));
							$today=date("Y-m-d");
							if($tuongtac!="" && $tuongtac=="3"){
								$query->where('leads.date_last_update','<=', $date60daysago);
								//$query->where('leads.date_last_update','<=', $ending_date);
							}elseif($tuongtac!="" && $tuongtac=="1"){
								$query->where('leads.date_last_update','>=', $starting_date);
								$query->where('leads.date_last_update','<=', $ending_date);
							}elseif($tuongtac!="" && $tuongtac=="2"){
								$query->where('leads.date_last_update','>', $starting_date);
							}
						}
						if($listStatusSearch!="" && $listStatusSearch!="0" && $listStatusSearch!="1"){
							$query->whereIn('leads.status',explode(",",$listStatusSearch));
						}
						if($type_status!=""){
							$query->whereIn('call_action_status.type',explode(",",$type_status));
						}
						if($product_id!="" && $product_id!="0"){
							$query->where('leads.product_id','=',$product_id);
						}
						if($UTM_Source!="" && $UTM_Source!="0"){
							$query->where('leads.UTM_Source','=',$UTM_Source);
						}
						if($tags!="" && $tags!="0"){
							//$query->where('lead_tags.tag_id','=',$tags);
							$query->where('lead_tags.tag_id','=',$tags);
		
						}
						if($group_id!="" && $group_id!="0"){
							$query->where('leads.group_id','=',$group_id);
						}
						if($function!="" && $function!="0"){
							$query->where('leads.function','=',$function);
						}
						if($brand_id!="" && $brand_id!="0"){
							$query->where('leads.brand_id','=',$brand_id);
						}
						if($keyword!=""){
							$query->where(function ($query1)  use ($keyword){
								$query1->where('leads.opportunity', 'LIKE', "%{$keyword}%");
								$query1->orWhere('leads.email','LIKE', "%{$keyword}%");
								$query1->orWhere('leads.phone', 'LIKE', "%{$keyword}%");
								$query1->orWhere('leads.messenger', 'LIKE', "%{$keyword}%");

							});
						} 
					})->where(function ($query)  use ($sales_id){
							if($sales_id!="" && $sales_id!="0"){
								$query->where(function ($query1)  use ($sales_id){
									$query1->where('leads.sales_person_id','=',$sales_id);
									$query1->orWhere('leads.customer_care_id','=',$sales_id);
									$query1->orWhere('leads.user_id','=',$sales_id);
								});
							}
					})->where(function ($query)  use ($starting_date, $ending_date, $status){
						
						if($starting_date!=""){
							$query->where(function ($query1) use($starting_date){
									$query1->where('leads.created_at','>=',$starting_date);
									$query1->orWhere('leads.updated_at','>=',$starting_date);
							});
							
						}
						if($ending_date!=""){
							$query->where(function ($query1) use($ending_date){
								$query1->where('leads.created_at','<=',$ending_date);
								$query1->orWhere('leads.updated_at','<=',$ending_date);
							});
						}
					})
					->where(function ($query){
						$query->orWhere('leads.phone','!=',"");
						$query->orWhere('leads.email','!=',"");
						$query->orWhere(function ($query1){
							$query1->where('leads.psid','!=',"");
							$query1->where('leads.URL','!=',"");
						});
					})
					->distinct('leads.id')
					->orderBy('leads.updated_at', 'DESC');
					//$totalLead=$leadsQuery->get()->count('leads.id');
					$totalLead=$leadsQuery->count('leads.id');

					$lastPage=ceil($totalLead/$limit);
					$leadsPage=$leadsQuery->paginate($limit)->appends(request()->query());
					Cache::put($cachename.$paramaterCacheLead.$processstatus, $leadsPage, now()->addMinutes(5));
					Cache::put($cachtotalname.$paramaterCacheLead.$processstatus, $totalLead, now()->addMinutes(5));
					Cache::put($cachLeadsLastPage.$paramaterCacheLead.$processstatus, $lastPage, now()->addMinutes(5));
				} 
			}
			
			$leadsList=$leadsPage->map( function ( $lead) use ($dateFormat, $processstatus){
                return [
                    'id'           => $lead->id,
					'created_at'   => date($dateFormat,strtotime($lead->created_at)),
					'update_at'   => date($dateFormat,strtotime($lead->updated_at)),
					'opportunity' => $lead->opportunity,
                    'company_name' => $lead->company_name,
					'client_name'  => $lead->client_name,
					'sale_id'  => $lead->sales_person_id,
					'sale_name'  => $lead->first_name,
					'product_id'   => $lead->product_id,
					'partner_id'   => $lead->partner_id,
					'branches_name' => $lead->branches_name,
					'email'        => $lead->email,
					'tagsList'	   => Tag::select('tags.id', 'tags.title', 'tags.color_bg', 'tags.color_text')->join('lead_tags','lead_tags.tag_id','=', 'tags.id')->where('lead_tags.lead_id',$lead->id)->orderBy('tags.position','asc')->get(),
                    'phone'        => $lead->phone,
					'calls'        => "",//LogsCall::where('lead_id',$lead->id)->count(),
					'calls_record' => "",//LogsCall::where('lead_id',$lead->id)->where('file_record','!=',"")->count(),
					'callType'   => "",//LogsCall::select('status')->where('lead_id',$lead->id)->orderBy('id','desc')->first(),
					'function'	   =>$lead->function,
					'source'	   =>$lead->UTM_Source,
					'UTM_Campaign' =>$lead->UTM_Campaign,
					'UTM_Medium'   =>$lead->UTM_Medium,
					'psid'		   =>$lead->psid,
					'URL'		   =>$lead->URL,
					'status'	   =>$lead->status,
					'lead_type'	   =>$lead->type,
					'status_title'	=>$lead->call_title,
					'logs'        => Logs::select('logs')->where('lead_id',$lead->id)->orderBy('id','desc')->first(),
					'tasks'			=>Task::select('task_start', 'task_end', 'report_status')->where('lead_id',$lead->id)->orderBy('id','desc')->first(),
					'next_time_follow' => $lead->next_follow_up,
					'sales_person_id'=> $lead->sales_person_id, 
					'group_name'=> $lead->group_name, 
					'group_id'=> $lead->group_id, 
					'icons'=> $lead->icons, 
					'process_note'=>$processstatus,
				];
			}
		);
		//LeadsTags::select('tags.*')->join('tags','tags.id','=','lead_tags.tag_id')->where('lead_tags.lead_id',$lead->id)->orderBy('tags.position','asc')->get()
		$leadGroupSource =Tag::where('partner_id',$this->partner_id)->get()
		->map( function ( $leadGroupSource){
			return [
				'title' => $leadGroupSource->title,
				'value' => $leadGroupSource->id,
			];
			}
		)->pluck( 'title', 'value')
		->prepend(trans('lead.all'), '');

		//salesList
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
						})->pluck( 'title', 'value')
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
		//Product list
		$productList=Product::where('partner_id','=',$this->partner_id)->orderBy('product_name', 'desc')->get()
		->map( function ( $productList ) {
			return [
				'title' => $productList->product_name,
				'value' => $productList->id,
			];
		} )->pluck( 'title', 'value')
		->prepend(trans('lead.all'), '');
		//Source
		$sourceList = $this->optionRepository->getAll()->where('partner_id','=',$this->partner_id)->where( 'category', 'function_type' )->get()
            ->map( function ( $title ) {
                return [
                    'title' => $title->title,
                    'value' => $title->value,
                ];
            } )->pluck( 'title', 'value')
			->prepend(trans('lead.all'), '');
		//FileAmThanh
		$fileList = $this->optionRepository->getAll()->where( 'category', 'file_record_status' )->get()
            ->map( function ( $title ) {
                return [
                    'title' => $title->title,
                    'value' => $title->value,
                ];
            } )->pluck( 'title', 'value')
			->prepend(trans('lead.all'), '');
		$project_id=$this->partner_id; 
		
		return view( 'user.lead.index', compact( 'title', 'leadsList', 'leadsPage', 'salesList', 'productList', 'sourceList', 'leadGroupSource', 'totalLead', 'date_select', 'keyword', 'product_id', 'project_id', 'fileList', 'listStatusSearch', 'type_status', 'approve', 'leadDetail', 'group_id', 'daterange', 'tags', 'tuongtac', 'sales_id', 'limit', 'lastPage'));   
	}
 
	public function kanban(Request $request) {
		$userData=$this->userRepository->getUser();
		$title = trans( 'lead.leads' );
		$dateFormat = config('settings.date_format');
		$date  = addslashes($request->starting_date);
		$sales_id = addslashes($request->sales_id) ;
		$product_id = addslashes($request->product_id);
		$status  = addslashes($request->status) ;
		$function  = addslashes($request->function);
		$UTM_Source = addslashes($request->UTM_Source);
		$tags = addslashes($request->tags);
		$fileamthanh = addslashes($request->fileamthanh);
		$keyword = addslashes($request->keyword);
		$group_id = addslashes($request->group_id);
		$type_status = addslashes($request->type_status);
		$page = addslashes($request->page);
		$locked = addslashes($request->locked);
		$brand_id = addslashes($request->brand_id);
		$processstatus = addslashes($request->process);
		$daterange  = addslashes($request->daterange);
		$approve = addslashes($request->approve);
		$tuongtac = addslashes($request->tuongtac);
		if($date!=""){
			$dateArray=explode("-",trim($date));
			$starting_date=$this->convertDate(trim($dateArray[0]));
			$ending_date=$this->convertDate(trim($dateArray[1]));
			$starting_date=date("Y-m-d", strtotime($starting_date." -1 days"));
			$ending_date=date("Y-m-d", strtotime($ending_date." +1 days"));
			
			$date_select=$date;
		}else{
			$starting_date=date("Y-m-d",strtotime('today - 30 days'));
			$ending_date=date("Y-m-d",strtotime('today +1 days'));
			$date_select=date("m/d/Y",strtotime('today - 30 days'))." - ".date("m/d/Y");
		}
		$this->partner_id=$userData->partner_id;
		$userData=$this->userRepository->getUser();
		//$listUser=$this->userRepository->getUserListSearch($grouppermission, $userData);

		if(!isset($group_id) || $group_id==""){
			$groupFirstCache = cache('groupFirst'.$this->partner_id);
			if(isset($groupFirstCache) && $groupFirstCache!=""){
				$group_id=$groupFirstCache;
			}else{
				$groupFirst=GroupLead::where([['partner_id',$this->partner_id],["status",1], ["default",1]])->orderBy('position','asc')->first();
				if($groupFirst){
					$group_id=$groupFirst["id"];
				}
				Cache::put('groupFirst'.$this->partner_id, $group_id, now()->addMinutes(20));
			}
		}
		$this->generateParams(array("group_id"=>$group_id, "status"=>$status));

		$grouppermission=GroupUser::getGroup();
		$listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
		if(!isset($locked) || $locked==""){
			$locked=0;
		} 

		$partner_id=$this->partner_id;
		$listStatusSearch=$status;

		if(!isset($sales_id) || $sales_id=="" || $sales_id==0){
			if(count($listUser)>1){
				$sales_id=0;
			}else{
				if($userData->user_id==1){
					$sales_id=0;
				}else{
					$sales_id=$userData->id;
				}
			}
			
		}
		$leadsQuery = Lead::join('call_action_status','call_action_status.id','=','leads.status')
			->leftJoin('users','users.id','=','leads.sales_person_id')
			->join('group_client','group_client.id','=','leads.group_id')
			->where('leads.partner_id',$this->partner_id)
			->where('leads.locked',0)
			->where(function ($query) use($sales_id, $status, $function,$listUser,$UTM_Source, $keyword, $product_id, $tags, $fileamthanh, $type_status, $userData){
				if($status!=""){
					$query->where('leads.status',$status);
				}
				if($product_id!="" && $product_id!="0"){
					$query->where('leads.product_id','=',$product_id);
				}
				if($UTM_Source!="" && $UTM_Source!="0"){
					$query->where('leads.UTM_Source','=',$UTM_Source);
				}
				if($tags!="" && $tags!="0"){
					$query->where('leads.tags','=',$tags);
				}
				if($function!="" && $function!="0"){
					$query->where('leads.function','=',$function);
				}
				if($keyword!=""){
					$query->where(function ($query1)  use ($keyword){
						$query1->where('leads.opportunity', 'LIKE', "%{$keyword}%");
						$query1->orWhere('leads.email','LIKE', "%{$keyword}%");
						$query1->orWhere('leads.phone', 'LIKE', "%{$keyword}%");

					});
				}
			})->where(function ($query)  use ($listUser, $sales_id){
				if($sales_id!="" && $sales_id!="0"){
					$query->where(function ($query1)  use ($sales_id){
						$query1->where('leads.sales_person_id','=',$sales_id);
						$query1->whereOr('leads.user_id','=',$sales_id);
					});
				}else{
					if(isset($listUser) && $listUser!="" && count($listUser)>0){
						$query->whereIn('leads.sales_person_id',$listUser);
						$query->orWhere('leads.sales_person_id',0);
					}
				}
			})
			->where(function ($query)  use ($group_id){
				if($group_id!="" && $group_id!="0"){
					$query->where('leads.group_id',$group_id);
				}
			})->where(function ($query)  use ($starting_date){
				if($starting_date!=""){
					$query->where('leads.created_at','>=',$starting_date);
					$query->whereOr('leads.updated_at','>=',$starting_date);
				}
			})->where(function ($query) use ($ending_date){
				if($ending_date!=""){
					$query->where('leads.created_at','<=',$ending_date);
					$query->whereOr('leads.updated_at','<=',$ending_date);
				}
			})
			->distinct();
			$totalLead=$leadsQuery->count();
			//$leadsPage=$leadsQuery->paginate(1000)->appends(request()->query());
		// Lead  source
		$leadGroupSource =Tag::where('partner_id',$this->partner_id)->get()
		->map( function ( $leadGroupSource){
			return [
				'title' => $leadGroupSource->title,
				'value' => $leadGroupSource->title,
			];
			}
		)->pluck( 'title', 'value')
		->prepend(trans('lead.all'), '');
		

		//salesList
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
		//Status list


		$callStatusQueryCache = cache('callStatusQueryCache1'.$this->partner_id.$group_id);
		if(isset($callStatusQueryCache) && $callStatusQueryCache!=""){
			$statusListKanban=$callStatusQueryCache;
		}else{
			$statusListKanban = CallActionStatus::where("partner_id",$this->partner_id)
			->where(function ($query)  use ($group_id){
				if($group_id!="" && $group_id!="0"){
					$query->where('type',$group_id);
				}
			})
			->orderBy("position", "asc")
			->get();
			
			Cache::put('callStatusQueryCache1'.$this->partner_id.$group_id, $statusListKanban, now()->addMinutes(10));
		} 
	
		//Status list Kanban

		//Product list
		$productList=Product::where('partner_id','=',$this->partner_id)->orderBy('product_name', 'desc')->get()
		->map( function ( $productList ) {
			return [
				'title' => $productList->product_name,
				'value' => $productList->id,
			];
		} )->pluck( 'title', 'value')
		->prepend(trans('lead.all'), '');
		//Source
		$sourceList = $this->optionRepository->getAll()->where('partner_id','=',$this->partner_id)->where( 'category', 'function_type' )->get()
            ->map( function ( $title ) {
                return [
                    'title' => $title->title,
                    'value' => $title->value,
                ];
            } )->pluck( 'title', 'value')
			->prepend(trans('lead.all'), '');
		//FileAmThanh
		$fileList = $this->optionRepository->getAll()->where( 'category', 'file_record_status' )->get()
            ->map( function ( $title ) {
                return [
                    'title' => $title->title,
                    'value' => $title->value,
                ];
            } )->pluck( 'title', 'value')
			->prepend(trans('lead.all'), '');

		$project_id=$this->partner_id; 
		return view( 'user.lead.kanban', compact( 'title', 'salesList', 'productList', 'sourceList', 'leadGroupSource', 'totalLead', 'date_select', 'keyword', 'product_id', 'project_id', 'fileList', 'statusListKanban', 'group_id', 'daterange'));
	}

	public function kanbanData(Request $request){

		$userData=$this->userRepository->getUser();
		$title = trans( 'lead.leads' );
		$dateFormat = config('settings.date_format');
		$date  = addslashes($request->starting_date);
		$sales_id = addslashes($request->sales_id) ;
		$product_id = addslashes($request->product_id);
		$status  = addslashes($request->status) ;
		$function  = addslashes($request->function); 
		$UTM_Source = addslashes($request->UTM_Source);
		$tags = addslashes($request->tags);
		$fileamthanh = addslashes($request->fileamthanh);
		$keyword = addslashes($request->keyword);
		$group_id = addslashes($request->group_id);
		$type_status = addslashes($request->type_status);
		$page = addslashes($request->page);
		$locked = addslashes($request->locked);
		$brand_id = addslashes($request->brand_id);
		$processstatus = addslashes($request->process);
		$daterange  = addslashes($request->daterange);
		$approve = addslashes($request->approve);
		$tuongtac = addslashes($request->tuongtac);
		if($date!=""){
			$dateArray=explode("-",trim($date));
			$starting_date=$this->convertDate(trim($dateArray[0]));
			$ending_date=$this->convertDate(trim($dateArray[1]));
			$starting_date=date("Y-m-d", strtotime($starting_date." -1 days"));
			$ending_date=date("Y-m-d", strtotime($ending_date." +1 days"));
			
			$date_select=$date;
		}else{
			$starting_date=date("Y-m-d",strtotime('today - 30 days'));
			$ending_date=date("Y-m-d",strtotime('today +1 days'));
			$date_select=date("m/d/Y",strtotime('today - 30 days'))." - ".date("m/d/Y");
		}
		$this->partner_id=$userData->partner_id;
		$userData=$this->userRepository->getUser();
		//$listUser=$this->userRepository->getUserListSearch($grouppermission, $userData);
		$this->generateParams();

		$grouppermission=GroupUser::getGroup();
		$listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
		if(!isset($locked) || $locked==""){
			$locked=0;
		} 

		$partner_id=$this->partner_id;
		$listStatusSearch=$status;

		if(!isset($sales_id) || $sales_id=="" || $sales_id==0){
			if(count($listUser)>1){
				$sales_id=0;
			}else{
				if($userData->user_id==1){
					$sales_id=0;
				}else{
					$sales_id=$userData->id;
				}
			}
			
		}
		$paramaterCacheLead=md5($starting_date.$ending_date.$sales_id.$function.$UTM_Source.$keyword. $product_id.$tags.$fileamthanh.$group_id.$brand_id.$partner_id.$page.$tuongtac.$status);
		$cachename="cachLeadsKanBan";
		$leadsQueryCache = cache($cachename.$paramaterCacheLead.$processstatus);
			
		if(isset($leadsQueryCache) && $leadsQueryCache!=""){
			$leadsPage=$leadsQueryCache;
		}else{ 
			$leadsQuery = Lead::select('leads.id', 'leads.created_at', 'leads.updated_at', 'leads.created_at', 'leads.opportunity', 'leads.company_name', 'leads.client_name', 'leads.sales_person_id', 'leads.product_id', 'leads.partner_id', 'leads.email', 'leads.phone', 'leads.function', 'leads.UTM_Source', 'leads.UTM_Campaign', 'leads.UTM_Medium', 'leads.psid', 'leads.URL', 'leads.status', 'leads.title', 'leads.next_follow_up', 'leads.group_id', 'call_action_status.title as call_title', 'call_action_status.type', 'users.first_name', 'call_action_status.icons', 'group_client.name as group_name')
			->join('call_action_status','call_action_status.id','=','leads.status')
			->leftJoin('users','users.id','=','leads.sales_person_id')
			->join('group_client','group_client.id','=','leads.group_id')
			->where('leads.partner_id',$this->partner_id)
			->where('leads.locked',0)
			->where(function ($query) use($sales_id, $status, $function,$listUser,$UTM_Source, $keyword, $product_id, $tags, $fileamthanh, $type_status){
				if($sales_id!="" && $sales_id!="0"){
					$query->where('leads.sales_person_id','=',$sales_id);
					$query->whereOr('leads.user_id','=',$sales_id);
				}
				if($status!=""){
					$query->where('leads.status',$status);
				}
				if($product_id!="" && $product_id!="0"){
					$query->where('leads.product_id','=',$product_id);
				}
				if($UTM_Source!="" && $UTM_Source!="0"){
					$query->where('leads.UTM_Source','=',$UTM_Source);
				}
				if($tags!="" && $tags!="0"){
					$query->where('leads.tags','=',$tags);
				}
				if($function!="" && $function!="0"){
					$query->where('leads.function','=',$function);
				}
				if($keyword!=""){
					$query->where(function ($query1)  use ($keyword){
						$query1->where('leads.opportunity', 'LIKE', "%{$keyword}%");
						$query1->orWhere('leads.email','LIKE', "%{$keyword}%");
						$query1->orWhere('leads.phone', 'LIKE', "%{$keyword}%");

					});
				}
			})->where(function ($query)  use ($listUser, $sales_id){
				if($sales_id!="" && $sales_id!="0"){
					$query->where(function ($query1)  use ($sales_id){
						$query1->where('leads.sales_person_id','=',$sales_id);
						$query1->whereOr('leads.user_id','=',$sales_id);
					});
				}
			})->where(function ($query)  use ($group_id){
				if($group_id!="" && $group_id!="0"){
					$query->where('leads.group_id',$group_id);
				}
			})->where(function ($query)  use ($starting_date){
				if($starting_date!=""){
					$query->where('leads.created_at','>=',$starting_date);
					$query->whereOr('leads.updated_at','>=',$starting_date);
				}
			})->where(function ($query) use ($ending_date){
				if($ending_date!=""){
					$query->where('leads.created_at','<=',$ending_date);
					$query->whereOr('leads.updated_at','<=',$ending_date);
				}
			})
			->distinct()
			->orderBy('leads.updated_at', 'DESC');
			$leadsPage=$leadsQuery->get();
			Cache::put($cachename.$paramaterCacheLead.$processstatus, $leadsPage, now()->addMinutes(5));

		}
		$leadsList=$leadsPage->map( function ( $lead) use ($dateFormat){
                return [
                    'id'           => $lead->id,
					'created_at'   => date($dateFormat,strtotime($lead->created_at)),
					'update_at'   => date($dateFormat,strtotime($lead->updated_at)),
					'opportunity' => $lead->opportunity,
                    'company_name' => $lead->company_name,
					'client_name'  => $lead->client_name,
					'sale_id'  => $lead->sales_person_id,
					'sale_name'  => $lead->first_name,
					'product_id'   => $lead->product_id,
					'partner_id'   => $lead->partner_id,
					'product_name' => $lead->product_name,
					'email'        => $lead->email,
					'tags'		   => $lead->tags,
                    'phone'        => (isset($lead->phone) && $lead->phone!="")?$lead->phone:"",
					'calls'        => 0,//LogsCall::where('lead_id',$lead->id)->count(),
					'calls_record' => 0,//LogsCall::where('lead_id',$lead->id)->where('file_record','!=',"")->count(),
					'function'	   =>$lead->function,
					'source'	   =>$lead->UTM_Source,
					'UTM_Campaign' =>$lead->UTM_Campaign,
					'UTM_Medium'   =>$lead->UTM_Medium,
					'URL'		   =>$lead->URL,
					'status'	   =>$lead->status,
					'lead_type'	   =>$lead->type,
					'status_title'	=>$lead->title,
					'next_time_follow' => $lead->next_follow_up,
					'sales_person_id'=> $lead->sales_person_id, 
					'group_name'=> $lead->group_name, 
					'group_id'=> $lead->group_id, 
					'icons'=> $lead->icons, 
                ];
			}
		);
		return $leadsList;
	}

	public function create() {
		$title = trans( 'lead.new' );
		$calls = 0;
		$user=$this->userRepository->getUser();
        $partner_id=$user->partner_id;
		$this->generateParams();
		$arrayupdate["sales_person_id"]=$user->id;
		$arrayupdate["partner_id"]=$partner_id;
		$fieldexept=array();
		// Add customer 

		$fieldexept=array();
		$customFieldsData=array();
		// Add customer
		$dataLead=$this->leadRepository->store( $arrayupdate);
		$lead_id=$dataLead->id;
		return redirect( "lead/".$lead_id."/edit" );

		//return view( 'user.lead.create', compact( 'title', 'calls' ) );
	}

	public function store( LeadRequest $request ) {
		$user=$this->userRepository->getUser();
        $partner_id=$user->partner_id;
		
		$arrayupdate=$request->all();
		$phone=$request->phone;
		if($phone){
			$checkPhone=Lead::where('phone',$phone)->where('partner_id',$partner_id)->first();
			if($checkPhone!=""){
				if($checkPhone["sales_person_id"]==$user->id || $checkPhone["user_id"]==$user->id){
					return redirect( "lead/".$checkPhone["id"]."/edit" );
				}else{
					return redirect( "lead/create?messenger=Thông tin KH đã tồn tại vui lòng kiểm tra lại thông tin" );
				}
			}
		} 
		if($request->sales_person_id=="" || $request->sales_person_id==0){
			$arrayupdate["sales_person_id"]=$user->id;
		}
		$arrayupdate["partner_id"]=$partner_id;
		$arrayupdate["contact_name"]=trim($arrayupdate["opportunity"]);

		$fieldexept=array();
		// Add customer 

		$fieldexept=array();
		$customFieldsData=array();
		// Add customer
		$customFields = CustomFields::where("partner_id",$partner_id)->where('type',"leads")->orderBy("position", "asc")->get();
		if($customFields){
			foreach($customFields as $listCustomerList){
				unset($arrayupdate[$listCustomerList["field_name"]]);
			}
		}
		$dataLead=$this->leadRepository->store( $arrayupdate);


		if($customFields){
			$customFieldsData=array();
			foreach($customFields as $listCustomerList){
				$listCustomerData[]=['field_id'=>$listCustomerList["id"],'item'=>$dataLead->id,'field_value'=>$request->input($listCustomerList["field_name"])];
			}
			if(count($customFieldsData)>0){
				CustomFieldsData::insert($customFieldsData);
			}
		}
		//
		return redirect( "lead" );
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function edit($lead, Request $request) {
		$linkfull=urlencode($request->fullUrl());
		$lead_id=$lead;
		$userData=$this->userRepository->getUser();
		$grouppermission=GroupUser::getGroup();
		//$listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
		$listUser=$this->memberUsers($userData["id"]);
		$user_id=$userData->id;
	//	array_push($listUser,$user_id);
		/*
		if(count($listUser)<=0){
			$listUser=array($user_id);
		} */
		$listUser=array_unique($listUser);

		$lead = Lead::where('id',$lead_id)
		->where('partner_id',$userData->partner_id)
		->where(function ($query1)  use ($listUser, $userData){
			if($listUser!="" && count($listUser)>=0){
				if($userData->user_id!=1){
					$query1->whereIn('sales_person_id',$listUser);
					$query1->orWhereIn('user_id',$listUser);
					$query1->orWhereIn('customer_care_id',$listUser);
				}
			}
		})
		->first();//$this->leadRepository->find($lead)->where();;
		if(!$lead || $lead==""){
			die("Lead không tồn tại");
		}
		$title = trans( 'lead.edit' );
		$calls  = $lead->calls()->count();
		$cities = City::where('country_id', $lead->country_id)->orderBy('name', 'asc')->pluck('name', 'id');
		$district = District::where('cities_id', $lead->city_id)->orderBy('name', 'asc')->pluck('name', 'id');
		$ward = Ward::where('districts_id', $lead->district_id)->orderBy('name', 'asc')->pluck('name', 'id');
		$userData=$this->userRepository->getUser();
		$partner_id=$userData->partner_id;
		$tagData = Tag::where([['partner_id',$partner_id],['group_client_id',$lead->group_id]])->orderBy('position','asc')->get();
		$tagListSelect=LeadsTags::select('tag_id')->where('lead_id',$lead_id)->get()
		->map( function ( $data ) {
			return [
				'value' => $data->tag_id,
			];
		} )->pluck('value')->toArray();
		/*
		$tagListSelect=[];
		if($tagSelect!=""){
			$tagListSelect=$tagSelect;
		} */

		$paramater=array("group_id"=>$lead->group_id);
		if(isset($lead->status) && $lead->status!=""){
			$groupDetail=CallActionStatus::where([["id",$lead->status],["partner_id",$lead->partner_id]])->first();
			if($groupDetail!=""){
				array_push($paramater,array("type_client"=>$groupDetail->type));
			}
		}
		$this->generateParams($paramater);
		$tagListData=array();
		if($tagData){
			foreach($tagData as $key=>$values){
				$select="";
				if(count($tagListSelect)>0){
					if(in_array($values["id"], $tagListSelect)){
						$select='active';
					}
				}
				$tagListData[]=array('id'=>$values["id"], 'title'=>$values["title"], 'active'=>$select);
			}
		} 
		//$tagData = Tag::where("partner_id",$partner_id)->orderBy("position", "DESC")->get();
		/*
		$tags=$tagData->map(function ($list) {
			return [
				'title' => $list->title,
				'value' =>  $list->title,
			];
		})->pluck( 'title', 'value')->prepend(trans('lead.select_tags'), '');
		*/
		//$grouppermission=GroupUser::getGroup();
		//$listUser=$this->userRepository->getUserListSearch($grouppermission, $userData);
		// Add customer
		$listCustomerData=array();
		$customerFieldData=CustomFieldsData::where('item',$lead_id)->get();
		if($customerFieldData){
			foreach($customerFieldData as $listData){
				$listCustomerData[$listData["field_id"]]=$listData["field_value"];
			}
		}
		
		return view( 'user.lead.edit', compact( 'lead', 'tagListData', 'title', 'calls', 'ward', 'district', 'cities', 'linkfull', 'userData', 'listCustomerData', 'tagListSelect') );
	}
 
	public function update( $lead, LeadRequest $request ) {
		$lead_id=$lead;
		$lead = $this->leadRepository->find($lead);

		$userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;

		$product_id=$request->product_id;
		//$partner=Product::where('id','=',$product_id)->first();
		$request->merge([
			'partner_id' => $partner_id,
		]);
		$arrayupdate=$request->all();
		if($request->sales_person_id=="" || $request->sales_person_id==0){
			$arrayupdate["sales_person_id"]=$userData->id;
		}
		$arrayupdate["contact_name"]=trim($arrayupdate["opportunity"]);

		if($arrayupdate["phone"]!="" && $arrayupdate["phone"]!=$lead["phone"]){
			$leadPhone=Lead::where('phone', $arrayupdate["phone"])->where('partner_id',$partner_id)->first();
			if(isset($leadPhone) && $leadPhone!=""){
				//return response()->json(['mess' => 'Số ĐT thoại '.$phone.' đang tồn tại trong KH/Lead Tên là: '.$leadPhone["opportunity"].' ID '.$leadPhone["id"], 'phone'=>$phone], 200);
				$arrayupdate["phone"]=$lead["phone"];
			}
		}

		$fieldexept=array();
		$customFieldsData=array();
		$listCustomerData2=array();
		// Add customer
		$customFields = CustomFields::where("partner_id",$partner_id)->where('type',"leads")->orderBy("position", "asc")->get();
		if($customFields){
			foreach($customFields as $listCustomerList){
				$listCustomerData[]=['field_id'=>$listCustomerList["id"],'item'=>$lead_id,'field_value'=>$request->input($listCustomerList["field_name"])];
				$listCustomerData2[]='("'.$listCustomerList["id"].'","'.$lead_id.'", "'.$request->input($listCustomerList["field_name"]).'")';
				unset($arrayupdate[$listCustomerList["field_name"]]);

			}
		}
			//	$arrayupdate = collect($arrayupdate)->except(implode(',',$fieldexept))->toArray();
			//	\array_splice($arrayupdate,implode(',',$fieldexept));
				//$arrayupdate = array_diff($arrayupdate, $fieldexept);
		$lead->update( $arrayupdate);

		if(count($listCustomerData2)>0){
			DB::insert('insert into `customer_field_data` (`field_id`, `item`, `field_value`) values'.implode(',',$listCustomerData2).' on duplicate key update field_id=values(field_id),field_value=values(field_value)');
			//DB::insert('insert into `contacts` (`phone`, `device_id`, `name`, `user_id`, `date_create`) values (?, ?, ?, ?, ?) on duplicate key update '.$keyUpdate[$i],$listInsert2[$i]);
		//	CustomFieldsData::insert($customFieldsData);
		}
		//
		 return redirect( "lead" );
		//return redirect()->back();
	}	

	public function show( $lead ) {
        $lead = $this->leadRepository->find($lead);
		$title  = trans( 'lead.show' );
		$action = "show";
		$this->generateParams();
		return view( 'user.lead.show', compact( 'title', 'lead', 'action' ) );
	}

	public function delete( $lead ) {
        $lead = $this->leadRepository->find($lead);
		$title = trans( 'lead.delete' );
		$this->generateParams();
        $action = "delete";
		return view( 'user.lead.delete', compact( 'title', 'lead','action' ) );
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy( $lead ) {
        $lead = $this->leadRepository->find($lead);
		$lead->calls()->delete();
		$lead->delete();

		return redirect( 'lead' );
	}

	public function data( Datatables $datatables ) {
		$dateFormat = config('settings.date_format');
		$leads = $this->leadRepository->orderBy('id', 'DESC')->getAll()
            ->with( 'country', 'salesTeam' )
            ->get()
            ->map( function ( $lead ) use ($dateFormat){
                return [
                    'id'           => $lead->id,
					'created_at'   => date($dateFormat,strtotime($lead->created_at)),
					'opportunity' => $lead->opportunity,
                    'company_name' => $lead->company_name,
					'client_name'  => $lead->client_name,
					'sale_name'  => $this->userRepository->findByField('id',$lead->sales_person_id)->pluck('first_name')->first(),
					'product_id'   => $lead->product_id,
					'product_name' => $this->productRepository->findByField('id',$lead->product_id)->pluck('product_name'),
                    'email'        => $lead->email,
                    'phone'        => $lead->phone,
					'calls'        => $lead->calls->count(),
					'function'	   =>$lead->function,
					'status'	   =>$lead->status,
					'status_title'	=> CallActionStatus::where('id',$lead->status)->pluck('title'),
					'next_time_follow' => $lead->next_follow_up,
					'sales_person_id'=> $lead->sales_person_id
                ];
			}
		);
		return $datatables->collection( $leads )
            ->addColumn( 'actions', '@if(Sentinel::getUser()->hasAccess([\'leads.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'lead/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning"></i> </a>
                                        <a href="{{ url(\'leadcall/\'. $id .\'/\' ) }}" title="{{ trans(\'table.calls\') }}">
                                            <i class="fa fa-fw fa-phone text-primary"></i> <sup>{{ $calls }}</sup></a>
                                    @endif
                                     @if(Sentinel::getUser()->hasAccess([\'leads.read\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'lead/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                    @endif
                                    @if(Sentinel::getUser()->hasAccess([\'leads.delete\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'lead/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
									@endif' )
			->addColumn( 'status_title', '@if($status==5 || $status==6) <span class="win"> {{ $status_title}} </span> @else    <span class="not_win_yet"> {{ $status_title }} </span> @endif')
            ->removeColumn( 'calls' )
            ->rawColumns(['actions','status_title'])->make();
	}

	public function ajaxStateList( Request $request ) {
		$state = State::where('country_id',$request->id)->orderBy('name', 'asc')->get()->pluck('name','id')->prepend(trans('company.select_state'), 'data');
		return $state;
	}

	public function ajaxCityList( Request $request ) {
		$city = City::where('country_id',$request->id)
		->orderBy('name', 'asc')
		->get()
		->map(function ($list) {
			return [
				'value' => $list->id,
				'title' =>  $list->name,
			];
		})
		->pluck('title', 'value' )->prepend(trans('lead.select_city'), '');
        return $city;
	}
	public function ajaxDistrictList( Request $request ) {
		$districts = District::where('cities_id',$request->id)
		->orderBy('name', 'asc')->get()
		->map(function ($list) {
			return [
				'value' => $list->id,
				'title' =>  $list->name,
			];
		})
		->pluck( 'title', 'value' )->prepend(trans('lead.select_district'), '');

        return $districts;
	}
	public function ajaxWardList( Request $request ) {
		$wards = Ward::where('districts_id',$request->id)->orderBy('name', 'asc')->get()
		->map(function ($list) {
			return [
				'value' => $list->id,
				'title' =>  $list->name,
			];
		})
		->pluck('title','value')
		->prepend(trans('lead.select_ward'), '');

        return $wards;
	}
	public function ajaxStausGroup( Request $request ) {
		$userData=$this->userRepository->getUser();
		$partner_id=$userData->partner_id;
		$group=$request->group_id;
		$callStatus = CallActionStatus::select('call_action_status.*')->join('group_client_status','group_client_status.client_status_id','=','call_action_status.id')->where("group_client_status.partner_id",$partner_id)->where("group_client_status.group_client_id",$group)->orderBy("call_action_status.position", "asc")
		->get()
		->map(function ($list) {
			return [
				'id' => $list->id,
				'title' => $list->title,
				'value' =>  $list->id,
			];
		})->pluck( 'title', 'value' )->prepend(trans('lead.status'), '');	

        return $callStatus;
	}
	private function generateParams($paramater="") {
		$userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;

		
		$priorityCache = cache('priorityCache'.$partner_id);
		if(isset($priorityCache) && $priorityCache!=""){
			$priority=$priorityCache;
		}else{
			$priority = $this->optionRepository->getAll()->where( 'category', 'priority' )->get()
		                                   ->map( function ( $title ) {
			                                   return [
				                                   'title' => $title->title,
				                                   'value' => $title->value,
			                                   ];
		                                   } )->pluck( 'title', 'value' );
			Cache::put('priorityCache'.$partner_id, $priority, now()->addMinutes(100));
		}

		$titlesCache = cache('titlesCache'.$partner_id);
		if(isset($titlesCache) && $titlesCache!=""){
			$titles=$titlesCache;
		}else{
			$titles = $this->optionRepository->getAll()->where( 'category', 'titles' )->get()
		                                 ->map( function ( $title ) {
			                                 return [
				                                 'title' => $title->title,
				                                 'value' => $title->value,
			                                 ];
		                                 } )->pluck( 'title', 'value' )
											->prepend(trans('lead.select_title'), '');
											
			Cache::put('titlesCache'.$partner_id, $titles, now()->addMinutes(100));
		}

		$companiesCache = cache('companiesCache'.$partner_id);
		if(isset($companiesCache) && $companiesCache!=""){
			$companies=$companiesCache;
		}else{
			$companies = $this->companyRepository->getAll()->orderBy( "name", "asc" )->pluck( 'name', 'id' )
		                                     ->prepend( trans( 'dashboard.select_company' ), '' );
											
			Cache::put('companiesCache'.$partner_id, $companies, now()->addMinutes(100));
		}

		$countriesCache = cache('countriesCache'.$partner_id);
		if(isset($countriesCache) && $countriesCache!=""){
			$countries=$countriesCache;
		}else{
			$countries = $this->countryRepository->orderBy('name', 'asc')->pluck('name', 'id')->prepend(trans('lead.select_country'),'');
											
			Cache::put('countriesCache'.$partner_id, $countries, now()->addMinutes(100));
		}
        
		//$staffs = $this->userRepository->getStaff()->pluck( 'full_name', 'id' )
		//									->prepend( trans( 'dashboard.select_staff' ), '' );


		//$listUserSales=$this->userRepository->getAllStaffOfUser($userData->id);
		$grouppermission=GroupUser::getGroup();
		$listUserSales=$this->userRepository->getUserListSearch($grouppermission, $userData);
        $staffs="";
        
		/*
		$staffsListCache = cache('staffsListCache'.$partner_id);
		if(isset($staffsListCache) && $staffsListCache!=""){
			$staffs=$staffsListCache;
		}else{ */
			if(isset($listUserSales) && $listUserSales!=""){
				$staffs=User::join('partner_user','partner_user.user_id','=','users.id')
				->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
				->where('partner_user.partner_id','=',$userData->partner_id)
				->whereIn('users.id',$listUserSales)
				->get()
				->map( function ( $salesList ) {
					return [ 
						'title' => $salesList->first_name." ".$salesList->last_name,
						'value' => $salesList->id,
					];
				} )->pluck( 'title', 'value')
				->prepend($userData->first_name." ".$userData->last_name, $userData->id);
			}else{
				$staffs=User::join('partner_user','partner_user.user_id','=','users.id')
				->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
				->where('partner_user.partner_id','=',$userData->partner_id)
				->get()
				->map( function ( $salesList ) {
					return [ 
						'title' => $salesList->first_name." ".$salesList->last_name,
						'value' => $salesList->id,
					];
				} )->pluck( 'title', 'value')
				->prepend($userData->first_name." ".$userData->last_name, $userData->id);
			}

			//Cache::put('staffsListCache'.$partner_id, $staffs, now()->addMinutes(20));
		//} 

		$salesteamsCache = cache('salesteamsCache'.$partner_id);
		if(isset($salesteamsCache) && $salesteamsCache!=""){
			$salesteams=$salesteamsCache; 
		}else{
			$salesteams = $this->salesTeamRepository->getAll()->orderBy( "id", "asc" )->where("partner_id",$partner_id)
		                                        ->pluck( 'salesteam', 'id' )
												->prepend( trans( 'dashboard.select_sales_team' ), '');
			Cache::put('salesteamsCache'.$partner_id, $salesteams, now()->addMinutes(100));
		}

		$productsCache = cache('productsCache'.$partner_id);
		if(isset($productsCache) && $productsCache!=""){
			$products=$productsCache;
		}else{
			$products= $this->productRepository->getAll()->where( 'partner_id', $partner_id)
												->pluck('product_name', 'id')
												->prepend(trans('product.products'), '');
			Cache::put('productsCache'.$partner_id, $products, now()->addMinutes(100));
		}
		
		$functionsCache = cache('functionsCache1'.$partner_id);
		if(isset($functionsCache) && $functionsCache!=""){
			$functions=$functionsCache;
		}else{
			$functions = $this->optionRepository->getAll()->where( 'partner_id', $partner_id)->where( 'category', 'function_type' )->get()
            ->map( function ( $title ) {
                return [
                    'title' => $title->title,
                    'value' => $title->id,
                ];
            } )->pluck( 'title', 'value' )
			->prepend(trans('lead.select_function'), '');
			Cache::put('functionsCache1'.$partner_id, $functions, now()->addMinutes(100));
		} 

		
		$groupStaffCache = cache('groupStaff'.$partner_id);
		if(isset($groupStaffCache) && $groupStaffCache!=""){
			$groupStaff=$groupStaffCache;
		}else{
			$groupStaff = GroupUser::where( 'partner_id', $partner_id)->get()
            ->map( function ( $title ) {
                return [
                    'title' => $title->name,
                    'value' => $title->id,
                ];
            } )->pluck( 'title', 'value' )
			->prepend(trans('lead.select_group_user'), ''); 

			Cache::put('groupStaffCache'.$partner_id, $groupStaff, now()->addMinutes(100));
		} 

		$group_id=0;
		if(isset($paramater["group_id"]) && $paramater["group_id"]!=""){
			$group_id=$paramater["group_id"];
		}

		

		$callStatusQueryCache = cache('callStatusQueryCache1'.$group_id.$partner_id);
		if(isset($callStatusQueryCache) && $callStatusQueryCache!=""){
			$callStatusQuery=$callStatusQueryCache;
		}else{
			$callStatusQuery = CallActionStatus::where("partner_id",$partner_id)->where('status',1)
			->where(function ($query)  use ($group_id){
				if($group_id!="" && $group_id!="0"){
					$query->where('type',$group_id);
				}
			})
			
			->orderBy("position", "asc")
			->get();
			
			Cache::put('callStatusQueryCache1'.$partner_id.$group_id, $callStatusQuery, now()->addMinutes(100));
		} 
		
		$callStatus=$callStatusQuery->map(function ($list) {
			return [
				'id' => $list->id,
				'title' => $list->title,
				'value' =>  $list->id
			];
		})->pluck( 'title', 'value');	


		

		$tagDataCache = cache('tagDataCache'.$partner_id);
		if(isset($tagDataCache) && $tagDataCache!=""){
			$tagData=$tagDataCache;
		}else{
			$tagData = Tag::where("partner_id",$partner_id)->where('group_client_id',$group_id)->orderBy("position", "asc")
		->get();
			if(count($tagData)<=0){
			$tagData = Tag::where("partner_id",0)->where('group_client_id',$group_id)->orderBy("position", "asc")
			->get();
			}
			Cache::put('tagDataCache'.$partner_id, $tagData, now()->addMinutes(10));
		} 

		$tags=$tagData->map(function ($list) {
			return [
				'title' => $list->title,
				'value' =>  $list->title,
			];
		})->pluck( 'title', 'value')->prepend(trans('lead.select_tags'), '');

		

		$customFieldsCache = cache('customFieldsCache'.$partner_id);
		if(isset($customFieldsCache) && $customFieldsCache!=""){
			$customFields=$customFieldsCache;
		}else{
			$customFields = CustomFields::where("partner_id",$partner_id)->where('type',"leads")->orderBy("position", "asc")->get();
			Cache::put('customFieldsCache'.$partner_id, $customFields, now()->addMinutes(100));
		} 

		$groupLeadCache = cache('groupLeadCache'.$partner_id);
		if(isset($groupLeadCache) && $groupLeadCache!=""){
			$groupLead=$groupLeadCache;
		}else{
			$groupLead = GroupLead::select('group_client.*')
			->join('group_client_status','group_client_status.group_client_id','=','group_client.id')
			->join('call_action_status','call_action_status.id','=','group_client_status.client_status_id')
			->where("group_client.partner_id",$partner_id)
			->orderBy("group_client.position", "asc")
			->get()->map(function ($list) {
				return [
					'title' => $list->name,
					'value' =>  $list->id,
				];
			})->pluck( 'title', 'value')->prepend(trans('lead.select_group_client'), '');
			Cache::put('groupLeadCache'.$partner_id, $groupLead, now()->addMinutes(100));
		}

		$brandCacheCache = cache('brandCache'.$partner_id);
		if(isset($brandCacheCache) && $brandCacheCache!=""){
			$brand=$brandCacheCache;
		}else{
			$brand=Brand::where('partner_id',$partner_id)->orderBy("name", "asc")
			->get()->map(function ($list) {
				return [
					'title' => $list->name,
					'value' =>  $list->id,
				];
			})->pluck( 'title', 'value')->prepend(trans('lead.select_brand'), '');
			Cache::put('brandCache'.$partner_id, $brand, now()->addMinutes(100));
		}

		


		view()->share( 'priority', $priority );
		view()->share( 'titles', $titles );
		view()->share( 'companies', $companies );
		view()->share( 'countries', $countries );
		view()->share( 'staffs', $staffs );
		view()->share( 'salesteams', $salesteams );
		view()->share( 'functions', $functions );
		view()->share( 'products', $products );
		view()->share( 'callStatus', $callStatus );
		view()->share( 'callStatusQuery', $callStatusQuery );
		view()->share( 'tagsList', $tags );
		view()->share( 'groupLead', $groupLead);
		view()->share( 'customFields', $customFields);
		view()->share( 'groupStaff', $groupStaff);
		view()->share( 'brand', $brand);

		
	}

	public function downloadExcelTemplate() {
        if (ob_get_length()) ob_end_clean();
        $path = base_path('resources/excel-templates/leads.xlsx');

        if (file_exists($path)) {
            return response()->download($path);
        }

        return 'File not found!';
	}

	public function getImport() {
		$title = trans( 'lead.newupload' );
		$this->generateParams();
		//  return 'jimmy';
		return view( 'user.lead.import', compact( 'title' ) );
	}

	public function getImportEmail() {
		$title = trans( 'lead.newupload' );
		$userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
		$emailExitCheckStatus = new EmailCheckStatus;
		$number_check_email=0;
		$total_email_checked=0;
		$mailPartner=EmailCheckStatus::where('partner_id',$partner_id)->first();//->count();
		if($mailPartner){
			$emailCheckPartner=$mailPartner["id"];
			$linnkma=$mailPartner["link_ma"];
			$segment=$mailPartner["segment"];
			$number_check_email=$mailPartner["number_check_email"];
			$total_email_checked=$mailPartner["total_email_checked"];
		}
		//  return 'jimmy';
		return view( 'user.lead.importemail', compact( 'title', 'number_check_email', 'total_email_checked') );
	}

	public function postImportEmail( Request $request ) {
		$userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
		$request->validate([
            'import_file' => 'required'
		]);

		$path = $request->file('import_file')->getRealPath();
		$data = Excel::load($path)->get();
		$linkMa=$request->domainma;
		$seagment=$request->segment;
		$datatype=$request->datatype;

		$emailExitCheckStatus = new EmailCheckStatus;
		$mailPartner=EmailCheckStatus::where('partner_id',$partner_id)->first();//->count();
		if($mailPartner){
			$emailCheckPartner=$mailPartner["id"];
			$linnkma=$mailPartner["link_ma"];
			$segment=$mailPartner["segment"];
			$number_check_email=$mailPartner["number_check_email"];
			$email_check_status=$mailPartner["email_check_status"];
		}else{
			echo "Vui lòng đăng ký sử dụng dịch vụ qua Hotline: 0908606456";
			exit();
		}
		if($data->count() > 0)
		{
			$totalEmailUpdate=0;
			foreach($data->toArray() as $key => $value)
			{
				if($datatype=="email"){
					if (filter_var($value['email'], FILTER_VALIDATE_EMAIL)) {
						$insert_data=null;
						$type  = $datatype;
						$fullname  = $value['name'];
						$phone   = $value['phone'];
						$email   = $value['email'];
						$emailExitCheck = new EmailCheck;
						$countEmail=EmailCheck::where('email',$email)->where('partner_id',$partner_id)->count();
						if($countEmail<=0){
							$emailExitCheck->email_check_id = $emailCheckPartner;
							$emailExitCheck->partner_id = $partner_id;
							$emailExitCheck->email = $email;
							$emailExitCheck->phone =  $phone;
							$emailExitCheck->full_name=$fullname;
							$emailExitCheck->type_data = $datatype;
							$emailExitCheck->segment_ma = $seagment;

							$emailExitCheck->status_check=0;
							$emailExitCheck->save();
							$totalEmailUpdate++;
						}
					}
				}else{
					$insert_data=null;
					$fullname  = $value['name'];
					$phone   = $value['phone'];
					$email   = $value['email'];
					$emailExitCheck = new EmailCheck;
					$countEmail=EmailCheck::where('email',$email)->where('partner_id',$partner_id)->count();
					if($countEmail<=0){
						$emailExitCheck->email_check_id = $emailCheckPartner;
						$emailExitCheck->partner_id = $partner_id;
						$emailExitCheck->email = $email;
						$emailExitCheck->phone =  $phone;
						$emailExitCheck->full_name=$fullname;
						$emailExitCheck->status_check=0;
						$emailExitCheck->type_data = $datatype;
						$emailExitCheck->segment_ma = $seagment;
						$emailExitCheck->save();
						$totalEmailUpdate++;
					}
				}
			}
			if($totalEmailUpdate>0){
				EmailCheckStatus::where('partner_id',$partner_id)->update(['number_check_email' => $totalEmailUpdate]);
			}

		}
		//Add contact
		return redirect( "lead/importemail" );
	}
	

	public function postImport2( Request $request ) {
		$request->validate([
            'import_file' => 'required'
		]);
		$path = $request->file('import_file')->getRealPath();
		$data = Excel::load($path)->get();
		//$data = $this->excelRepository->load( $request->file( 'file_exists' ));
		$linkMa=$request->domainma;
		$seagment=$request->segment;
		
		if($data->count() > 0)
		{
			foreach($data->toArray() as $key => $value)
			{
				$insert_data=null;
				$fullname  = $value['name'];
				$phone   = $value['phone'];
				$email   = $value['email'];
				$priority    = $value['priority'];
				$utm_source  = $value['utm_source'];
				$utm_campaign   = $value['utm_campaign'];
				$partner_id  = $value['partner_id'];
				$product_id   = $value['product_id'];
				$product_name   = $value['product_name'];
				$internal_notes   = $value['internal_notes'];
				
				$partner=Product::where('id','=',$product_id)->first();
				$partner_id=0;
				if($partner){
					$partner_id=$partner->partner_id;
				}
				if($phone){
					$lead = new Lead;
					$countLead=Lead::where('phone',$phone)->where('product_id',$product_id)->count();
					if($countLead<=0){
						$lead->opportunity = $fullname;
						$lead->partner_id = $partner_id;
						$lead->title = "";
						$lead->email = $email;
						$lead->phone =  $phone;
						$lead->function=$utm_source;
						$lead->cookie_id="";
						$lead->status=0;
						$lead->internal_notes=$internal_notes;
						$lead->client_name="";
						$lead->user_id =0;
						$lead->sales_person_id =0;
						$lead->contact_name =$fullname;
						$lead->tags ="";
						$lead->sales_team_id =0;
						$lead->product_id =$product_id;
						$lead->UTM_Source =$utm_source;
						$lead->UTM_Campaign=$utm_campaign;
						$lead->UTM_Medium="";
						$lead->UTM_Term="";
						$lead->UTM_Content="";
						$lead->URL="";
						$lead->PID="";
						$lead->PSID="";
						$lead->GCLID="";
						$lead->FBCLID="";
						$lead->token=md5($product_id);
						$lead->save();
					}
				}

			}
		}
		return redirect( "lead" );
	}
	public function postImport( Request $request ) {
		
		$request->validate([
			'import_file' => 'required'
		]);  
		$userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
		$path = $request->file('import_file')->getRealPath();
		$data = Excel::load($path)->get();
		$status=$request->status;
		$group_id=$request->group_id;
		if($status==""){
			$status=0;
		}
		if($group_id==""){
			$group_id=0;
		}
		if($data->count() > 0)
		{
			foreach($data->toArray() as $key => $value)
			{
				$sales_person_id=0;
				if(isset($value['sales_id']) && $value['sales_id']!=""){
					$sales_person_id=$value['sales_id'];
				}else{
					$sfaff=User::where([['partner_id',$partner_id],['email',$value['sales']]])->first();//->count();
					if($sfaff!=""){
						$sales_person_id  = $sfaff->id;
					}
				}

				$insert_data=null;
				$lead_code   = $value['ma'];
				$fullname  = $value['ten_khach_hang'];
				$phone   = $value['dien_thoai'];
				$email   = $value['email'];
				$birthday   = $value['ngay_sinh'];
				$gender=$value['gioi_tinh'];
				$website = $value['website'];
				$fax = $value['fax'];
				$tax_code   = $value['ma_so_thue'];
				$internal_notes   =$value['mo_ta'];;
				$mobile="";
				$contactName=$fullname;
				$address_2=$value['dia_chi_2'];
				$address=$value['dia_chi_1'];
				$city_title=$value['tinh_thanh'];
				$district_title=$value['quan_huyen'];
				$ward=$value['phuong_xa'];
				$tagsTitle=$value['nhan_kh'];
				$page_id="";
				if(isset($value['page_id']) && $value['page_id']!=""){
					$page_id=$value['page_id'];
				}
				$priority    = 1;
				$utm_source  = "Offline";
				$utm_campaign   = "";
				$stringvaluepsid=$value['psid'];
				if(substr($stringvaluepsid,0,1)=="N"){
					$psid=substr($stringvaluepsid,1);
				}else{
					$psid=$stringvaluepsid;
				}
				if($psid<=0 || strlen($psid)<10){
					$psid="";
				}				
				$product_id   = "";
				$product_name   ="";
				$birthday="";
				$tag_id="";
				$phone=substr($phone,2);
				if(substr($phone,0,1)!="0"){
					$phone="0".$phone;
				}
				
				if($phone || $psid!="" || $email!=""){
					$lead = new Lead;
					$countLead="";
					if($phone!="" && $phone>0 && strlen(trim($phone))>10){
						$countLead=Lead::where('partner_id',$partner_id)->where('phone', $phone)->first();
						
					}
					if($countLead=="" && $psid!="" && strlen(trim($psid))>10){
						$countLead=Lead::where('partner_id',$partner_id)->where('psid', $psid)->first();
						
					}
					if($countLead=="" && $email!="" && strlen(trim($email))>4){
						$countLead=Lead::where('partner_id',$partner_id)->where('email', $email)->first();
					}
					
					if($tagsTitle!=""){
						$tags=Tag::where([['partner_id',$partner_id],['title',$tagsTitle]])->first();
						if($tags==""){
							$tags = new Tag;
							$tags->title = $tagsTitle;
							$tags->partner_id = $partner_id;
							$tags->status = 1;
							$tags->color_bg = "#f41e4b";
							$tags->color_text = "#fff";
							$tags->save();
						}
						$tag_id=$tags->id;
					}
					//$countLead=Lead::where('phone',$phone)->where('partner_id',$partner_id)->first();
					$disctrict_id=0;
					$city_id=0;
					$ward_id=0;
					if($city_title!=""){
						$cities = City::where('name', $city_title)->first();
						$city_id=$cities["id"];
					}
					if($city_id!="" && $district_title!="" && $city_id!=0){
						$district = District::where('cities_id', $city_id)->where('name', $district_title)->first();
						$disctrict_id=$district["id"];
					}
					if($disctrict_id!="" && $disctrict_id!=0 && $ward!=""){
						$warddata = Ward::where('districts_id', $disctrict_id)->where('name', $ward)->first();
						$ward_id=$warddata["id"];
					}  
					if($countLead=="" || $countLead==null){
						$lead->opportunity = $fullname;
						$lead->partner_id = $partner_id;
						$lead->gender = $gender;
						$lead->fax = $fax;
						$lead->lead_code = $lead_code;
						$lead->email = $email;
						$lead->birth_day=$birthday;
						$lead->phone =  $phone;
						$lead->psid =  $psid;
						$lead->function=$utm_source;
						$lead->tax_code=$tax_code;
						$lead->mobile=$mobile;
						$lead->address_2=$address_2;
						$lead->city_title=$city_title;
						$lead->district_title=$disctrict_id;
						$lead->ward=$ward;
						$lead->city_id=$city_id;
						$lead->district_id=$disctrict_id;
						$lead->ward_id=$ward_id;
						$lead->cookie_id="";
						$lead->status=$status;
						$lead->page_id=$page_id;
						$lead->internal_notes=$internal_notes;
						$lead->contact_name =$fullname;
						$lead->client_name=$fullname;
						$lead->user_id =$userData->id;
						$lead->sales_person_id=$sales_person_id;
						$lead->group_id=$group_id;
						$lead->tags=$tagsTitle;
						$lead->sales_team_id =0;
						$lead->product_id ="";
						$lead->UTM_Source =$utm_source;
						$lead->UTM_Campaign=$utm_campaign;
						$lead->UTM_Medium="";
						$lead->UTM_Term="";
						$lead->UTM_Content="";
						$lead->URL="";
						$lead->PID="";
						$lead->GCLID="";
						$lead->FBCLID="";
						$lead->token="";
						$lead->save();
						$lead_id=$lead->id;
					}else{
						$leadUpdate = Lead::find($countLead->id);
						if($page_id!=""){
							$leadUpdate->page_id=$page_id;
						}
						if($fullname!=""){
							$leadUpdate->opportunity=$fullname;
							$leadUpdate->contact_name=$fullname;
						}
						if($group_id!=""){
							$leadUpdate->group_id=$group_id;
						} 
						if($status!="" && $status!=0){
							$leadUpdate->status=$status;
						}
						if($sales_person_id!="" && $sales_person_id>0 && $leadUpdate->sales_person_id<=0){
							$leadUpdate->sales_person_id=$sales_person_id;
						}
						if($utm_source!=""){
							$leadUpdate->function=$utm_source;
							$leadUpdate->UTM_Source=$utm_source;
						}
						if($city_id!=""){
							$leadUpdate->city_id=$city_id;
						}
						if($disctrict_id!=""){
							$leadUpdate->disctrict_id=$disctrict_id;
						}
						if($ward_id!=""){
							$leadUpdate->ward_id=$ward_id;
						}
						if($lead_code!=""){
							$leadUpdate->lead_code=$lead_code;
						}
						if($gender!=""){
							$leadUpdate->gender=$gender;
						}
						$leadUpdate->updated_at=date("Y-m-d H:i:s");
						$result=$leadUpdate->update();
						$lead_id=$leadUpdate->id; 

					//	Lead::where('id', $countLead["id"])->where('partner_id',$partner_id)->update(['opportunity'=>$fullname, 'gender'=>$gender, 'fax'=>$fax, 'lead_code'=>$lead_code, 'email'=>$email, 'group_id'=>$group_id, 'function'=>$utm_source, 'tax_code'=>$tax_code, 'contact_name'=>$fullname, 'status'=>$status, 'city_id'=>$city_id, 'district_id'=>$disctrict_id, 'ward_id'=>$ward_id, 'sales_person_id'=>$sales_person_id]);
					}

					if($tag_id!=""){
						$tagUpdate = LeadTags::firstOrNew(['partner_id' =>  $partner_id, 'lead_id' =>$lead_id, 'tag_id' =>$tag_id]);
						$tagUpdate->user_id=$sales_person_id ;
						$tagUpdate->save();
					}
					
				}
			}
			die();
		}

		return redirect( "lead" );
	}


	public function postImportTempToMain( Request $request ) {
		$userData=$this->userRepository->getUser();
		$data = LeadsTemp::where('convert_data',0)->offset(0)
		->limit(200)->get();
		$birthday="";
		$group_id=10;
		if($data)
		{
			foreach($data as $listData)
			{
				$insert_data=null;
				$fullname  = $listData['opportunity'];
				$phone   = str_replace(" ","",$listData['phone']);
				$email   = $listData['email'];
				$partner_id=$listData['partner_id'];
				$function=$listData['function'];
				$internal_notes=$listData['internal_notes'];
				$user_id=$listData['user_id'];
				$UTM_Source=$listData['UTM_Source'];
				$ngay_hen="";
				/*
				$ngay_hen=$listData['ngay_hen'];
				$date_ship=$listData['ngay_hen'];
				if(isset($ngay_hen) && $ngay_hen!=""){
					$listNgayHen=explode("/",$ngay_hen);
					if(count($listNgayHen)>0){
						$year=date("Y");
						$month=date("m");
						$day=date("d");
						if(isset($listNgayHen[2]) && $listNgayHen[2]!=""){
							$year=$listNgayHen[2];
						}
						if(isset($listNgayHen[1]) && $listNgayHen[1]!=""){
							$month=$listNgayHen[1];
						}
						if(isset($listNgayHen[0]) && $listNgayHen[0]!=""){
							$day=$listNgayHen[0];
						}
						$date_ship=$year."-".$month."-".$day;
					}
				} */
				$muc_tieu=$listData['muc_tieu'];
				$hinh_thuc_thanh_toan=$listData['hinh_thuc_thanh_toan'];
				$sales_name=$listData['sales_name'];
				$congty=$listData['cong_type'];
				$ngaysinh=$listData['ngay_sinh'];
				$status=$listData['status'];
				$statusKhachHang=$listData['status'];
				$cong_ty=$listData['cong_ty'];
				$brand_id=$listData['brand_id'];
				$product_name=$listData['product_name'];
				$product_id=$listData['product_id'];
				$tinh_trang_khach_hang=$listData['tinh_trang_khach_hang'];;
				$nhom_truong=$listData['nhom_truong'];
				$utm_source=$listData['UTM_Source'];
				$price=$listData['price'];
				$per_id=$listData["sales_person_id"];
				$contact_name=$listData["contact_name"];
				$leadmd5=$listData["leadmd5"];

				
				if($per_id==""){
					$per_id=116;
				}

				$start=$listData["bat_dau"];

				$priority    = 1;
				$utm_campaign   = "";
				if(substr($phone,0,1)!="0"){
					$phone="0".$phone;
				}
				if($phone!="" || $email!="" || $opportunity!=""){
					$lead = new Lead;
					echo  $fullname."<br/>";
					echo  $phone."<br/>";
					echo  $email;
					if($phone!=0 || $email!="" || $leadmd5!=""){
						$countLead=Lead::where('partner_id',$partner_id)
						->where(function ($query1)  use ($phone, $email, $leadmd5){
							if($phone!="" && $phone!=0){
								$query1->where('phone', $phone);
							}elseif($email!=""){
								$query1->where('email',$email);
							}else{
								$query1->where('leadmd5',$leadmd5);
							}
						})
						->first();	
					}else{
						$countLead="";
					}
					$lead_id=0;
					if($countLead==""){
						$lead->opportunity = $fullname;
						$lead->partner_id = $partner_id;
						$lead->email = $email;
						$birthday=$ngaysinh;
						if($ngaysinh!=""){
							$ngaysinhex=explode("/",$ngaysinh);
							if(count($ngaysinhex)>=2){
								$birthday=$ngaysinhex[2].'-'.$ngaysinhex[1].'-'.$ngaysinhex[0];
							}
						} 
						//$lead->birth_day=$birthday;
						$lead->phone =  $phone;
						$lead->function=$utm_source;
						$lead->cookie_id="";
						$lead->status=$status;
						$statusKhachHang=$status;
						$lead->leadmd5=$leadmd5;
						
						/*
						$statusKhachHang=CallActionStatus::where('partner_id',$partner_id)->where('title',$tinh_trang_khach_hang)->first();
						$status=0;
						if($statusKhachHang){
							$status=$statusKhachHang["id"];
						}  */

						$lead->internal_notes=$internal_notes;
						$lead->contact_name =$fullname;
						//$lead->client_name=$cong_ty;
						$lead->user_id =$userData->id;
						
						$lead->tags ="";
						$lead->sales_team_id =0;
						$lead->product_id =$product_id;
						$lead->product_name =$product_name;
						$lead->brand_id =$brand_id;
						$lead->UTM_Source =$utm_source;
						$lead->UTM_Campaign=$utm_campaign;
						/*
						$sales=User::where('partner_id',$partner_id)->where('nick_name',$sales_name)->first();
						if($sales){
							$lead->sales_person_id =$sales["id"];
							$per_id=$sales["id"];
						}else{
							$lead->sales_person_id =$userData->id;
							$per_id=$userData->id;
						} */
						$lead->sales_person_id=$per_id;
						$lead->UTM_Medium="";
						$lead->UTM_Term="";
						$lead->UTM_Content="";
						$lead->URL="";
						$lead->PID="";
						$lead->PSID="";
						$lead->GCLID="";
						$lead->FBCLID="";
						$lead->token="";
						$lead->save();
						$lead_id=$lead->id;
					}else{
						$lead_id=$countLead["id"];
					}
					if($lead_id>0){
						$saleorder = $this->salesOrderRepository->getAll()->withDeleteList()->get()->count();
						if($saleorder == 0){
							$total_fields = 0;
						}else{
							$total_fields = $this->salesOrderRepository->getAll()->withDeleteList()->get()->last()->id;
						}
						$start_number = Settings::get('sales_start_number');
						$saleorder_no = "FCRM" . (is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields : 0) + 1);
						$arrayupdate["sales_person_id"]=$per_id;
						if($start==null || $start==""){
							$date_ship=date("Y-m-d");
						}else{
							$date_ship=$start;
						}
						$arrayupdate["date_ship"]=$date_ship;
						if($date_ship!=""){
							$date_exp=date("Y-m-d",strtotime('+30 days',strtotime($date_ship)));
						}else{
							$date_exp=date("Y-m-d",strtotime('+30 days'));
						}
						
					
						/*
						if($product_name!=""){
						$product_id=$this->productRepository->findByField('product_name',$product_name)->pluck('id')->first();
						} */
						if($product_id!="" && $product_id!=0){
							/*
							$listInsert2=array();
							$keyUpdate=array();
							$listInsert2=[$product_id, $lead_id, 1];
							$keyUpdate="product_id='".$product_id."', lead_id=".$lead_id;
							try {
								$DataList=$listInsert2;
									DB::insert('insert into `lead_products` (`product_id`, `lead_id`, `status`) values (?, ?, ?) on duplicate key update '.$keyUpdate,$listInsert2);
							} catch (ModelNotFoundException $exception) {
								return "";
							} */
							$Saleorder = new Saleorder();   
							$Saleorder->partner_id = $partner_id;
							$Saleorder->sale_number = $saleorder_no;
							$Saleorder->branch_id = $brand_id;
							$Saleorder->customer_id =0;
							$Saleorder->lead_id=$lead_id;
							$Saleorder->date_ship =$date_ship;
							$Saleorder->date_exp=$date_exp;
							$Saleorder->shipping_term="Thu tiền mặt";
							$Saleorder->shipping_term_id=25;
							$Saleorder->sales_person_id=$per_id;
							$Saleorder->terms_and_conditions="";
							$Saleorder->status=1;
							$Saleorder->total=$price;
							$Saleorder->tax_amount=0;
							$Saleorder->grand_total=$price;
							$Saleorder->discount=0;
							$Saleorder->final_price=$price;
							$Saleorder->status_order="Đặt hàng chính thức";
							$Saleorder->status_client=$statusKhachHang;
							$Saleorder->user_id=$per_id;
							$Saleorder->save();
							$saleorder_id = $Saleorder->id;
							$SaleorderProduct = new SaleorderProduct();   
							$SaleorderProduct->saleorder_id=$saleorder_id;
							$SaleorderProduct->product_id=$product_id;
							$SaleorderProduct->product_name=$product_name;
							$SaleorderProduct->description=0;
							$SaleorderProduct->quantity=1;
							$SaleorderProduct->price=$price;
							$SaleorderProduct->taxes=0;
							$SaleorderProduct->sub_total=$price;
							$SaleorderProduct->save();
							
						}
					}
					/*
					}else{
						$lead_id=$countLead["id"];
						
						$statusKhachHang=CallActionStatus::where('partner_id',$partner_id)->where('title',$tinh_trang_khach_hang)->first();
						$status=0;
						if($statusKhachHang){
							$status=$statusKhachHang["id"];
						} 
						Lead::where('id',$lead_id)->update(array('sales_person_id'=>$per_id));
					}
					*/

					/*
					$customFieldsData=array();
					if($hinh_thuc_thanh_toan!=""){
						$customFieldsData[]=['field_id'=>3,'item'=>$lead_id,'field_value'=>$hinh_thuc_thanh_toan];
					}
					if($muc_tieu!=""){
						$customFieldsData[]=['field_id'=>2,'item'=>$lead_id,'field_value'=>$muc_tieu];
					
					}
					if($nhom_truong!=""){
						$customFieldsData[]=['field_id'=>1,'item'=>$lead_id,'field_value'=>$nhom_truong];
					}
					if($ngay_hen!=""){
							$ngay_henex=explode("/",$ngay_hen);
							if(count($ngay_henex)>=2){
								$ngay_hen=$ngay_henex[2].'-'.$ngay_henex[1].'-'.$ngay_henex[0];
							}
						$customFieldsData[]=['field_id'=>4,'item'=>$lead_id,'field_value'=>$ngay_hen];
					}
					if(count($customFieldsData)>0){
						CustomFieldsData::insert($customFieldsData);
					}*/
				}
				echo $fullname."<br/>";
				LeadsTemp::where('id', $listData["id"])->update(['convert_data'=>1]);
			}


		}
		//return redirect( "lead" );
	}

	public function postImportTempToMainLead( Request $request ) {
		$userData=$this->userRepository->getUser();
		$data = LeadsTemp::where('convert_data',0)->get();
		$birthday="";
		$group_id=10;
		if($data)
		{
			foreach($data as $listData)
			{
			LeadsTemp::where('id', $listData["id"])->update(['convert_data'=>1]);
				$insert_data=null;
				$fullname  = $listData['opportunity'];
				$phone   = str_replace(" ","",$listData['phone']);
				$email   = $listData['email'];
				$partner_id=$listData['partner_id'];
				$function=$listData['function'];
				$internal_notes=$listData['internal_notes'];
				$user_id=$listData['user_id'];
				$UTM_Source=$listData['UTM_Source'];
				$ngay_hen="";
				/*
				$ngay_hen=$listData['ngay_hen'];
				$date_ship=$listData['ngay_hen'];
				if(isset($ngay_hen) && $ngay_hen!=""){
					$listNgayHen=explode("/",$ngay_hen);
					if(count($listNgayHen)>0){
						$year=date("Y");
						$month=date("m");
						$day=date("d");
						if(isset($listNgayHen[2]) && $listNgayHen[2]!=""){
							$year=$listNgayHen[2];
						}
						if(isset($listNgayHen[1]) && $listNgayHen[1]!=""){
							$month=$listNgayHen[1];
						}
						if(isset($listNgayHen[0]) && $listNgayHen[0]!=""){
							$day=$listNgayHen[0];
						}
						$date_ship=$year."-".$month."-".$day;
					}
				} */
				$muc_tieu=$listData['muc_tieu'];
				$hinh_thuc_thanh_toan=$listData['hinh_thuc_thanh_toan'];
				$sales_name=$listData['sales_name'];
				$congty=$listData['cong_type'];
				$ngaysinh=$listData['ngay_sinh'];
				$status=$listData['status'];
				$cong_ty=$listData['cong_ty'];
				$brand_id=$listData['brand_id'];
				$product_name=$listData['product_name'];
				$product_id=$listData['product_id'];
				$tinh_trang_khach_hang=$listData['tinh_trang_khach_hang'];;
				$nhom_truong=$listData['nhom_truong'];
				$utm_source=$listData['UTM_Source'];
				$price=$listData['price'];
				$per_id=$listData["sales_person_id"];
				if($per_id==""){
					$per_id=116;
				}

				$start=$listData["bat_dau"];

				$priority    = 1;
				$utm_campaign   = "";
				$partner_id  = $partner_id;
				if(substr($phone,0,1)!="0"){
					$phone="0".$phone;
				}
				if($phone!="" || $email!="" || $fullname!=""){
					$lead = new Lead;
					if($phone!=0 || $email!=""){
						$countLead=Lead::where('partner_id',$partner_id)
						->where(function ($query1)  use ($phone, $email){
							
									$query1->where('phone', $phone);
									$query1->orWhere('email',$email);
						})
						->first();	
					}else{
						$countLead="";
					}
					
					if($countLead=="" || $countLead==null){
						/*
						$lead->opportunity = $fullname;
						$lead->partner_id = $partner_id;
						$lead->email = $email;

						
						$birthday=$ngaysinh;
						if($ngaysinh!=""){
							$ngaysinhex=explode("/",$ngaysinh);
							if(count($ngaysinhex)>=2){
								$birthday=$ngaysinhex[2].'-'.$ngaysinhex[1].'-'.$ngaysinhex[0];
							}
						} 
						//$lead->birth_day=$birthday;
						
						$lead->phone =  $phone;
						$lead->function=$utm_source;
						$lead->cookie_id="";
						$lead->status=$status;
						$statusKhachHang=$status;
						
						$statusKhachHang=CallActionStatus::where('partner_id',$partner_id)->where('title',$tinh_trang_khach_hang)->first();
						$status=0;
						if($statusKhachHang){
							$status=$statusKhachHang["id"];
						} 

						$lead->internal_notes=$internal_notes;
						$lead->contact_name =$fullname;
						//$lead->client_name=$cong_ty;
						$lead->user_id =$userData->id;
						
						$lead->tags ="";
						$lead->sales_team_id =0;
						$lead->product_id =$product_id;
						$lead->product_name =$product_name;
						$lead->brand_id =$brand_id;
						$lead->UTM_Source =$utm_source;
						$lead->UTM_Campaign=$utm_campaign;*/
						/*
						$sales=User::where('partner_id',$partner_id)->where('nick_name',$sales_name)->first();
						if($sales){
							$lead->sales_person_id =$sales["id"];
							$per_id=$sales["id"];
						}else{
							$lead->sales_person_id =$userData->id;
							$per_id=$userData->id;

						} 
						$lead->UTM_Medium="";
						$lead->UTM_Term="";
						$lead->UTM_Content="";
						$lead->URL="";
						$lead->PID="";
						$lead->PSID="";
						$lead->GCLID="";
						$lead->FBCLID="";
						$lead->token="";
						$lead->save();
						$lead_id=$lead->id;*/
						DB::insert('insert into leads(opportunity, status, function, partner_id, email, phone, UTM_Source) values ("'.$fullname.'", "'.$status.'", "'.$utm_source.'", "'.$partner_id.'", "'.$email.'", "'.$phone.'", "'.$utm_source.'") on duplicate key update phone="'.$phone.'", email="'.$email.'", partner_id="'.$partner_id.'"');
						echo $lead_id;
					
						/*

						$saleorder = $this->salesOrderRepository->getAll()->withDeleteList()->get()->count();
						if($saleorder == 0){
							$total_fields = 0;
						}else{
							$total_fields = $this->salesOrderRepository->getAll()->withDeleteList()->get()->last()->id;
						}
						$start_number = Settings::get('sales_start_number');
						$saleorder_no = "FCRM" . (is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields : 0) + 1);
						$arrayupdate["sales_person_id"]=$per_id;
						if($start==null || $start==""){
							$date_ship=date("Y-m-d");
						}else{
							$date_ship=$start;
						}
						$arrayupdate["date_ship"]=$date_ship;
						if($date_ship!=""){
							$date_exp=date("Y-m-d",strtotime('+30 days',strtotime($date_ship)));
						}else{
							$date_exp=date("Y-m-d",strtotime('+30 days'));
						}
						
						$product_id=0;
						if($product_name!=""){
						$product_id=$this->productRepository->findByField('product_name',$product_name)->pluck('id')->first();
						} */
						if($product_id!="" && $product_id!=0){
						
							$listInsert2=array();
							$keyUpdate=array();
							$listInsert2=[$product_id, $lead_id, 1];
							$keyUpdate="product_id='".$product_id."', lead_id=".$lead_id;
							try {
								$DataList=$listInsert2;
								//for($i=0;$i<count($DataList);$i++){
									DB::insert('insert into `lead_products` (`product_id`, `lead_id`, `status`) values (?, ?, ?) on duplicate key update '.$keyUpdate,$listInsert2);
								//}  
								//DB::table('contacts')->updateOrInsert($listInsert, $attributes);
							} catch (ModelNotFoundException $exception) {
								return "";
							}
	
							$Saleorder = new Saleorder();   
							$Saleorder->partner_id = $partner_id;
							$Saleorder->sale_number = $saleorder_no;
							$Saleorder->branch_id = $brand_id;
							$Saleorder->customer_id =0;
							$Saleorder->lead_id=$lead_id;
							$Saleorder->date_ship =$date_ship;
							$Saleorder->date_exp=$date_exp;
							$Saleorder->shipping_term="Thu tiền mặt";
							$Saleorder->shipping_term_id=25;
							$Saleorder->sales_person_id=$per_id;
							$Saleorder->terms_and_conditions="";
							$Saleorder->status=1;
							$Saleorder->total=$price;
							$Saleorder->tax_amount=0;
							$Saleorder->grand_total=$price;
							$Saleorder->discount=0;
							$Saleorder->final_price=$price;
							$Saleorder->status_order="Đặt hàng chính thức";
							$Saleorder->status_client=$statusKhachHang;
							$Saleorder->user_id=$per_id;
							$Saleorder->save();
							$saleorder_id = $Saleorder->id;
							$SaleorderProduct = new SaleorderProduct();   
							$SaleorderProduct->saleorder_id=$saleorder_id;
							$SaleorderProduct->product_id=$product_id;
							$SaleorderProduct->product_name=$product_name;
							$SaleorderProduct->description=0;
							$SaleorderProduct->quantity=1;
							$SaleorderProduct->price=$price;
							$SaleorderProduct->taxes=0;
							$SaleorderProduct->sub_total=$price;
							$SaleorderProduct->save();
							
						}  

 

					}else{
						$lead_id=$countLead["id"];
						/*
						$statusKhachHang=CallActionStatus::where('partner_id',$partner_id)->where('title',$tinh_trang_khach_hang)->first();
						$status=0;
						if($statusKhachHang){
							$status=$statusKhachHang["id"];
						} */
						Lead::where('id',$lead_id)->update(array('sales_person_id'=>$per_id));
					}
					

					/*
					$customFieldsData=array();
					if($hinh_thuc_thanh_toan!=""){
						$customFieldsData[]=['field_id'=>3,'item'=>$lead_id,'field_value'=>$hinh_thuc_thanh_toan];
					}
					if($muc_tieu!=""){
						$customFieldsData[]=['field_id'=>2,'item'=>$lead_id,'field_value'=>$muc_tieu];
					
					}
					if($nhom_truong!=""){
						$customFieldsData[]=['field_id'=>1,'item'=>$lead_id,'field_value'=>$nhom_truong];
					}
					if($ngay_hen!=""){
							$ngay_henex=explode("/",$ngay_hen);
							if(count($ngay_henex)>=2){
								$ngay_hen=$ngay_henex[2].'-'.$ngay_henex[1].'-'.$ngay_henex[0];
							}
						$customFieldsData[]=['field_id'=>4,'item'=>$lead_id,'field_value'=>$ngay_hen];
					}
					if(count($customFieldsData)>0){
						CustomFieldsData::insert($customFieldsData);
					}*/
				}
			}

		}
		return redirect( "lead" );
	}

	//Add contact

	public function postAjaxStore( LeadImportRequest $request ) {
		$this->leadRepository->store( $request->except( 'created', 'errors', 'selected' ) );

		return response()->json( [], 200 );
	}
   
	public function importExcelData( Request $request ) {
		$this->validate( $request, [
			'file' => 'required|mimes:xlsx,xls,csv|max:5000',
		] );

		$reader = $this->excelRepository->load( $request->file( 'file' ) );
	}
	public function leadWin($lead){
		$leaddata = $this->leadRepository->find($lead);
		if($leaddata){
			$product=$this->productRepository->findByField('id',$leaddata->product_id)->pluck('sale_price')->first();
			if($product){
				$sale_price=$product;
			}else{
				$sale_price=0;
			}
			$expected_closing=date("Y-m-d",strtotime("+30 days"));
			if($leaddata->next_follow_up!="" && $leaddata->next_follow_up!=0){

				$expected_closing=date($leaddata->next_follow_up,strtotime("+30 days"));
			}
			$data=array(
				'opportunity'=>$leaddata->opportunity,
				'stages'=>'Won',
				'expected_revenue'=>$sale_price,
				'probability'=>'100',
				'company_name'=>$leaddata->company_name,
				'customer_id'=>$leaddata->customer_id,
				'next_action'=>$leaddata->next_follow_up,
				'expected_closing'=>$expected_closing,
				'lead_id'=>$leaddata->id,
				'email'=>$leaddata->email,
				'sales_team_id'=>$leaddata->sales_team_id,
				'sales_person_id'=>$leaddata->sales_person_id,
				'phone'=>$leaddata->phone,
				'user_id'=>$leaddata->user_id,
				'quantity'=>1
			);
			$id=Opportunity::insertGetId($data);
			$dataUpdate=array(
				'status'=>6
			);
			$leaddata->update($dataUpdate);
			return redirect( "opportunity/".$id."/edit#/" );
		}
	}
	public function leadLost($lead){
		$leaddata = $this->leadRepository->find($lead);
		if($leaddata){
			$product=$this->productRepository->findByField('id',$leaddata->product_id)->pluck('sale_price')->first();
			if($product){
				$sale_price=$product;
			}else{
				$sale_price=0;
			}
			if($leaddata->next_follow_up!=""){
				$expected_closing=date($leaddata->next_follow_up,strtotime("+30 days"));
			}
			$data=array(
				'opportunity'=>$leaddata->opportunity,
				'stages'=>'Lost',
				'expected_revenue'=>$sale_price,
				'probability'=>'0',
				'company_name'=>$leaddata->company_name,
				'customer_id'=>$leaddata->customer_id,
				'next_action'=>$leaddata->next_follow_up,
				'expected_closing'=>$expected_closing,
				'lead_id'=>$leaddata->id,
				'email'=>$leaddata->email,
				'sales_team_id'=>$leaddata->sales_team_id,
				'sales_person_id'=>$leaddata->sales_person_id,
				'phone'=>$leaddata->phone,
				'user_id'=>$leaddata->user_id,
				'quantity'=>0
			);
			$id=Opportunity::insertGetId($data);
			$dataUpdate=array(
				'status'=>12
			);
			$leaddata->update($dataUpdate);
			return redirect( "opportunity/".$id."/lost#/" );
		}

	}

	public function leadExport(Request $request){
		$title = trans( 'lead.leads' );
		$dateFormat = config('settings.date_format');
		$date  = addslashes($request->starting_date);
		$sales_id = addslashes($request->sales_id) ;
		$product_id = addslashes($request->product_id);
		$status  = addslashes($request->status) ;
		$UTM_Source = addslashes($request->UTM_Source);
		$tags = addslashes($request->tags);
		$keyword = addslashes($request->keyword);
		$group_id = addslashes($request->group_id);
		$type_status = addslashes($request->type_status);
		$function  = addslashes($request->function);
		$locked  = addslashes($request->locked);
		$brand_id  = addslashes($request->brand_id);

		if($date!=""){
			$dateArray=explode("-",trim($date));
			$starting_date=$this->convertDate(trim($dateArray[0]));
			$ending_date=$this->convertDate(trim($dateArray[1]));
			$starting_date=date("Y-m-d 00:01:00", strtotime($starting_date));
			$ending_date=date("Y-m-d 23:59:00", strtotime($ending_date));
			
			$date_select=$date;
		}else{
			$starting_date=date("Y-m-d",strtotime('today - 90 days'));
			$ending_date=date("Y-m-d",strtotime('today +1 days'));
			$date_select=date("m/d/Y",strtotime('today - 90 days'))." - ".date("m/d/Y");
		}

		$userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
		$listUser=$this->userRepository->getAllStaffOfUser(0);

		if(!isset($locked) || $locked==""){
			$locked=0;
		} 
		$listStatusSearch=$status;
		$leadsQuery = Lead::select('leads.*', 'call_action_status.title as statustitle', 'call_action_status.type', 'users.first_name', 'users.last_name', 'products.product_name', 'call_action_status.icons', 'group_client.name as group_name')
			->leftJoin('call_action_status','call_action_status.id','=','leads.status')
			->leftJoin('users','users.id','=','leads.sales_person_id')
			->leftJoin('products','products.id','=','leads.product_id')
			->leftJoin('group_client','group_client.id','=','leads.group_id')
			->where('leads.locked',$locked)
			->where('leads.partner_id',$partner_id)
			->where(function ($query)  use ($starting_date, $ending_date, $sales_id, $listStatusSearch, $function,  $keyword, $product_id, $tags, $group_id, $type_status, $UTM_Source, $brand_id){
				if($starting_date!=""){
					$query->where('leads.created_at','>=',$starting_date);
				}
				if($ending_date!=""){
					$query->where('leads.created_at','<=',$ending_date);
				}
				if($sales_id!="" && $sales_id!="0"){
					$query->where('leads.sales_person_id','=',$sales_id);
					$query->whereOr('leads.user_id','=',$sales_id);
				}
				if($product_id!="" && $product_id!="0"){
					$query->where('leads.product_id','=',$product_id);
				}
				if($UTM_Source!="" && $UTM_Source!="0"){
					$query->where('leads.UTM_Source','=',$UTM_Source);
				}
				if($tags!="" && $tags!="0"){
					$query->where('leads.tags','=',$tags);
				}
				if($group_id!="" && $group_id!="0"){
					$query->where('leads.group_id','=',$group_id);
				}
				if($function!="" && $function!="0"){
					$query->where('leads.function','=',$function);
				}
				if($listStatusSearch!="" && $listStatusSearch!="0" && $listStatusSearch!="1"){
				//	$query->where('leads.status',$listStatusSearch);
					$query->whereIn('leads.status',explode(",",$listStatusSearch));
				}
				if($type_status!=""){
					$query->whereIn('call_action_status.type',explode(",",$type_status));
				}
				if($brand_id!="" && $brand_id!="0"){
					$query->where('leads.brand_id','=',$brand_id);
				}
				
			})->where(function ($query)  use ($listUser, $partner_id, $sales_id){

					if($sales_id!="" && $sales_id!="0"){
						$query->where(function ($query1)  use ($sales_id){
							$query1->where('leads.sales_person_id','=',$sales_id);
							$query1->whereOr('leads.user_id','=',$sales_id);
						});
					}
			})->where(function ($query)  use ($starting_date, $ending_date, $status){
				if($status==0){
					if($starting_date!=""){
						$query->where('leads.created_at','>=',$starting_date);
					}
					if($ending_date!=""){
						$query->where('leads.created_at','<=',$ending_date);
					}
				}elseif($status==1){
						$query->where(function ($query1){
							$query1->where('leads.sales_person_id','=',0);
							$query1->whereOr('leads.user_id','=',0);
						});
						if($starting_date!=""){
							$query->where('leads.created_at','>=',$starting_date);
							$query->whereOr('leads.updated_at','>=',$starting_date);
						}
						if($ending_date!=""){
							$query->where('leads.created_at','<=',$ending_date);
							$query->whereOr('leads.updated_at','<=',$ending_date);
						}
				}else{
					if($starting_date!=""){
						$query->where('leads.created_at','>=',$starting_date);
						$query->whereOr('leads.updated_at','>=',$starting_date);
					}
					if($ending_date!=""){
						$query->where('leads.created_at','<=',$ending_date);
						$query->whereOr('leads.updated_at','<=',$ending_date);
					}
				}
			})->distinct()
			->groupBy('leads.id')
			->orderBy('leads.id', 'DESC')->get()->toArray();
			
			$customer_data=$leadsQuery;
			$customer_array[] = array('ID', 'Lead Name', 'Ngày tạo', 'Email', 'Phone', 'Sales chăm sóc', 'Tags', 'Nhóm');
			$i=0;
			foreach($customer_data as $customer)
			{
				$i++;
				$customer_array[] = array(
					'ID'  => $customer["lead_code"],
					'Lead Name'   => $customer["opportunity"],
					'Ngày tạo'   => date($dateFormat,strtotime($customer["created_at"])),
					'Email'    => $customer["email"],
					'Phone'  => $customer["phone"],
					'Sales'   => $customer["first_name"]." ".$customer["last_name"],
				//	'Câu lạc bộ'   => Option::select('title')->where('value',$customer["function"])->where('partner_id',$customer["partner_id"])->orderBy('id','desc')->get()->map( function ( $dataOption){ return ['title' => $dataOption->title];})->pluck( 'title')->first(),
					'Tags'   => $customer["tags"],
					'Nhóm'   => $customer["group_name"],
			//		'Tình trạng'   => $customer["statustitle"],
				//	'Chi nhánh'   => $customer["brandname"],
				//	'Loại cuộc gọi'=>($customer["type_call"]==2)?"NV gọi":"KH gọi",
					//'Chứng minh nhân dân'   => CustomFieldsData::select('field_value')->where('item',$customer["id"])->where('field_id',9)->get()->map( function ( $dataOption){ return ['field_value' => $dataOption->field_value];})->pluck( 'field_value')->first(),
				//	'Ngày cấp'   => CustomFieldsData::select('field_value')->where('item',$customer["id"])->where('field_id',10)->get()->map( function ( $dataOption){ return ['field_value' => $dataOption->field_value];})->pluck( 'field_value')->first(),
				//	'Ngày mua xe'   => CustomFieldsData::select('field_value')->where('item',$customer["id"])->where('field_id',11)->get()->map( function ( $dataOption){ return ['field_value' => $dataOption->field_value];})->pluck( 'field_value')->first(),
				//	'Biển số xe'   => CustomFieldsData::select('field_value')->where('item',$customer["id"])->where('field_id',12)->get()->map( function ( $dataOption){ return ['field_value' => $dataOption->field_value];})->pluck('field_value')->first(),
				//	'Profile facebook'   => $customer["URL"],
				//	'Địa chỉ'   => $customer["address"],
				//	'Tỉnh/TP'   => $customer["city_name"],
				);
			}
			/*
			Excel::create('Danh sach Lead', function($excel) use ($customer_array){
				$excel->setTitle('Danh sách Lead');
				$excel->sheet('Danh sach Lead', function($sheet) use ($customer_array){
				$sheet->fromArray($customer_array, null, 'A1', false, false);
			});
			})->download('xls'); */

			Excel::create('Danh sach Lead', function($excel) use ($customer_array) {

				$excel->sheet("Danh sach Lead", function($sheet) use ($customer_array){
					$sheet->fromArray($customer_array, null, 'A1', false, false);			
				});
			
			})->download('xlsx');
		
	}
	public static function checkphone($phone)
	{
		$phone1=explode("-",$phone);
		if(count($phone1)>0){
			$phone=trim($phone1[0]);
		}
		$phone=str_replace(array("+840","+84"," "),array("0","0",""),$phone);
		if(substr($phone,0,3)=="840"){
			$phone=substr($phone,3,strlen($phone)-3);
		}
		if(substr($phone,0,2)=="84"){
			$phone=substr($phone,2,strlen($phone)-2);
		}
		if(substr($phone,0,2)=="00"){
			$phone=trim(substr($phone,1,strlen($phone)-1));
		}
		if((int)substr($phone,0,1)>0){
			$phone="0".$phone;
		}
		// Allow +, - and . in phone number
		$filtered_phone_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
		// Remove "-" from number
		$phone_to_check = str_replace(array("-"," "), array("",""), $filtered_phone_number);
		// Check the lenght of number
		// This can be customized if you want phone number from a specific country
		if (strlen($phone_to_check) < 10 || strlen($phone_to_check) > 12) {
			return false;
		} else {
			return $phone_to_check;
		}
	}

	public function history(Request $request){
		$lead_id =$request->lead_id;
		$logData=Logs::where('lead_id',$lead_id)->orderBy("id", "desc")->paginate(50)->appends(request()->query());
        $logshow=$logData->map( function ( $logs){
                return [
					'id' => $logs->id,
					'date' => date("d/m/Y H:i:s", strtotime($logs->created_at)),
					"description" => $logs->logs,
					'logs_description'=>$logs->logs_description,
					'photos'=>$logs->photos,

                ];
            }
        );
        return $logshow;
    }
	
	//Add Call logs
	public function addCallLog(Request $request)
	{
		$user=$this->userRepository->getUser();
		$data = array(
		   'user_id' => $user->id,
		   'lead_id'=>$request->input('lead_id_log'),
		   'date_call'=> date("Y-m-d H:i:s"),
	   );
	   $rules = array(
		   'user_id' => 'required',
		   'lead_id' => 'required',
	   );
	   $logtext=$request->input('logs_text');
	   $lead_id=$request->input('lead_id_log');
	   $logs_description=$request->input('logs_description');
	   $client_interactive=$request->input('client_interactive');
	   $source_id=$request->input('source_id');
	   $tags=$request->input('tags');

	   
	   if ($logtext && $lead_id) {
			$leadDetail = Lead::where("id",$lead_id)->first();
			if($leadDetail ){
				//$tagsLead=$leadDetail["tags"];
				// Add to log
				//if($request->input('tags')!=""){
				//	$tagsLead=$leadDetail["tags"].", ".$tags;
				//}
				$listPhoto=array();
				$photo="";
				$leadDetail->date_last_update=date("Y-m-d H:i:s");
				$leadDetail->customer_care_id=$user->id;
				$leadDetail->sales_person_id=$user->id;
				$leadDetail->update();
				if(isset($_FILES["file"]["name"]) && $_FILES["file"]["name"]!=""){
					$file_names = $_FILES["file"]["name"];
					for ($i = 0; $i < count($file_names); $i++) {
						$file_name=$file_names[$i];
						//$extension = end(explode(".",$file_name));
						$file_url=str_replace(" ","-",$file_name);
						@move_uploaded_file($_FILES["file"]["tmp_name"][$i], 'upload/' . $file_url);
						$listPhoto[]=$file_url;
					} 
				}
				
				if(count($listPhoto)>0){
					$photo=implode("|",$listPhoto);
				}
				$dataLogs = array(
					'user_id' => $user->id,
					'logs'=>$logtext,
					'logs_description'=>$logs_description,
					'client_interactive'=>$client_interactive,
					'created_at'=> date("Y-m-d H:i:s"),
					'lead_id'=>$lead_id,
					'photos'=>$photo,
					'source_id'=>$source_id,
					'tags'=>$tags
				 );
				Logs::insert($dataLogs);
				// end add
				return response()->json(['success' => 'success'], 200);
			}else{
				return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
			}
		  
	   } else {
		   return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	   }
	}
	public function updateLeadStatus(Request $request){
		$user=$this->userRepository->getUser();
		$lead_id=$request->lead_id;
		$status=$request->status_from;
		$statusTo=$request->status_to;

		if($lead_id!="" && $status!="" && $statusTo!=""){
			$leadDetail = Lead::where("id",$lead_id)->first();
			Lead::where('id', $lead_id)->where('partner_id',$user->partner_id)->update(['status'=>$statusTo]);
			$statusFrom=CallActionStatus::where('id',$status)->first();
			$statusTo=CallActionStatus::where('id',$statusTo)->first();
			$dataLogs = array(
				'user_id' => $user->id,
				'logs'=>"Chuyển tình trạng khách hàng từ (".$statusFrom["title"].") đến (".$statusTo["title"].")",
				'logs_description'=>"",
				'created_at'=> date("Y-m-d H:i:s"),
				'lead_id'=>$lead_id,
			 );
			Logs::insert($dataLogs);
			return response()->json(['success' => 'success'], 200);
		}else{
			return response()->json(['success' => 'NoSuccess'], 200);
		}
		return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	}
	
	// Sản phẩm đã mua
	public function historyProductOfLead(Request $request){
		$lead_id  = $request->lead_id;
		$user=$this->userRepository->getUser();
		$productData=SaleorderProduct::join('products','sales_order_products.product_id','=','products.id')->join('sales_orders','sales_orders.id','=','sales_order_products.saleorder_id')->select('products.*', 'sales_orders.created_at as orderdate', DB::raw('sum(sales_order_products.quantity) as total_buy'))->where('sales_orders.lead_id',$lead_id)->where('sales_orders.partner_id',$user->partner_id)->groupBy('products.id')->orderBy("sales_orders.id", "desc")->paginate(50)->appends(request()->query());
        $productshow=$productData->map( function ( $products){
                return [
					'id' => $products->id,
					'name' => $products->product_name,
					'number_buy'=>$products->total_buy,
					'date_buy' => date("d/m/Y H:i:s", strtotime($products->orderdate)),
                ];
            }
        );
        return $productshow;
	}
	// Sản phẩm đang quan tâm
	public function interestProductOfLead(Request $request){
		$lead_id  = $request->lead_id;
		$user=$this->userRepository->getUser();
		$productData=LeadProduct::join('products','lead_products.product_id','=','products.id')->select('products.*', 'lead_products.created_at as interest') ->where('lead_products.lead_id',$lead_id)->where('products.partner_id',$user->partner_id)->groupBy('products.id')->orderBy("lead_products.created_at", "desc")->paginate(50)->appends(request()->query());
        $productshow=$productData->map( function ( $products){
                return [
					'id' => $products->id,
					'name' => $products->product_name,
					'number_hand_store'=>$products->quantity_on_hand,
					'date_interate' => date("d/m/Y H:i:s", strtotime($products->interest)),
                ];
            }
        );
        return $productshow;
	}
	
	// Thêm sản phẩm quan tâm
	public function addProductInterate(Request $request){
		$lead_id  = $request->lead_id;
		$product_id  = $request->product_id;
		if($lead_id!="" && $product_id!="" && $product_id!=0 && $lead_id!=0){
			$checkProductInterRate=LeadProduct::where('lead_id',$lead_id)->where('product_id',$product_id)->first();
			if(!$checkProductInterRate){
				$leadProduct=new LeadProduct;
				$leadProduct->lead_id=$lead_id;
				$leadProduct->product_id=$product_id;
				$leadProduct->status=1;
				$leadProduct->save();
				exit();
			}
			exit();
			
		}else{
			exit();
		}
		
    }
	///
	public function searchLead(Request $request){
		$keyword=$request->keyword;
		$userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
		$leadsList=null;
		if($keyword!=""){
			$leadsQuery = Lead::select('leads.*')->join('call_action_status','call_action_status.id','=','leads.status')
			->where('leads.partner_id',$partner_id)
			->where('leads.locked',0)
			->whereIn('call_action_status.type',array(2,4))
			->whereNotNull('leads.sales_person_id')
			->where(function ($query)  use ($keyword){
				if($keyword!=""){
					$query->where(function ($query1)  use ($keyword){
						$query1->where('leads.opportunity', 'LIKE', "%{$keyword}%");
						$query1->orWhere('leads.email','LIKE', "%{$keyword}%");
						$query1->orWhere('leads.phone', 'LIKE', "%{$keyword}%");
					});
				}
			})
			->distinct()
			->groupBy('leads.id')
			->orderBy('leads.updated_at', 'DESC');
			$leadsPage=$leadsQuery->paginate(100)->appends(request()->query());
			$leadsList=$leadsPage->map( function ( $lead){
				return [
					'id'           => $lead->id,
					'opportunity' => $lead->opportunity,
				];
				}
			);
		}
		return response()->json(['lead_data'=>$leadsList], 200 );
	}
	public function ajaxLeadList(Request $request){
		$userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
		$leadsList=null;
		$lead_id=$request->lead_id;
		$leadsQuery = Lead::select('leads.*')
		->where('leads.partner_id',$partner_id)
		->where('leads.locked',0)
		->whereNotNull('leads.sales_person_id')
		->where(function ($query)  use ($lead_id){
			if($lead_id!=""){
				$query->where('leads.id', $lead_id);
			}
		})
		->groupBy('leads.id')
		->orderBy('leads.updated_at', 'DESC');

		$leadsPage=$leadsQuery->paginate(100)->appends(request()->query());
		$leadsList=$leadsPage->map( function ( $lead){
			return [
				'value' => $lead->id,
				'title' => $lead->opportunity,
			];
			}
		)->pluck( 'title', 'value' )->prepend(trans('lead.select_lead'), '');
		
        return $leadsList;
	}
	public function lockeduser(Request $request){
		$userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
		$lead_id=$request->lead_id;
		$result="";
		if($lead_id>0){
			$result=Lead::where('id',$lead_id)->where('partner_id',$partner_id)->update(['locked' => 1]);
		}
		return 1;
	}

	//Log
	public function historypageaccess(Request $request,Datatables $datatables)
    {
		$cookie=$request->cookie_id;
		$psid=$request->psid;
		if($cookie!="" || $psid!=""){
			$logData=Cookie::select('cookie.*')->leftJoin('cookie_map','cookie_map.cookie','=', 'cookie.cookie_site')
			->where(function ($query)  use ($psid, $cookie){
				if($psid!=""){
					$query->where('cookie_map.psid', $psid);
				}elseif($cookie!=""){
					$query->where('cookie.cookie_site', $cookie);
				}
			})
			->groupBY('cookie.url')->orderBy("cookie.id", "desc")->paginate(50)->appends(request()->query());
			$logshow=$logData->map( function ( $logs){
					return [
						'id' => $logs->id,
						'date' => $logs->create_date,
						"url_access" => $logs->url,
						'url_refer'=>$logs->url_refer,
						'browser'=>$logs->photos,
						'user_agent'=>$logs->user_agent,
						'ip'=>$logs->ip_add
					];
				}
			);
			return $datatables->collection($logshow)
			->addColumn( 'url', '@if($url_access) <a href="{{$url_access}}" target="_blank">{{$url_access}}</a> @endif')
			->rawColumns(['url'])->make();
		}else{
			return $datatables->collection([])->make();
		}
		
            
		
	}
	
	public function historyChat(Request $request){
		$psid =$request->psid;
		$timelast=$request->lasttime;
		$timeload=$request->timeload;

		$lastIdChat=$request->lastIdChat;
		$datenow=date("Y-m-d H:i:s", strtotime("-30 day"));
		$limit=50;
		if($timeload>0){
			$limit=5;
		}
		$key=md5($psid.$lastIdChat.$timeload.$limit.$datenow);
		$pageDataCache = cache('pageDataCache'.$key);
		if(isset($pageDataCache) && $pageDataCache!=""){
			$logData=$pageDataCache;
		}else{
			$logData=Chatbox::select('id', 'sender_id', 'receive_id', 'messenger', 'title', 'page_id', 'extention', 'timechat', 'type_attch', 'read', 'file_link', 'date_create')
			->where('date_create','>=', $datenow)
			->where(function ($query) use ($psid) {
				$query->where('sender_id', '=', $psid)->orWhere('receive_id', '=', $psid);
			})->where(function ($query) use ($lastIdChat) {
				if($lastIdChat!="" && $lastIdChat>0){
					$query->where('id', '>', $lastIdChat);
				}
			})
			->groupBy('sender_id','receive_id','mess_encode','date_create')
			->orderBy('read', 'asc')
			->orderBy('date_create', 'desc')
			->offset(0)->limit($limit)->get();
			Cache::put('pageDataCache'.$key, $logData, now()->addMinutes(1));
		}

		$logshow=array();
		$listIdUpdate=array();
		$chatnow=0;
		if($logData){
			foreach($logData as $key=>$values){
				$messenger="";
				$pre=$logData->get($key-1);
				$next=$logData->get($key+1);
				$idpre="";
				if($pre){
					$idpre=$pre["id"];
				}
				$idnext="";
				if($next){
					$idnext=$next["id"];
				}
				$listIdUpdate[]=$values["id"];
				$phone=$this->checkphone($values["extention"]); //str_replace(array("+84", "84"),array("0","0"),$values["extention"]);
				if(isset($values["title"]) && $values["title"]!=""){
					$values["messenger"]=$values["title"];
				}
				/*
				if(substr($phone,2)=='00'){
					$phone=substr($phone,1,strlen($phone)-1);
				} */
				$email="";
				if($this->validate_email($values["messenger"])!==false){
					$email=$this->validate_email($values["messenger"]);
				}
				
				if($values["messenger"]!="Attachments"){
					$messenger="<div class='linechatcontent'>".$this->turnUrlIntoHyperlink(nl2br($values["messenger"]))."</div>";
				}
				if($values["type_attch"]!="" && $values["type_attch"]!=null && $values["type_attch"]=="image"){

					$fileList=explode("<|>",$values["file_link"]);
					$listPhoto=[];
					$photo="";
					if(count($fileList)>0){
						for($j=0;$j<count($fileList);$j++){
							$listPhoto[]="<a href='".$fileList[$j]."' target='_blank'><img src='".$fileList[$j]."' style=\"height:180px!important; float:left; margin-right:5px; margin-bottom:5px\" /></a>";
						}
						if(count($listPhoto)>0){
							$photo=implode("",$listPhoto);
						}
					}else{
						$photo="<a href='".$values["file_link"]."' target='_blank'><img src='".$values["file_link"]."' style=\"max-width:350px!important\" /></a>";
					}
					$messenger.="<div class='linechatcontent'>".$photo."</div>";
				}
				if($values["type_attch"]!="" && $values["type_attch"]!=null && $values["type_attch"]=="file"){
					$messenger.="<div class='linechatcontent'><a href='".$values["file_link"]."' target='_blank'>Download file</a></div>"; 
				}
				if($values["type_attch"]!="" && $values["type_attch"]!=null && $values["type_attch"]=="video"){
					$messenger.="<div class='linechatcontent'><video width=\"320\" height=\"240\" controls>";
					$messenger.="<source src=\"".$values["file_link"]."\" type=\"video/mp4\">";
					$messenger.="<source src=\"".$values["file_link"]."\" type=\"video/ogg\">";
					$messenger.="Your browser does not support the video tag.";
					$messenger.="</video>";
					$messenger.="<a href='".$values["file_link"]."' target='_blank'>Xem Link</a></div>";
				}
				
				$timenow=time();
				if($timenow-$values["timechat"]<=86040 && $values["sender_id"]!=$values["page_id"]){
					$chatnow=1;
				}
				$logshow[]=array('id' => $values["id"], 'idpre' => $idpre, 'idnext' => $idnext, 'sender_id' => $values["sender_id"], 'receive_id' => $values["receive_id"], 'date' => date("d/m/Y H:i:s", strtotime($values["date_create"])), "messenger" =>$messenger, 'title'=>$values["title"], 'extention'=>$phone, 'lasttime'=>$values["timechat"], 'read'=>$values["read"], "email"=>$email, "timechat"=>$values["timechat"], "chatnow"=>$chatnow);
			}
		//	Lead::where('psid',$psid)->update(['new_inbox'=>1]);
			if(count($listIdUpdate)>0){
				Chatbox::whereIn('id',$listIdUpdate)->where('read',0)->update(['read'=>1]);
			} 
		}
		
        return $logshow;
	}
	 
	public function turnUrlIntoHyperlink($string){
		if($string!=""){
			$reg_exUrl = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";
		
			// Check if there is a url in the text
			if(preg_match_all($reg_exUrl, $string, $url)) {
		
				// Loop through all matches
				foreach($url[0] as $newLinks){
					if(strstr( $newLinks, ":" ) === false){
						$link = 'http://'.$newLinks;
					}else{
						$link = $newLinks;
					}
		
					// Create Search and Replace strings
					$search  = $newLinks;
					$replace = '<a href="'.$link.'" title="'.$newLinks.'" target="_blank">'.$link.'</a>';
					$string = str_replace($search, $replace, $string);
				}
			}
		
			//Return result
			return $string;
		}else{
			return "";
		}
	}
	public function updateclientauto(Request $request){
		$user=$this->userRepository->getUser();
		$lead_id=$request->lead_id;
		$id=$request->id;
		$title=$request->title;
		$function=$request->function;
		if($lead_id!="" && $id!="" && $function!=""){
			$leadDetail = Lead::where("id",$lead_id)->first();
			Lead::where('id', $lead_id)->where('partner_id',$user->partner_id)->update([$function=>$id]);
			$dataLogs = array(
				'user_id' => $user->id,
				'logs'=>"Chuyển tình trạng khách hàng đên (".$title.")",
				'logs_description'=>"",
				'created_at'=> date("Y-m-d H:i:s"),
				'lead_id'=>$lead_id,
			 );
			Logs::insert($dataLogs);

			$mapStatus=array("lead_id"=>$lead_id, "partner_id"=>$user->partner_id, "status_id"=>$id, "user_id"=>$user->id);
		//	var_dump($mapStatus);
		
			LeadStatus::updateOrCreate($mapStatus, ["last_update"=>time()]);

			return response()->json(['success' => 'success'], 200);
		}else{
			return response()->json(['success' => 'NoSuccess'], 200);
		}
		return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	}
	public function updatephonemain(Request $request)
	{
		$user=$this->userRepository->getUser();
		$lead_id=$request->lead_id;
		$phone=$request->phone;
		$partner_id=$request->partner_id;
		$user_id=$request->user_id;
		$data = array(
			'lead_id' => $lead_id,
			'phone'=>$phone,
			'partner_id'=>$partner_id,
		);
		$rules = array(
			'id' => 'required',
			'phone'=> 'required',
			'partner_id'=> 'required',
		);
		if ($lead_id!="" && $phone!="" && $user->id==$user_id) {
			$phone=str_replace(array("+84", "84"),array("0","0"),$phone);
			if(substr($phone,2)=='00'){
				$phone=substr($phone,1,strlen($phone)-1);
			}
			//$user = User::where("id",$user_id)->where('partner_id',$partner_id)->first();
			// Add to log
			//if($user){
				$leadPhone=Lead::where('phone', $phone)->where('partner_id',$partner_id)->first();
				if(isset($leadPhone) && $leadPhone!=""){
					return response()->json(['mess' => 'Số ĐT thoại '.$phone.' đang tồn tại trong KH/Lead Tên là: '.$leadPhone["opportunity"].' ID '.$leadPhone["id"], 'phone'=>$phone], 200);
				}
				$lead=Lead::where('id', $lead_id)->where('partner_id',$partner_id)->first();
				if($lead){
					Lead::where('id', $lead_id)->where('partner_id',$partner_id)->update(['phone'=>$phone]);
					$dataLogs = array(
						'user_id' => $user["id"],
						'logs'=>"Nhân viên ".$user["first_name"]." ".$user["last_name"].", chuyển số điện thoại KH",
						'phone'=>$phone,
						'created_at'=> date("Y-m-d H:i:s"),
					);
					Logs::insert($dataLogs);
					$phoneOld=$lead["phone"];
					if($phoneOld!=""){
						$Leadmap=Leadmap::where('lead_id', $lead["id"])->where('phone',$phoneOld)->first();
						if(!$Leadmap){
							$mapleadAdd=array("lead_id"=>$lead["id"], "phone"=>$phoneOld);
							Leadmap::insert($mapleadAdd);
						}
					}
					return response()->json(['mess' => 'Thay thế thành công', 'phone' => $phone], 200);
				}else{
					return response()->json(['mess' => 'Không tìm thấy KH', 'phone'=>$phone], 200);
				}
				
			//}
				
			//return response()->json(['mess' => 'Không tìm thấy user', 'phone'=>$phone], 200);
		}else{
			return response()->json(['error' => trans('dashboard.datafail')], 500);
		}
	}

	public function updateemail(Request $request)
	{
		$user=$this->userRepository->getUser();
		$lead_id=$request->lead_id;
		$email=$request->email;
		$partner_id=$request->partner_id;
		$user_id=$request->user_id;
		$data = array(
			'lead_id' => $lead_id,
			'email'=>$email,
			'partner_id'=>$partner_id,
		);
		$rules = array(
			'id' => 'required',
			'email'=> 'required',
			'partner_id'=> 'required',
		);
		if ($lead_id!="" && $email!="" && $user->id==$user_id) {
			
			if($this->validate_email($email)==false){
				return response()->json(['mess' => 'Email sai', 'email'=>$email], 200);
			}else{
				$user = User::where("id",$user_id)->where('partner_id',$partner_id)->first();
				// Add to log
				if($user){
					$lead=Lead::where('id', $lead_id)->where('partner_id',$partner_id)->first();
					if($lead){
						Lead::where('id', $lead_id)->where('partner_id',$partner_id)->update(['email'=>$email]);
						$dataLogs = array(
							'user_id' => $user["id"],
							'logs'=>$user["first_name"]." ".$user["last_name"].", chuyển số điện thoại KH ".$lead["opportunity"],
							'phone'=>$lead["phone"],
							'created_at'=> date("Y-m-d H:i:s"),
						);
						$Leadmap=Leadmap::where('lead_id', $lead["id"])->where('email',$email)->first();
						if(!$Leadmap){
							$mapleadAdd=array("lead_id"=>$lead["id"], "email"=>$email);
							Leadmap::insert($mapleadAdd);
						}
						return response()->json(['mess' => 'Thay thế thành công', 'email' => $email], 200);
					}else{
						return response()->json(['mess' => 'Không tìm thấy KH', 'email'=>$email], 200);
					}
					
				}
				return response()->json(['mess' => 'Không tìm thấy user', 'email'=>$email], 200);
			}
				
			return response()->json(['mess' => 'Không tìm thấy user', 'email'=>$email], 200);
		}else{
			return response()->json(['error' => trans('dashboard.datafail')], 500);
		}
	}

	public function validate_email($content) {

		if($content!="" && strlen($content)>5 && (strpos($content, '@') !== false)){
			$emailList=explode(" ",$content);
			if(count($emailList)>0){
				for($i=0;$i<count($emailList);$i++){
					if (filter_var($emailList[$i], FILTER_VALIDATE_EMAIL)) {
						return $emailList[$i];
					}
				}
				return false;
			}
			return false;;
		}
		return false;
		
    }
   

	public function historySms(Request $request){
		$lead_id =$request->lead_id;
		$timelast=$request->lasttime;
		$lastIdChat=$request->lastIdChat;
		$timeload=$request->timeload;
		$user=$this->userRepository->getUser();
		$limit=100;
		if($timeload>0){
			$limit=5;
		}
		$logData=Smsdesc::where(function ($query) use ($lead_id) {
			$query->where('lead_id', '=', $lead_id);
		})->where(function ($query) use ($lastIdChat) {
			if($lastIdChat!="" && $lastIdChat>0){
				$query->where('id', '<', $lastIdChat);
			}
		})->where('partner_id',$user["partner_id"])
		->orderBy("id", "desc")->offset(0)->limit($limit)->get();//->paginate($limit)->appends(request()->query());
		$logshow=array();
		$listIdUpdate=array();
		if($logData){
			foreach($logData as $key=>$values){
				$pre=$logData->get($key-1);
				$next=$logData->get($key+1);
				$idpre="";
				if($pre){
					$idpre=$pre["id"];
				}
				$idnext="";
				if($next){
					$idnext=$next["id"];
				}
				$listIdUpdate[]=$values["id"];
				$logshow[]=array('id' => $values["id"], 'idpre' => $idpre, 'idnext' => $idnext, 'sender_id' => $values["sender"], 'receive_id' => $values["phone"], 'date'=>date("d/m/Y H:i:s", strtotime($values["created_at"])), "messenger" => $values["description"], 'extention'=>0, 'status'=>$values["status"], 'delivery'=>$values["delivery"], 'read'=>$values["read"]);
			}
		}
		if(count($listIdUpdate)>0){
			Smsdesc::whereIn('id',$listIdUpdate)->where('read',0)->update(['read'=>1]);
		}
        return $logshow;
	}
	

	public function chat(Request $request) {
		$title = trans( 'lead.chat' );
		$dateFormat = "d/m/Y H:i:s";
		$sales_id = addslashes($request->sales_id) ;
		$page_id = addslashes($request->page_id) ;
		$keyword = addslashes($request->keyword);
		$status = addslashes($request->status);
		$lead_id = addslashes($request->lead);
		$start=1;
		if(isset($page) && $page>1){
			$start=$page;
		}
		$limit=20; 
		$userData=$this->userRepository->getUser();
		$user_id=$userData["id"];
		$this->partner_id=$userData->partner_id;
		$user_group=$userData->group_id;
		$approve = addslashes($request->approve);
		$leadDetail="";
		if(isset($approve) && $approve>0){
			$leadDetail=Lead::select('leads.id', 'leads.created_at', 'leads.updated_at', 'leads.created_at', 'leads.opportunity', 'leads.company_name', 'leads.client_name', 'leads.sales_person_id', 'leads.partner_id', 'leads.email', 'leads.phone', 'leads.function', 
			'leads.UTM_Source', 'leads.UTM_Campaign', 'leads.UTM_Medium', 'leads.psid', 'leads.URL', 'leads.status', 'leads.title', 'leads.next_follow_up', 'lead_assign_status.assign_from_name', 'call_action_status.title as statusclient', 'leads.product_name', 'leads.psid', 'leads.page_id') 
			->leftJoin('call_action_status','call_action_status.id','=','leads.status')
			->join('lead_assign_status','lead_assign_status.lead_id','=','leads.id')
			->where('lead_assign_status.id',$approve)
			->where('lead_assign_status.status',0)
			->where('lead_assign_status.user_id',$userData["id"])->first();

		}
		
		$listUserAssignCache = cache('listUserAssignCache32'.$this->partner_id.$userData->id);
		$listUserCache = cache('listUserCache22'.$this->partner_id.$userData->id);

		if(isset($listUserAssignCache) && $listUserAssignCache!=""){
			$listUserAssign=$listUserAssignCache;
			$listUser=$listUserCache;

		}else{
			$grouppermission=GroupUser::getGroup();
			//$listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
			$listUser=$this->memberUsers($userData->id);
			$listUserAssign=$this->userRepository->getAllUserOfPermissionOfStaffOnline($userData, array("messenger.view_other", "messenger.full", "messenger.view_person"));
			Cache::put('listUserAssignCache32'.$this->partner_id.$userData->id, $listUserAssign, now()->addMinutes(10));
			Cache::put('listUserCache22'.$this->partner_id.$userData->id, $listUser, now()->addMinutes(10));
		}

		if(!isset($locked) || $locked==""){
			$locked=0;
		}  
		$partner_id=$this->partner_id;
		$tagGroupCache = cache('tagGroup'.$this->partner_id);
		$tagGroupRightCache = cache('tagGroupRight'.$this->partner_id);
		if(isset($tagGroupCache) && $tagGroupCache!=""){
			$tagGroup=$tagGroupCache;
			$tagGroupRight=$tagGroupRightCache;
		}else{
			$tagGroupData=Tag::where('partner_id',$this->partner_id)->get();
			$tagGroup=$tagGroupData->map( function ( $tagGroupList){
				return [
					'title' => $tagGroupList->title,
					'value' => $tagGroupList->title,
				];
			}
			)->pluck( 'title', 'value')
			->prepend(trans('lead.all'), '');

			$tagGroupRight=$tagGroupData->map( function ( $tagGroup){
				return [
					'title' => $tagGroup->title,
					'value' => $tagGroup->id,
				];
				}
			)->pluck( 'title', 'value');

			Cache::put('tagGroup'.$this->partner_id, $tagGroup, now()->addMinutes(20));
			Cache::put('tagGroupRight'.$this->partner_id, $tagGroupRight, now()->addMinutes(20));

		}
		/*
		$tagGroupRight=$tagGroupData->map( function ( $tagGroup){
			return [
				'title' => $tagGroup->title,
				'value' => $tagGroup->id,
			];
			}
		)->pluck( 'title', 'value'); */

		/*
		$statusList=CallActionStatus::where('partner_id','=',$this->partner_id)
		->orderBy('position', 'asc')->get()
		->map( function ( $statusList ) {
			return [
				'title' => $statusList->title,
				'value' => $statusList->id,
			];
		} )->pluck( 'title', 'value')->prepend(trans('lead.all'), ''); */

		$statusListCache = cache('statusList1'.$this->partner_id);
		if(isset($statusListCache) && $statusListCache!=""){
			$statusList=$statusListCache;
		}else{
			$statusList=CallActionStatus::where('partner_id','=',$this->partner_id)->where('status',1)
			->orderBy('position', 'asc')->get()
			->map( function ( $statusListData ) {
				return [
					'title' => $statusListData->title,
					'value' => $statusListData->id,
				];
			})->pluck( 'title', 'value')->prepend(trans('lead.all'), '');

			Cache::put('statusList1'.$this->partner_id, $statusList, now()->addMinutes(20));
		}
		
		
		//salesList
		$listUserCache="1";
		if(isset($listUser) && $listUser!=""){
			$listUserCache=md5(implode(",",$listUser));
		}
		if(isset($sales_id)){
			$usercache=$sales_id;
		}else{
			$usercache=$userData->id;
		}
		$salesListCache = cache('salesListCache'.$this->partner_id.$listUserCache.$usercache);
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
			Cache::put('salesListCache'.$this->partner_id.$listUserCache.$salesListCache, $salesList, now()->addMinutes(10));
		}
		$pageDataCache = cache('pageDataCache'.$this->partner_id);
		if(isset($pageDataCache) && $pageDataCache!=""){
			$pageData=$pageDataCache;
		}else{
			if($userData->user_id==1){
				$pageData=Getdata::select('config_datas.*')
				->where('config_datas.partner_id','=',$this->partner_id)
				->where('config_datas.status',1)->orderBy('config_datas.id', 'desc')->get();
			}else{
				$pageData=Getdata::select('config_datas.*')
				->join('user_control_page','user_control_page.page_id','=','config_datas.page_id')
				->where('config_datas.partner_id','=',$this->partner_id)
			//	->whereIn('user_control_page.user_id',$listUser)
				->where('config_datas.status',1)->orderBy('config_datas.id', 'desc')->groupBy('user_control_page.page_id')->get();
			}
			Cache::put('pageDataCache'.$this->partner_id, $pageData, now()->addMinutes(20));
		}
		$totalpage=0;
		$leadsList=null;
		$leadsPage=null;
		$totalLead=0;
		$pageList=null;
		$pagenext=0;
		/* 
		if(count($pageData)>0){	
			if(!isset($sales_id) || $sales_id=="" || $sales_id==0){
				if($userData->user_id==1){
					$sales_id=0;
				}else{
					$sales_id=$userData->id;
				}
				
			}
			$key=md5($partner_id.$page_id.$sales_id.$keyword.$status.$lead_id);
			$queryKey=md5(implode(",",request()->query()));
			$totalLeadCache = cache('leadsQueryCache'.$key);
			$leadsPageCache= cache('leadsPageCache'.$key.$queryKey);
			if(isset($totalLeadCache) && $totalLeadCache!=""){
				$totalLead=$totalLeadCache;
				$leadsPage=$leadsPageCache;
			}else{
				$leadsQuery = Lead::select('leads.id', 'leads.opportunity', 'leads.phone', 'leads.URL', 'leads.tags', 'leads.partner_id', 'leads.sales_person_id', 'leads.photos', 'leads.psid','leads.lead_type','leads.title', 'leads.created_at', 'leads.updated_at', 'leads.user_id', 'leads.page_id', 'chat_box.read', 'chat_box.messenger', 'chat_box.title')
				->join('users','users.id','=','leads.sales_person_id')
				->join('chat_box','chat_box.sender_id','=','leads.psid')
				->where([['leads.partner_id',$partner_id],['leads.psid','!=',''],['leads.opportunity','!=','']])
				->where('leads.locked',$locked)
				->where(function ($query)  use ($page_id,$listUser, $sales_id, $keyword, $status, $lead_id){
					
					if($lead_id!="" && $lead_id!="0"){
						$query->where(function ($query1)  use ($lead_id){
							$query1->where('leads.id','=',$lead_id);
							$query1->whereOr('leads.psid','=',$lead_id);

						});
					}
					if($sales_id!="" && $sales_id!="0"){
						$query->where(function ($query1)  use ($sales_id){
							$query1->where('leads.sales_person_id','=',$sales_id);
							$query1->whereOr('leads.user_id','=',$sales_id);
						});
					}else{
						$query->where(function ($query1)  use ($listUser){
							if(isset($listUser) && $listUser!="" && count($listUser)>0){
								$query1->whereIn('leads.sales_person_id',$listUser);
								$query1->orWhere('leads.sales_person_id',0);
							}
						});
					}
					if($keyword!=""){
						$query->where(function ($query1)  use ($keyword){
							$query1->where('leads.opportunity', 'LIKE', "%{$keyword}%");
							$query1->orWhere('leads.email','LIKE', "%{$keyword}%");
							$query1->orWhere('leads.phone', 'LIKE', "%{$keyword}%");
						});
					}
					if($status!=""){
						$query->where('leads.status','=',$status);
					}
				})
				->distinct('leads.psid')
				->groupBy('leads.psid')
				->orderBy('chat_box.read', 'asc')
				->orderBy('chat_box.date_create', 'desc');
				$totalLead=$leadsQuery->count();
				$leadsPage=$leadsQuery->paginate($limit)->appends(request()->query());
				//Cache::put('leadsQueryCache'.$key, $totalLead, now()->addMinutes(1));
				//Cache::put('leadsPageCache'.$key.$queryKey, $leadsPage, now()->addMinutes(1));
			}
		//	->orderBy('leads.updated_at', 'DESC');
			//->orderBy('chat_box.read', 'asc');
			//$totalLead=count($leadsQuery->get());
			$totalpage=$totalLead/$limit;
			if($leadsPage!=""){
			
				$leadsList=$leadsPage->map( function ( $lead) use ($dateFormat){
					return [
						'id'           => $lead->id,
						'created_at'   => date($dateFormat,strtotime($lead->created_at)),
						'update_at'   => date($dateFormat,strtotime($lead->updated_at)),
						'opportunity' => $lead->opportunity,
						'sale_id'  => $lead->sales_person_id,
						'sale_name'  => $lead->first_name,
						'product_id'   => $lead->product_id,
						'partner_id'   => $lead->partner_id,
						'product_name' => $lead->product_name,
						'email'        => $lead->email,
						'tags'		   => $lead->tags,
						'phone'        => $lead->phone,
						'photos'        => $lead->photos,
						'psid'		   =>$lead->psid,
						'read'		   =>$lead->read,
						'messenger'	    =>$lead->messenger,
						'title'		   =>$lead->title,
						'page_id'	   =>$lead->page_id,
						'status'	   =>$lead->status,
						'lead_type'	   =>$lead->lead_type,
						'status_title'	=>$lead->title,
						'sales_person_id'=> $lead->sales_person_id, 
						'icons'=> $lead->icons
					];
				}
				);
			}else{
				$leadsList=[];
			}
			
			$pageList=$pageData->map(function ( $pageListData ) {
				return [
					'title' => $pageListData->title,
					'value' => $pageListData->page_id,
				];
			} )->pluck( 'title', 'value')->prepend(trans('lead.all'), '');
			$project_id=$this->partner_id; 
			
			
			if($totalpage>1){
				$pagenext=2;
			} 
		} */
		$this->generateParams();
		return view( 'user.lead.chat', compact( 'title', 'leadsList', 'leadsPage', 'salesList', 'pageList', 'totalLead', 'tagGroup', 'pagenext', 'tagGroupRight', 'approve', 'leadDetail', 'listUserAssign', 'statusList', 'user_group'));
	}

	public function detailComment(Request $request){
		$comment_id=$request->comment_id;
		$userData=$this->userRepository->getUser();
		$this->partner_id=$userData->partner_id;
		$user_id=$userData->id;

		if($comment_id!=""){ 
			$commentDetailCache = cache('commentdetail1'.$comment_id);
			if(isset($commentDetailCache) && $commentDetailCache!=""){
				$commentDetail=$commentDetailCache;
			}else{
				$commentDetail = Comment::where('comment_id',$comment_id)->first();
				$commentDetail->update(["read"=>1]);
				Cache::put('commentdetail1'.$comment_id, $commentDetail, now()->addMinutes(2));
			}
			$resultToken=Getdata::where('page_id',$commentDetail["page_id"])->where('partner_id',$this->partner_id)->where('status',1)->first();
			$token="";
            if($resultToken){
                $token=$resultToken["token"];
			}
			$post_id=$commentDetail["post_id"];
			$messenger="";
			if($token!=""){
				$messengerCache = cache('messengercache1'.$comment_id);
				if(isset($messengerCache) && $messengerCache!=""){
					$messenger=$messengerCache;
				}else{
					$url = 'https://graph.facebook.com/'.$post_id.'?access_token='.$token;
					$data=[];
					$curl = curl_init();
					$data = array();
					$data_string = json_encode($data);
					$curl = curl_init($url);
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($curl, CURLOPT_HTTPHEADER, array());
					$result = curl_exec($curl);
					$messenger=json_decode($result)->message;
					Cache::put('messengercache1'.$comment_id, $messenger, now()->addMinutes(1200));
				}
			}

			if($commentDetail){
				return response()->json(['comment' => $commentDetail, 'messenger' => $messenger]);
			}
			return "";
		}else{
			exit();
		}
	}


	public function detailLead(Request $request){
		$lead_id=$request->lead_id;
		$task_id=$request->task_id;
		$userData=$this->userRepository->getUser();
		$this->partner_id=$userData->partner_id;
		$user_id=$userData->id;

		if($userData->user_id==1){
			$listUser=User::select('id')->where('partner_id','=',$this->partner_id)->get()->pluck('id')->toArray();
		}else{
			$grouppermission=GroupUser::getGroup();
			$listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
			if(count($listUser)<=0){
				$listUser=array($user_id);
			}
			$listUser=array_unique($listUser);
		}

		if($lead_id!=""){ 
			/*
			$leadDetailCache = cache('lead3'.$lead_id);
			if(isset($leadDetailCache) && $leadDetailCache!=""){
				$lead=$leadDetailCache;
			}else{
				$lead = Lead::select('leads.id', 'leads.opportunity', 'leads.psid', 'leads.page_id', 'leads.URL', 'leads.function', 'leads.sales_person_id', 'leads.phone', 'leads.email','users.id as staff_care', 'users.first_name as staff_first_name', 'users.last_name as staff_last_name', 'leads.status', 'leads.group_id', 'leads.gim')->leftJoin('users','users.id','=','leads.sales_person_id')->where('leads.id',$lead_id)->where('leads.partner_id',$this->partner_id)
				->where(function ($query1)  use ($listUser, $userData){
					
					if($listUser!="" && count($listUser)>=0){
						$query1->whereIn('leads.sales_person_id',$listUser);
					}
					if($userData->type_user==1){
						$query1->orWhere('leads.sales_person_id',0);
					}
				})
				->first();
				Cache::put('lead3'.$lead_id, $lead, now()->addMinutes(2));
			}*/

			$lead = Lead::select('leads.id', 'leads.opportunity', 'leads.psid', 'leads.page_id', 'leads.URL', 'leads.function', 'leads.sales_person_id', 'leads.phone', 'leads.email','users.id as staff_care', 'users.first_name as staff_first_name', 'users.last_name as staff_last_name', 'leads.status', 'leads.group_id', 'leads.gim')
			->leftJoin('users','users.id','=','leads.sales_person_id')
			->where('leads.id',$lead_id)
			->where('leads.partner_id',$this->partner_id)
				/*->where(function ($query1)  use ($listUser, $userData){
					
					if($listUser!="" && count($listUser)>=0){
						$query1->whereIn('leads.sales_person_id',$listUser);
					}
					if($userData->type_user==1){
						$query1->orWhere('leads.sales_person_id',0);
					} 
				})*/
				->first();

			if($lead){
				$statusListData=array();
				$tagListData=array();
				//Status list
				$statusLead=$lead["status"];
				$opportunity=$lead["opportunity"];
				/*
				if($lead["status"]==0){
					$statusList=CallActionStatus::where('partner_id',$this->partner_id)->where('type',1)->orderBy('position', 'asc')->get();
				}else{
					$statusdetail=CallActionStatus::select('type')->where('partner_id',$this->partner_id)->where('id',$statusLead)->first();
					$statusList=CallActionStatus::where('type',$statusdetail["type"])->where('partner_id',$this->partner_id)->orderBy('position', 'asc')->get();
				} */
				
				$partner_id=$this->partner_id;
				$statusListCache = cache('statusListCache1'.$partner_id.$lead["group_id"]);
				if(isset($statusListCache) && $statusListCache!=""){
					$statusList=$statusListCache;
				}else{
					$statusList=CallActionStatus::where('type',$lead["group_id"])->where('status',1)->where('partner_id',$this->partner_id)->orderBy('position', 'asc')->get();
					Cache::put('statusListCache1'.$this->partner_id.$lead["group_id"], $statusList, now()->addMinutes(100));
				} 

				$tagsCache = cache('tagsCache'.$partner_id.$lead["group_id"]);
				if(isset($tagsCache) && $tagsCache!=""){
					$tags=$tagsCache;
				}else{
					$tags = Tag::where([['partner_id',$this->partner_id],['group_client_id',$lead["group_id"]]])->orderBy('position','asc')->get();
					Cache::put('tagsCache'.$this->partner_id.$lead["group_id"], $tags, now()->addMinutes(100));
				} 

				/*
				$tagListSelect=array();
				if($lead["tags"]!=""){
					$tagListSelect=explode(",",$lead["tags"]);
				} */
				$tagListSelect=LeadsTags::select('tag_id')->where('lead_id',$lead->id)->get()
				->map( function ( $data ) {
					return [
						'value' => $data->tag_id,
					];
				} )->pluck('value')->toArray();

				if($tags){
					foreach($tags as $key=>$values){
						$select="";
						if(in_array($values["id"], $tagListSelect)){
							$select='active';
						}
						$tagListData[]=array('id'=>$values["id"], 'title'=>$values["title"], 'active'=>$select);
					}
				}

				if($statusList){
					foreach($statusList as $key=>$values){
						$select="";
						if($lead["status"]==$values["id"]){
							$select='active';
						}
						$statusListData[]=array('id'=>$values["id"], 'title'=>$values["title"], 'active'=>$select);
					}
				}
				//Start Tags
				
				if($task_id!="" && $task_id!=0){
					//$taskDetail = Task::where("id",$task_id)->where('partner_id',$this->partner_id)->first();
					Task::where('id', $task_id)->update(["time_update"=>time()]);
				}else{
					$taskchek=Task::select('id')->where(['lead_id'=>$lead_id, 'finished'=>$statusLead, 'user_id'=>$userData["id"]])->first();
					if(isset($taskchek) && $taskchek!=""){
						Task::where('id', $task_id)->update(["time_update"=>time()]);
						$task_id=$taskchek["id"];
					}else{
						$task = new Task;
						$task->task_title="@".$userData["first_name"]." ".$userData["last_name"]." chăm sóc Lead/KH ".$opportunity;
						$task->task_description="";
						$task->lead_id=$lead_id;
						$task->task_start=date("Y-m-d H:i:s");
						$task->task_end=date("Y-m-d H:i:s", strtotime("+1 day"));
						$task->full_name=$userData["first_name"]." ".$userData["last_name"];
						$task->finished=$statusLead;
						$task->user_id=$userData["id"];
						$task->partner_id=$this->partner_id;
						$task->save();
						$task_id=$task->id;
					}
				}
				$pageDetail="";
				if($lead["page_id"]!=""){
					$pageCache = cache('tagsCache'.$lead["page_id"]);
					if(isset($pageCache) && $pageCache!=""){
						$pageDetail=$pageCache;
					}else{
						$pageDetail=Getdata::where('page_id', $lead["page_id"])->first();
						Cache::put('tagsCache'.$lead["page_id"], $pageDetail, now()->addMinutes(20));
					} 

				}
				// end
				Lead::where('leads.id',$lead_id)->update(['new_inbox'=>0, 'updated_at'=>date('Y-m-d H:i:s')]);

				if($lead->psid!="" && $lead->psid!=0){
					Chatbox::where('sender_id',$lead->psid)->where('read',0)->update(['read'=>1]);
				}
				return response()->json(['lead' => $lead, 'statuslist' => $statusListData, 'tagList'=>$tagListData, 'task_id'=>$task_id, 'pagedetail'=>$pageDetail]);
			}
			return "";
		}else{
			exit();
		}
	}
	public function pageloading(Request $request) {
		$title = trans( 'lead.leads' );
		$dateFormat = "d/m/Y H:i:s";
		$sales_id = addslashes($request->sales_id) ;
		$page_id = addslashes($request->page_id) ;
		$page = addslashes($request->page);
		$autoload = addslashes($request->autoload);
		$status = addslashes($request->status);
		$datenow=date("Y-m-d H:i:s", strtotime("-30 day"));
		$profilter = addslashes($request->profilter);
		$keyword = addslashes($request->keyword);
		$start=1;
		if(isset($page) && $page>1){
			$start=$page;
		}
		$limit=20; 
		$userData=$this->userRepository->getUser();
		$this->partner_id=$userData->partner_id;
		//$listUser=$this->userRepository->getUserListSearch($grouppermission, $userData);

		$keycache=md5($page_id.$status.$autoload.$datenow.$keyword.$profilter.$sales_id.$userData->id);
		$userListCache = cache('userListCache1'.$keycache);
		if(isset($userListCache) && $userListCache!=""){
			$listUser=$userListCache;
		}else{
			$grouppermission=GroupUser::getGroup();
			//$listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
			$listUser=$this->memberUsers($userData->id);
			Cache::put($userListCache, $listUser, now()->addMinutes(10));
		} 
		if(isset($_GET["demo"])){
			var_dump($listUser);
			echo $sales_id;
			die();	
		}
		if(!isset($sales_id) || $sales_id=="" || $sales_id==0){
			if(count($listUser)>1){
				$sales_id=0;
			}else{
				if($userData->user_id==1){
					$sales_id=0;
				}else{
					$sales_id=$userData->id;
				}
			}
			
		}
		

		if(!isset($locked) || $locked==""){
			$locked=0;
		}   
		$partner_id=$this->partner_id; 
		$leadsQuery = Lead::select('leads.id', 'leads.opportunity', 'leads.phone', 'leads.URL', 'leads.tags', 'leads.partner_id', 'leads.sales_person_id', 'leads.photos', 'leads.psid','leads.lead_type','leads.title','leads.sales_person_id', 'leads.created_at', 'leads.updated_at', 'leads.user_id', 'leads.page_id', 'leads.messenger', 'leads.new_inbox')
			->where([['leads.partner_id',$partner_id],['leads.psid','!=',''],['leads.opportunity','!=','']])
			->where('leads.locked',$locked)
			->whereNotNull('leads.messenger')
			->where(function ($query)  use ($page_id,$listUser, $sales_id, $autoload, $status, $profilter, $keyword){
				/*
				if($page_id!=""){
					$query->where('leads.page_id',$page_id);
				}  */
				if($sales_id!="" && $sales_id!="0"){
					$query->where(function ($query1)  use ($sales_id){
						$query1->where('leads.sales_person_id','=',$sales_id);
						$query1->whereOr('leads.user_id','=',$sales_id);
					});
				}else{
					$query->where(function ($query1)  use ($listUser){
						if(isset($listUser) && $listUser!="" && count($listUser)>0){
							$query1->whereIn('leads.sales_person_id',$listUser);
							//$query1->orWhere('leads.sales_person_id',0);
						}
					}); 
				}
				if($keyword!=""){
					$query->where(function ($query1)  use ($keyword){
						$query1->where('leads.opportunity', 'LIKE', "%{$keyword}%");
						$query1->orWhere('leads.email','LIKE', "%{$keyword}%");
						$query1->orWhere('leads.phone', 'LIKE', "%{$keyword}%");
						$query1->orWhere('leads.messenger', 'LIKE', "%{$keyword}%");
					});
				} 
				if($status!=""){
					$query->where('leads.status','=',$status);
				}
				if($profilter==2){
					$query->where('leads.new_inbox','=',1);
				}
				if($profilter==3){
					$query->where('leads.new_inbox','=',0);
				}
			}) 
			->distinct()
			->orderBy('leads.last_update', 'desc');
			
			$totalLead=$leadsQuery->count();
			$totalpage=ceil($totalLead/$limit);
			$leadsPage=$leadsQuery->paginate($limit)->appends(request()->query());
			$leadsList=$leadsPage->map( function ( $lead) use ($dateFormat){
                return [
                    'id'           => $lead->id,
					'created_at'   => date($dateFormat,strtotime($lead->created_at)),
					'update_at'    => date($dateFormat,strtotime($lead->updated_at)),
					'opportunity'  => $lead->opportunity,
					'sale_id'  	   => $lead->sales_person_id,
					'sale_name'    => $lead->first_name,
					'product_id'   => $lead->product_id,
					'partner_id'   => $lead->partner_id,
					'product_name' => $lead->product_name,
					'email'        => $lead->email,
					'tags'		   => $lead->tags,
                    'phone'        => $lead->phone,
					'psid'		   =>$lead->psid,
					'photos'       => $lead->photos,
					'page_id'	   =>$lead->page_id,
					'status'	   =>$lead->status,
					'lead_type'	   =>$lead->type,
					'read'		   => $lead->new_inbox,
					'messenger'	   => (isset($lead->messenger) && $lead->messenger!="")?$this->shorten_string(stripcslashes($lead->messenger),10):"",
					'new_inbox'		=>$lead->new_inbox,
					'sales_person_id'=> $lead->sales_person_id, 
					'icons'=> $lead->icons, 
                ];
			}

		);
		
		$pagenext=1;
		if($totalpage>$page){
			$pagenext=$page+1;
		}
		return response()->json(['leadsList' => $leadsList, 'pagenext'=>$pagenext, 'totalpage'=>$totalpage]);
		//return view( 'user.lead.chat', compact( 'title', 'leadsList', 'leadsPage', 'totalLead'));
	}

	public function messengerloading(Request $request) {
		$title = trans( 'lead.leads' );
		$dateFormat = "d/m/Y H:i:s";
		$sales_id = addslashes($request->sales_id) ;
		$page_id = addslashes($request->page_id) ;
		$page = addslashes($request->page);
		$autoload = addslashes($request->autoload);
		$status = addslashes($request->status);
		$datenow=date("Y-m-d H:i:s", strtotime("-30 day"));

		$start=1;
		if(isset($page) && $page>1){
			$start=$page;
		}
		$limit=20; 
		$userData=$this->userRepository->getUser();
		$this->partner_id=$userData->partner_id;
		//$grouppermission=GroupUser::getGroup();
		//$listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
		if(!isset($sales_id) || $sales_id=="" || $sales_id==0){
			if($userData->user_id==1){
				$sales_id=0;
			}else{
				$sales_id=$userData->id;
			}
			
		}
		if(!isset($locked) || $locked==""){
			$locked=0;
		}    
		$numberPsid=0;
		$partner_id=$this->partner_id; 
		$leadsQuery = Lead::select('leads.id', 'leads.opportunity', 'leads.phone', 'leads.URL', 'leads.tags', 'leads.partner_id', 'leads.sales_person_id', 'leads.photos', 'leads.psid', 'leads.lead_type','leads.title','leads.sales_person_id', 'leads.created_at', 'leads.updated_at', 'leads.user_id', 'leads.page_id', 'leads.messenger', 'leads.new_inbox')
			->where('leads.opportunity','!=','')
			->where('leads.locked',$locked)
			->where('leads.partner_id',$partner_id)
			->where('leads.psid','>',0) 
			->where('leads.new_inbox',1) 
			->whereNotNull('leads.messenger')
			->where('leads.messenger','!=','')

			->where(function ($query)  use ($page_id, $sales_id, $autoload, $status){
				$query->where('leads.sales_person_id','=',$sales_id);
				$query->orWhere('leads.user_id','=',$sales_id);
			})  
			->where(function ($query)  use ($status){
				if($status!="" && $status!="0"){
					$query->where('leads.status','=',$status);
				} 
			})  
			->distinct('leads.psid')->limit($limit)
			->orderBy('leads.last_update', 'desc')->get();
			//var_dump($leadsQuery);
			//die();
			$leadsList=$leadsQuery->map( function ( $lead) use ($dateFormat){
                return [
                    'id'           => $lead->id,
					'created_at'   => date($dateFormat,strtotime($lead->created_at)),
					'update_at'    => date($dateFormat,strtotime($lead->updated_at)),
					'opportunity'  => $lead->opportunity,
					'sale_id'  	   => $lead->sales_person_id,
					'sale_name'    => $lead->first_name,
					'product_id'   => $lead->product_id,
					'partner_id'   => $lead->partner_id,
					'product_name' => $lead->product_name,
					'email'        => $lead->email,
					'tags'		   => $lead->tags,
					'messenger'	   => (isset($lead->messenger) && $lead->messenger!="")?$this->shorten_string(stripcslashes($lead->messenger),10):"",
					'title'		   => $lead->title,
                    'phone'        => $lead->phone,
					'psid'		   =>$lead->psid,
					'photos'       => $lead->photos,
					'page_id'	   =>$lead->page_id,
					'status'	   =>$lead->status,
					'lead_type'	   =>$lead->type,
					'read'		   => $lead->new_inbox,
					'status_title'	=>$lead->title,
					'sales_person_id'=> $lead->sales_person_id, 
					'icons'=> $lead->icons,
                ];
			}
		);
		return response()->json(['leadsList' => $leadsList]);
		//return view( 'user.lead.chat', compact( 'title', 'leadsList', 'leadsPage', 'totalLead'));
	}
	public function assignlead(Request $request){
		$user=$this->userRepository->getUser();
		$lead_id=$request->lead_id;
		$user_to=$request->user_to;
		$user_fullname=$request->user_fullname;
		$group_id=$request->group_id;
		$user_assign=$user["id"];
		$type_assign=$user["type_assign"];

		$task_description = addslashes($request->task_description) ;
		$task_title= addslashes($request->task_title) ;
		$task_deadline  =date("Y-m-d H:i:s",strtotime(addslashes(trim($request->task_deadline))));
		$task_start  =date("Y-m-d H:i:s");
		$task_from_id=$request->task_from_id;
		$task_from_user=$request->task_from_user;

		
		if($lead_id!="" && $user_assign!=""){
			$leadDetail = Lead::where("id",$lead_id)->where('partner_id',$user->partner_id)->first();

			if($user_to==0){
					$partner_id=$leadDetail["partner_id"];
					$lead_name=$leadDetail["opportunity"];
					$dateNow=date("Y-m-d");
					$date=date("Y-m-d");
					//$fiveMinutesago=strtotime('-30 secons');
					$fiveMinutesago=time() - 30;
					$listTag="";
					$listIDHotel=[];
					if($partner_id==21){
						$listTag=LeadTags::whereIn('tag_id',array(74,84))->where('lead_id',$lead_id)->get();
						$listIDHotel=array(227, 207, 224, 222, 218);
					}
					if($listTag!="" && count($listTag)>0){
						$listSales=User::select('users.id', 'users.first_name', 'users.partner_id', 'users.last_name', 'users.full_name','lead_routing.number')
						->join('lead_routing','lead_routing.user_id','=','users.id')
						->where(['users.received_lead'=>1,'users.partner_id'=>$partner_id])
						//->where('users.last_login','>=', 'users.last_logout')
						->where('lead_routing.date',$date)
						->whereIn('users.id',$listIDHotel)
						->groupBy('users.id')
						->orderBy('lead_routing.number','asc')->first(); 
					}else{
						$listSales=User::select('users.id', 'users.partner_id', 'users.first_name', 'users.last_name', 'users.full_name','lead_routing.number')
						->join('lead_routing','lead_routing.user_id','=','users.id')
						->where(['users.received_lead'=>1,'users.partner_id'=>$partner_id])
						//->where('users.last_login','>=', 'users.last_logout')
						->where('lead_routing.date',$date)
						//->where('lead_routing.number', '<',300)
						//->where('users.last_assign','<=', $fiveMinutesago)
						->groupBy('users.id')
						->inRandomOrder()->first();
						//->orderBy('lead_routing.number','asc')->first(); 
					}
					//$now= microtime();
					$now = (int) round(microtime(true) * 10000);
					
					$user_fullname=$listSales["first_name"]." ".$listSales["last_name"];
					$user_to=$listSales["id"];
					if($user_to!="" && $user_to!=0){
						//$userUpdate = User::find($user_to);
						$listSales->assign_time=$now;
						$listSales->last_assign=time();
						$listSales->save();
					}
					
			}
			if($user_to!="" && $user_to!=0){
				$checkAssign=LeadAssignStatus::where(['user_id'=>$user_to, "lead_id"=>$lead_id, "status"=>0])->first();
				if(isset($checkAssign) && $checkAssign!=""){
					return response()->json(['success' => 'Exit'], 200);
				}
				/** */
				if($leadDetail){
					if(isset($task_from_id) && $task_from_id!="" && $task_from_id>0){
						Task::where('id', $task_from_id)->update(["task_end"=>date("Y-m-d H:i:s"), "work_status"=>10]);
					}
					
					$task = new Task;
					$task->task_title=$task_title;
					$assignName=$user["first_name"]." ".$user["last_name"];
					$task->task_description=str_replace(array('{fullname}', '{client}'),array($assignName, $leadDetail["opportunity"]),$task_description);
					$task->lead_id=$lead_id;
					$task->task_start=$task_start;
					$task->task_end=$task_deadline;
					$task->full_name=$user_fullname;
					$task->task_from_fullname=$assignName;
					$task->finished=$leadDetail["status"];
					$task->user_id=$user_to;
					$task->partner_id=$user->partner_id;
					$task->task_from=$task_from_id;
					$task->task_from_user=$user->id;
					$task->save();
					$taskid=$task->id;
					
					$leadAssign = new LeadAssignStatus;
					$leadAssign->assign_from=$user->id;
					$leadAssign->assign_from_name=$user["first_name"]." ".$user["last_name"];
					$leadAssign->user_id=$user_to;
					$leadAssign->lead_id=$lead_id;
					$leadAssign->status=0;
					$leadAssign->date_create=date("Y-m-d H:i:s");
					$leadAssign->date_assign=date("Y-m-d");
					$leadAssign->time_assign=time();
					$leadAssign->task_id=$taskid;
					$leadAssign->group_id=$group_id;
					$leadAssign->link="";
					$leadAssign->save();
					$idAssign=$leadAssign->id;
					$protocol = isset($_SERVER["HTTPS"]) ? 'https://' : 'http://';
					$domain=$protocol.$_SERVER['HTTP_HOST'];
					if(!isset($type_assign) || $type_assign==0){
						$url=$domain.'/lead/chat?approve='.$idAssign;
					}else{
						$url=$domain.'/lead?approve='.$idAssign;
					}
					// LeadAssignStatus::updateOrCreate($dataAssign, ['time_assign' =>time()]);
					LeadAssignStatus::where('id', $idAssign)->update(["link"=>$url]);
	
					$dataLogs = array(
						'user_id' => $user_to,
						'logs'=>$user["first_name"]." ".$user["last_name"]." đã chuyển KH ".$leadDetail["opportunity"]." cho nhân viên ".$user_fullname,
						'logs_description'=>"",
						'created_at'=> date("Y-m-d H:i:s"),
						'lead_id'=>$lead_id,
					 );
					Logs::insert($dataLogs);
				
					$notification = array(
						'partner_id'=>$user->partner_id,
						'user_id' => $user_to,
						'url'=> $url,
						'type'=> "lead",
						'item_id'=> $lead_id,
						'title'=>"Nhận KH từ ".$user["first_name"]." ".$user["last_name"],
						'desc'=>"Vui lòng chấp nhật KH từ ".$user["first_name"]." ".$user["last_name"].". Nếu bạn không thực hiện KH sẽ chuyển cho nhân viên khác",
						'status'=>0, 
						'created_at'=> date("Y-m-d H:i:s"),
						'date_notification'=>time()
					);
					Notification::insert($notification);
					return response()->json(['success' => 'success', 'notification' => $user_fullname], 200);
				}
			
			}
		}else{

			return response()->json(['success' => 'NoSuccess'], 200);
		}
		return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	}
	public function updateassignlead(Request $request){
		$user=$this->userRepository->getUser();
		$lead_id=$request->lead_id;
		$id=$request->id;
		$user_to=$request->user_to;
		$user_fullname=$request->user_fullname;
		$user_assign=$user["id"];
		$group_id=$request->group_id;

		$task_description = addslashes($request->task_description) ;
		$task_title= addslashes($request->task_title) ;
		$task_deadline  =date("Y-m-d H:i:s",strtotime(addslashes(trim($request->task_deadline))));
		$task_start  =date("Y-m-d H:i:s");
		if($lead_id!="" && $user_to!="" && $user_assign!="" && $id!=""){
			/** */
			$checkDetailAssign=LeadAssignStatus::where('id',$id)->first();
			if($checkDetailAssign){
				$task_id=$checkDetailAssign["task_id"];
				$taskDetail = Task::where("id",$task_id)->where('partner_id',$user->partner_id)->first();
				if($taskDetail){
					Task::where('id', $task_id)->update(["user_id"=>$user_to, "full_name"=>$user_fullname, "task_deadline"=>$task_deadline, "task_start"=>$task_start]);
				}else{
					$task = new Task;
					$task->task_title=$task_title;
					$task->task_description=$task_description;
					$task->lead_id=$lead_id;
					$task->task_start=$task_start;
					$task->task_end=$task_deadline;
					$task->full_name=$user_fullname;
					$task->finished=0;
					$task->user_id=$user_to;
					$task->partner_id=$user->partner_id;
					$task->save();
					$task_id=$task->id;
				}
				LeadAssignStatus::where('id', $id)->update(["status"=>2]);
				/*
				$dataAssign = array(
					'assign_from' => $user->id,
					'assign_from_name' => $user->first_name." ".$user->last_name,
					'user_id' => $user_to,
					'lead_id'=>$lead_id,
					'status'=>0,
					'date_create'=> date("Y-m-d H:i:s"),
					'time_assign'=>time(),
					'task_id'=>$task_id,
					'group_id'=>$group_id,
				 );
				 LeadAssignStatus::updateOrCreate($dataAssign, ['time_assign' =>time()]); */
				$leadAssign = new LeadAssignStatus;
				$leadAssign->assign_from=$user->id;
				$leadAssign->assign_from_name=$user["first_name"]." ".$user["last_name"];
				$leadAssign->user_id=$user_to;
				$leadAssign->lead_id=$lead_id;
				$leadAssign->status=0;
				$leadAssign->date_create=date("Y-m-d H:i:s");
				$leadAssign->time_assign=time();
				$leadAssign->task_id=$taskid;
				$leadAssign->group_id=$group_id;
				$leadAssign->save();
				$idAssign=$task->id;
				$leadDetail = Lead::where("id",$lead_id)->where('partner_id',$user->partner_id)->first();
				//LeadAssignStatus::where('id', $id)->update(["user_id"=>$user_to, 'date_create'=> date("Y-m-d H:i:s"), 'task_id'=>$task_id, 'time_assign'=>time()]);
				 $dataLogs = array(
					'user_id' => $user_to,
					'logs'=>$user["first_name"]." ".$user["last_name"]." đã chuyển KH ".$leadDetail["opportunity"]." cho nhân viên ".$user_fullname,
					'logs_description'=>"",
					'created_at'=> date("Y-m-d H:i:s"),
					'lead_id'=>$lead_id,
				 );
				Logs::insert($dataLogs);
				$domain=$_SERVER['HTTP_HOST'];
				$url=$domain.'/lead?approve='.$idAssign;
				$notification = array(
					'partner_id'=>$user->partner_id,
					'user_id' => $user_to,
					'url'=> $url,
					'type'=> "noti",
					'item_id'=> $lead_id,
					'title'=>"Nhận KH từ ".$user["first_name"]." ".$user["last_name"],
					'desc'=>"Vui lòng chấp nhật KH từ ".$user["first_name"]." ".$user["last_name"].". Nếu bạn không thực hiện KH sẽ chuyển cho nhân viên khác",
					'status'=>0, 
					'created_at'=> date("Y-m-d H:i:s"),
					'date_notification'=>time()
				);
				Notification::insert($notification);
				return response()->json(['success' => 'success'], 200);
			}
				
			return response()->json(['success' => 'NoSuccess'], 200);
			
			
		}else{
			return response()->json(['success' => 'NoSuccess'], 200);
		}
		return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	}
	public function updatecommentread(Request $request){

		$comment_id = addslashes($request->comment_id) ;
		if($comment_id!=""){
			Comment::where(["comment_id"=>$comment_id])->update(['read'=>1]);
		}
		return "";
	}
	
	public function editassign(Request $request)
    {
        $user = $this->userRepository->getUser();
        $title = trans('task.tasks');
        $assign=null;
        $id=$request->id;
        //Status list
		$statusList=CallActionStatus::where('partner_id','=',$user->partner_id)->where('status',1)->orderBy('position', 'asc')->get()
		->map( function ( $statusList ) {
			return [
				'title' => $statusList->title,
				'value' => $statusList->id,
			];
		} )->pluck( 'title', 'value')
        ->prepend(trans('lead.all'), '');

        if($id){
            $assign = LeadAssignStatus::select('lead_assign_status.*', 'tasks.task_title', 'tasks.task_end', 'tasks.task_description')->leftJoin('tasks','tasks.id','=','lead_assign_status.task_id')->where('lead_assign_status.id',$id)->first();
		}
		
        $listUserSales=$this->userRepository->getAllStaffOfUser($user->id);
        $staffs="";
		$groupStaff = GroupUser::where( 'partner_id', $user->partner_id)->get()
            ->map( function ( $title ) {
                return [
                    'title' => $title->name,
                    'value' => $title->id,
                ];
            } )->pluck( 'title', 'value' )
			->prepend(trans('lead.select_group_user'), '');
        return view('user.lead.assign.editassign', compact('title', 'statusList', 'assign', 'groupStaff'));
	}
	
	public function receivelead(Request $request){
		$user=$this->userRepository->getUser();
		$assignid=$request->approve;
		$cancel=$request->cancel;
		if($assignid!="" && $user!=""){
			$leadAssign=LeadAssignStatus::select('lead_assign_status.*')
			->join('leads','leads.id','=','lead_assign_status.lead_id')
			->where('lead_assign_status.user_id', $user->id)
			->where('lead_assign_status.status', 0)->where('lead_assign_status.id', $assignid)
			->where(function ($query){
				$query->where('leads.sales_person_id',0);
				$query->orWhereRaw('leads.sales_person_id','=','lead_assign_status.assign_from');
			})
			->first();
			
			if($leadAssign){
				$leadDetail = Lead::where("id",$leadAssign["lead_id"])->first();
				if($cancel==1){
					LeadAssignStatus::where('id', $assignid)->update(["status"=>4]);
					Task::where('id', $leadAssign["task_id"])->update(["work_status"=>4]); //Working
					return response()->json(['success' => 2, 'messenger'=>'Bạn đã bỏ qua Lead '.$leadDetail["opportunity"]], 200);
				}else{
					$lead_id=$leadAssign["lead_id"];
					$result=Lead::where('id', $lead_id)->where('partner_id',$user->partner_id)->update(["sales_person_id"=>$user->id, "last_update"=>date("YmdHis"), "new_inbox"=>1]);
					LeadAssignStatus::where('id', $assignid)->update(["status"=>1, "time_accept"=>time()]);
					if($leadAssign["task_id"]>0){
						Task::where('id', $leadAssign["task_id"])->update(["work_status"=>3]); //Working
					}
					$dataLogs = array(
						'user_id' => $user->id,
						'logs'=>$user->first_name." ".$user->last_name." đã nhận Khách hàng từ ".$leadAssign["assign_from_name"],
						'logs_description'=>"",
						'created_at'=> date("Y-m-d H:i:s"),
						'lead_id'=>$lead_id,
					 );
					
					Logs::insert($dataLogs);
					return response()->json(['success' => 1, 'leadDetail'=>$leadDetail], 200);
				}
				
				
			}else{
				LeadAssignStatus::where('id', $assignid)->update(["status"=>2]);
				return response()->json(['success' => 2, 'messenger'=>'Thời gian nhận khách hàng đã hết hạn hoặc đã có NV khác chăm sóc'], 200);
			}
		}else{
			return response()->json(['success' => 3,  'messenger'=>'Không tìm thấy User'], 200);
		}
		return response()->json(['success' => 4, 'error' => trans('dashboard.not_valid_data'), 'messenger'=>trans('dashboard.not_valid_data')], 500);
	}

	public function checksattusassign(Request $request){
		$user=$this->userRepository->getUser();
		$lead_id=$request->lead_id;
		$timecheck=$request->timecheck;
		if($lead_id!="" && $user["id"]!=""){
			$leadAssign=LeadAssignStatus::where('assign_from', $user["id"])->where('lead_id', $lead_id)->first();
			if($leadAssign){
				if($leadAssign["status"]==0 && $timecheck>=20){
					LeadAssignStatus::where('id',$leadAssign["id"])->update(["staus"=>3]);
				}
				return response()->json(['success' => 'success', 'status' => $leadAssign["status"]], 200);
			}
		}else{
			return response()->json(['success' => 'NoSuccess'], 200);
		}
		return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	}

	public function addtags(Request $request){
		$user=$this->userRepository->getUser();
		$lead_id=$request->lead_id;
		$tags = addslashes($request->tags) ;
		$remove = addslashes($request->remove) ;
		$tags_id=addslashes($request->tag_id);
		
		if($lead_id!="" && $tags!=""){
			if($remove==1){
				$leadDetail = Lead::where("id",$lead_id)->first();
				$tagold=$leadDetail["tags"];
				$tagoldarray=array();
				if($tagold!=""){
					$tagoldarray=explode(",",$tagold);
				}
				$array2 = array_diff($tagoldarray, [$tags]);
				$listTag="";
				if(count($array2)>0){
					$listTag=implode(",",$array2);
				}
				Lead::where('id', $lead_id)->where('partner_id',$user->partner_id)->update(["tags"=>$listTag]);
				
				LeadTags::where(['tag_id'=>$tags_id, 'lead_id'=>$lead_id, 'partner_id'=>$user->partner_id])->delete();
				$dataLogs = array(
					'user_id' => $user->id,
					'logs'=>$user->first_name." ".$user->last_name." Xóa tag: ".$tags." khách hàng ".$leadDetail["opportunity"],
					'logs_description'=>"",
					'created_at'=> date("Y-m-d H:i:s"),
					'lead_id'=>$lead_id,
				);
				Logs::insert($dataLogs);
				$domain=$_SERVER['HTTP_HOST'];
				$url=$domain.'/lead/chat?lead_id='.$lead_id;
				$notification = array(
					'partner_id'=>$user->partner_id,
					'user_id' => $user->id,
					'url'=> $url,
					'type'=> "noti",
					'item_id'=> $lead_id,
					'title'=>"Xóa tag khách hàng ".$leadDetail["opportunity"],
					'desc'=>$user->first_name." ".$user->last_name." Xóa tag: ".$tags." khách hàng ".$leadDetail["opportunity"],
					'status'=>0, 
					'created_at'=> date("Y-m-d H:i:s"),
					'date_notification'=>time()
				);
				Notification::insert($notification);
				return response()->json(['success' => 'success', 'tag_id'=>$tags_id], 200);
			}else{
				//Check Trùng
				if($tags_id=="" || $tags_id=="0"){
					$tagDetail = Tag::where('title','=',$tags)->first();
					if(!$tagDetail || $tagDetail==""){
						$tagquery=new Tag;
						$tagquery->title=$tags;
						$tagquery->partner_id=$user->partner_id;
						$tagquery->position=1;
						$tagquery->created_at=date("Y-m-d H:i:s");
						$tagquery->save();
						$idtag=$tagquery->id;
					}else{
						$idtag=$tagDetail["id"];
					}
				}else{
					$idtag=$tags_id;
				}
				$leadDetail = Lead::where("id",$lead_id)->first();
				$tagold=$leadDetail["tags"];
				$tagoldarray=array();
				if($tagold!=""){
					$tagoldarray=explode(",",$tagold);
				}
				array_push($tagoldarray,$tags);
				Lead::where('id', $lead_id)->where('partner_id',$user->partner_id)->update(["tags"=>implode(",",$tagoldarray)]);
				$dataLogs = array(
					'user_id' => $user->id,
					'logs'=>"Thêm tag: ".$tags." cho khách hàng ",
					'logs_description'=>"",
					'created_at'=> date("Y-m-d H:i:s"),
					'lead_id'=>$lead_id,
				);
				Logs::insert($dataLogs);

				$dataTag = array(
					'user_id' => $user->id,
					'partner_id'=>$user->partner_id,
					'lead_id'=>$lead_id,
					'tag_id'=>$idtag,
					'created_at'=>date("Y-m-d H:i:s"),
				);
				LeadTags::insert($dataTag);
				return response()->json(['success' => 'success', 'tag_id'=>$idtag], 200);
			}
			
			
		}else{
			return response()->json(['success' => 'NoSuccess'], 200);
		}
		return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	}

	public function assign(Request $request)
    {
        $title = trans('task.tasks');
        $title = trans( 'task.tasks' );
		$dateFormat = config('settings.date_format');
		$date  = addslashes($request->starting_date);
		$sales_id = addslashes($request->sales_id) ;
		$status  = addslashes($request->status) ;
        $keyword = addslashes($request->keyword);
        if($date!=""){
			$dateArray=explode("-",trim($date));
			$starting_date=$this->convertDate(trim($dateArray[0]));
			$ending_date=$this->convertDate(trim($dateArray[1]));
			$starting_date=date("Y-m-d", strtotime($starting_date." -1 days"));
			$ending_date=date("Y-m-d", strtotime($ending_date." +1 days"));
			$date_select=$date;
		}else{
			$starting_date=date("Y-m-d",strtotime('today - 1 days'));
			$ending_date=date("Y-m-d",strtotime('today +1 days'));
			$date_select=date("m/d/Y",strtotime('today - 1 days'))." - ".date("m/d/Y");
		}
        $usersList = "";
        $user = $this->userRepository->getUser(); 
        //Status list
        //Source
		$statusList = $this->optionRepository->getAll()->where('partner_id','=',$user->partner_id)->where( 'category', 'status_report' )->get()
        ->map( function ( $title ) {
            return [
                'title' => $title->title,
                'value' => $title->value,
            ];
        } )->pluck( 'title', 'value')
        ->prepend(trans('lead.select_function'), '');
        /*
        $statusList = $this->optionRepository->getAll()->where( 'partner_id', $user->partner_id)->where( 'category', 'status_report' )->get()
        ->map( function ( $title ) {
            return [
                'title' => $title->title,
                'value' => $title->value,
            ];
        } )->pluck( 'title', 'value' )
        ->prepend(trans('lead.select_function'), '');
        */
        //salesList

        $listUserSales=$this->userRepository->getAllStaffOfUser($user->id);
        $salesList="";
        if($listUserSales){
            $salesList=User::join('partner_user','partner_user.user_id','=','users.id')
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
            ->prepend(trans('lead.all'), '');
        }else{
            $salesList=array(''=>trans('lead.all'));
        }
        $dateFormat = config('settings.date_format');
        $date  = addslashes($request->starting_date);
		
        $assignQuery=LeadAssignStatus::select('lead_assign_status.*', 'users.first_name as first_name', 'users.last_name as last_name', 'leads.contact_name as lead_name', 'leads.id as lead_id')
        ->join('users','users.id','=','lead_assign_status.assign_from')
		->join('leads','leads.id','=','lead_assign_status.lead_id')
		//->whereIn('lead_assign_status.assign_from',$listUserSales)
        ->where(function ($query)  use ($starting_date, $ending_date, $status, $keyword){
            if($keyword!=""){
                $query->where(function ($query1)  use ($keyword){
                    $query1->where('leads.contact_name', 'LIKE', "%{$keyword}%");
                    $query1->orWhere('leads.opportunity','LIKE', "%{$keyword}%");
                });
            }
            if($starting_date!=""){
                $query->where('lead_assign_status.date_create','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('lead_assign_status.date_create','<=',$ending_date);
			} 
			if($status!=""){
                $query->where('lead_assign_status.status',$status);
            }
        })->where(function ($query)  use ($sales_id, $listUserSales, $user){
            if($sales_id!="" && $sales_id!="0"){
                $query->where('lead_assign_status.assign_from','=',$sales_id);
                $query->whereOr('lead_assign_status.user_id','=',$sales_id);
            }else{
                if($listUserSales){
                    array_push($listUserSales,$user->id);
				}
                if($listUserSales!="" && count($listUserSales)>0){
                    $query->whereIn('lead_assign_status.assign_from',$listUserSales);
                  //  $query->orWhereIn('lead_assign_status.user_id',$listUserSales);
                }else{
                    $query->where('lead_assign_status.assign_from',$user->id);
                 //   $query->orWhere('lead_assign_status.user_id',$user->id);
                } 
               
            }
        })->distinct()
        ->orderBy("lead_assign_status.id", "desc");
        $totalAssign=count($assignQuery->get());
        $assignPage=$assignQuery->paginate(20)->appends(request()->query());
        $assignList=$assignPage->map( function ( $assign) use ($dateFormat){
                return [
                    'id'	=> $assign->id,
                    'lead_id'   => $assign->lead_id,
					'date_assign'   => $assign->date_create,
					
                    'lead_name' => $assign->lead_name,
					'status' => $assign->status,
					'assign_to_id' => $assign->user_id,
                    'assign_to'  => User::where('id',$assign->user_id)->first(),
                    'taskwork'  => (isset($assign->task_id) && $assign->task_id>0)?Task::where('id',$assign->task_id)->first():null,
                ];
            }
        );
        return view('user.lead.assign.index', compact('title','salesList', 'assignPage', 'statusList', 'totalAssign', 'assignList', 'date_select', 'status'));
	}
	public function assignto(Request $request)
    {
        $title = trans('task.tasks');
		$dateFormat = config('settings.date_format');
		$date  = addslashes($request->starting_date);
		$sales_id = addslashes($request->sales_id) ;
		$status  = addslashes($request->status) ;
        $keyword = addslashes($request->keyword);
        if($date!=""){
			$dateArray=explode("-",trim($date));
			$starting_date=$this->convertDate(trim($dateArray[0]));
			$ending_date=$this->convertDate(trim($dateArray[1]));
			$starting_date=date("Y-m-d", strtotime($starting_date." -1 days"));
			$ending_date=date("Y-m-d", strtotime($ending_date." +1 days"));
			$date_select=$date;
		}else{
			$starting_date=date("Y-m-d",strtotime('today - 1 days'));
			$ending_date=date("Y-m-d",strtotime('today +1 days'));
			$date_select=date("m/d/Y",strtotime('today - 1 days'))." - ".date("m/d/Y");
		}
        $usersList = "";
        $user = $this->userRepository->getUser(); 
        //Status list
        //Source
		$statusList = $this->optionRepository->getAll()->where('partner_id','=',$user->partner_id)->where( 'category', 'status_report' )->get()
        ->map( function ( $title ) {
            return [
                'title' => $title->title,
                'value' => $title->value,
            ];
        } )->pluck( 'title', 'value')
		->prepend(trans('lead.select_function'), '');
		
        $listUserSales=$this->userRepository->getAllStaffOfUser($user->id);
        $salesList="";
        if($listUserSales){
            $salesList=User::join('partner_user','partner_user.user_id','=','users.id')
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
            ->prepend(trans('lead.all'), '');
        }else{
            $salesList=array(''=>trans('lead.all'));
        }
        $dateFormat = config('settings.date_format');
        $date  = addslashes($request->starting_date);
        $assignQuery=LeadAssignStatus::select('lead_assign_status.*', 'users.first_name as first_name', 'users.last_name as last_name', 'leads.contact_name as lead_name', 'leads.id as lead_id')
        ->join('users','users.id','=','lead_assign_status.assign_from')
		->join('leads','leads.id','=','lead_assign_status.lead_id')
		->where('lead_assign_status.user_id',$user->id)
        ->where(function ($query)  use ($starting_date, $ending_date, $status, $keyword){
            if($keyword!=""){
                $query->where(function ($query1)  use ($keyword){
                    $query1->where('leads.contact_name', 'LIKE', "%{$keyword}%");
                    $query1->orWhere('leads.opportunity','LIKE', "%{$keyword}%");
                });
			}
            if($starting_date!=""){
                $query->where('lead_assign_status.date_create','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('lead_assign_status.date_create','<=',$ending_date);
			}
			if($status!=""){
                $query->where('lead_assign_status.status',$status);
            }
        })->where(function ($query)  use ($sales_id, $listUserSales, $user){
            if($sales_id!="" && $sales_id!="0"){
                $query->where('lead_assign_status.assign_from','=',$sales_id);
            }
        })->distinct()
        ->orderBy("lead_assign_status.id", "desc");
        $totalAssign=count($assignQuery->get());
        $assignPage=$assignQuery->paginate(20)->appends(request()->query());
        $assignList=$assignPage->map( function ( $assign) use ($dateFormat){
                return [
                    'id'	=> $assign->id,
                    'lead_id'   => $assign->lead_id, 
					'date_assign'   => $assign->date_create,
                    'lead_name' => $assign->lead_name,
					'status' => $assign->status,
					'link' => $assign->link,
					'assign_to_id' => $assign->user_id,
                    'assign_from'  => User::where('id',$assign->assign_from)->first(),
                    'taskwork'  => (isset($assign->task_id) && $assign->task_id>0)?Task::where('id',$assign->task_id)->first():null,
                ];
            }
        );
        return view('user.lead.assign.assignto', compact('title','salesList', 'assignPage', 'statusList', 'totalAssign', 'assignList', 'date_select', 'status'));
	}

	public function updateLeadImport() {

		$getLeadImport = LeadTemp::where("convert",0)->limit(10000)->get();
		if($getLeadImport){
			foreach($getLeadImport as $listData){
				$partner_id=$listData["partner_id"];
				$group_id=$listData["group_id"];
				$lead_code=$listData["code"];
				$status=$listData["status"];
				$fullname=$listData["name"];

				$email=$listData["email"];
				$phone=$listData["phone"];
				$psid=$listData["psid"];
				$page_id=$listData["page_id"];

				$sales=$listData["sales"];

				$sales_id=$listData["sales_id"];
				$tagsTitle=$listData["nhan_kh"];
				$birthday=$listData["ngay_sinh"];
				$gender=$listData["gioi_tinh"];
				$website=$listData["website"];

				$sales_person_id=0;
				if(isset($sales_id) && $sales_id!=""){
					$sales_person_id=$sales_id;
				}else{
					$sfaff=User::where([['partner_id',$partner_id],['email',$sales]])->first();//->count();
					if($sfaff!=""){
						$sales_person_id  = $sfaff->id;
					}
				}
				$priority    = 1;
				$utm_source  = "Offline";
				$utm_campaign   = "";
				/*
				$stringvaluepsid=$psid;
				if(substr($stringvaluepsid,0,1)=="N"){
					$psid=substr($stringvaluepsid,1);
				}else{
					$psid=$stringvaluepsid;
				}
				if($psid<=0 || strlen($psid)<10){
					$psid="";
				}				 */
				$product_id   = "";
				$product_name   ="";
				$birthday="";
				$tag_id="";
				$phone=substr($phone,0,10);
				if(substr($phone,0,1)!="0"){
					$phone="0".$phone;
				}
				if($phone!="" || $psid!="" || $email!=""){
					
					$lead = new Lead;
					$countLead="";
					if($phone!="" && $phone>0 && strlen(trim($phone))>10){
						$countLead=Lead::where('partner_id',$partner_id)->where('phone', $phone)->first();
					}
					if($countLead=="" && $psid!="" && strlen(trim($psid))>10){
						$countLead=Lead::where('partner_id',$partner_id)->where('psid', $psid)->first();
						
					}
					if($countLead=="" && $email!="" && strlen(trim($email))>4){
						$countLead=Lead::where('partner_id',$partner_id)->where('email', $email)->first();
					}

					if($tagsTitle!=""){
						$tags=Tag::where([['partner_id',$partner_id],['title',$tagsTitle]])->first();
						if($tags==""){
							$tags = new Tag;
							$tags->title = $tagsTitle;
							$tags->partner_id = $partner_id;
							$tags->status = 1;
							$tags->color_bg = "#f41e4b";
							$tags->color_text = "#fff";
							$tags->save();
						}
						$tag_id=$tags->id;
					}
					//$countLead=Lead::where('phone',$phone)->where('partner_id',$partner_id)->first();
					$disctrict_id=0;
					$city_id=0;
					$ward_id=0;
					if($countLead=="" || $countLead==null){
						$lead->opportunity = $fullname;
						$lead->partner_id = $partner_id;
						$lead->gender = $gender;
						$lead->lead_code = $lead_code;
						$lead->email = $email;
						$lead->birth_day=$birthday;
						$lead->phone =  $phone;
						$lead->psid =  $psid;
						$lead->function=$utm_source;
						$lead->status=$status;
						$lead->page_id=$page_id;
						$lead->contact_name =$fullname;
						$lead->client_name=$fullname;
						$lead->user_id =$sales_person_id;
						$lead->sales_person_id=$sales_person_id;
						$lead->group_id=$group_id;
						$lead->tags=$tagsTitle;
						$lead->sales_team_id =0;
						$lead->product_id ="";
						$lead->UTM_Source =$utm_source;
						$lead->UTM_Campaign=$utm_campaign;
						$lead->UTM_Medium="";
						$lead->UTM_Term="";
						$lead->UTM_Content="";
						$lead->URL="";
						$lead->PID="";
						$lead->GCLID="";
						$lead->FBCLID="";
						$lead->token="";
						$lead->save();
						$lead_id=$lead->id;
					}else{
						$leadUpdate = Lead::find($countLead->id);

						if($lead_code!="" && $leadUpdate->lead_code==""){
							$leadUpdate->lead_code=$lead_code;
						}
						if($page_id!=""){
							$leadUpdate->page_id=$page_id;
						}
						if($fullname!=""){
							$leadUpdate->opportunity=$fullname;
							$leadUpdate->contact_name=$fullname;
						}
						if($group_id!="" && $group_id>0){
							$leadUpdate->group_id=$group_id;
						} 
						if($status!="" && $status!=0){
							$leadUpdate->status=$status;
						}
						if($sales_person_id!="" && $sales_person_id>0 && $leadUpdate->sales_person_id<=0){
							$leadUpdate->sales_person_id=$sales_person_id;
						}
						if($utm_source!=""){
							$leadUpdate->function=$utm_source;
							$leadUpdate->UTM_Source=$utm_source;
						}
						if($city_id!=""){
							$leadUpdate->city_id=$city_id;
						}
						if($disctrict_id!=""){
							$leadUpdate->disctrict_id=$disctrict_id;
						}
						if($ward_id!=""){
							$leadUpdate->ward_id=$ward_id;
						}
						if($lead_code!=""){
							$leadUpdate->lead_code=$lead_code;
						}
						if($gender!=""){
							$leadUpdate->gender=$gender;
						}
						$leadUpdate->updated_at=date("Y-m-d H:i:s");
						$result=$leadUpdate->update();
						$lead_id=$leadUpdate->id; 
					}
					if($tag_id!=""){
						$tagUpdate = LeadTags::firstOrNew(['partner_id' =>  $partner_id, 'lead_id' =>$lead_id, 'tag_id' =>$tag_id]);
						$tagUpdate->user_id=$sales_person_id ;
						$tagUpdate->save();
					}
					
				}

				LeadTemp::where("id",$listData["id"])->update(["convert"=>1]);
			}
		}
		exit();
	}
	
	public function updateacceptlead(Request $request){
		$user=$this->userRepository->getUser();
		$received_lead=$request->received_lead;
		if($user->id!="" && $received_lead!=""){
			$userDetail = User::where("id",$user->id)->first();
			if($userDetail){
				$userDetail->update(["received_lead"=>$received_lead]);
				if($received_lead==1){
					$title="nhận lead";
				}else{
					$title="ngưng nhận lead";
				}
				$dataLogs = array(
					'user_id' => $user->id,
					'logs'=>$userDetail["first_name"]." ".$userDetail["last_name"]." ".$title." lúc  ".date("Y-m-d H:i:s"),
					'logs_description'=>"",
					'created_at'=> date("Y-m-d H:i:s"),
					'lead_id'=>0,
				 );
				Logs::insert($dataLogs);
			}
		
			return response()->json(['success' => '1'], 200);
		}else{
			return response()->json(['success' => '0'], 200);
		}
	}



	public function comment(Request $request) {
		$title = trans( 'lead.chat' );
		$dateFormat = "d/m/Y H:i:s";
		$sales_id = addslashes($request->sales_id) ;
		$page_id = addslashes($request->page_id) ;
		$keyword = addslashes($request->keyword);
		$status = addslashes($request->status);
		$lead_id = addslashes($request->lead);
		$start=1;
		if(isset($page) && $page>1){
			$start=$page;
		}
		$limit=20; 
		$userData=$this->userRepository->getUser();
		$this->partner_id=$userData->partner_id;
		$approve = addslashes($request->approve);
		$leadDetail="";
		
		
		
		$listUserAssignCache = cache('listUserAssignCache'.$this->partner_id.$userData->group_id);
		$listUserCache = cache('listUserCache'.$this->partner_id.$userData->group_id);

		if(isset($listUserAssignCache) && $listUserAssignCache!=""){
			$listUserAssign=$listUserAssignCache;
			$listUser=$listUserCache;

		}else{
			$grouppermission=GroupUser::getGroup();
			$listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
			$listUserAssign=$this->userRepository->getAllUserOfPermissionOfStaff($userData, array("messenger.view_other", "messenger.full", "messenger.view_person"));
			Cache::put('listUserAssignCache'.$this->partner_id.$userData->group_id, $listUserAssign, now()->addMinutes(10));
			Cache::put('listUserCache'.$this->partner_id.$userData->group_id, $listUser, now()->addMinutes(10));

		}


		if(!isset($locked) || $locked==""){
			$locked=0;
		}  
		$partner_id=$this->partner_id;
		$tagGroupCache = cache('tagGroup'.$this->partner_id);
		$tagGroupRightCache = cache('tagGroupRight'.$this->partner_id);
		if(isset($tagGroupCache) && $tagGroupCache!=""){
			$tagGroup=$tagGroupCache;
			$tagGroupRight=$tagGroupRightCache;
		}else{
			$tagGroupData=Tag::where('partner_id',$this->partner_id)->get();
			$tagGroup=$tagGroupData->map( function ( $tagGroupList){
				return [
					'title' => $tagGroupList->title,
					'value' => $tagGroupList->title,
				];
			}
			)->pluck( 'title', 'value')
			->prepend(trans('lead.all'), '');

			$tagGroupRight=$tagGroupData->map( function ( $tagGroup){
				return [
					'title' => $tagGroup->title,
					'value' => $tagGroup->id,
				];
				}
			)->pluck( 'title', 'value');

			Cache::put('tagGroup'.$this->partner_id, $tagGroup, now()->addMinutes(20));
			Cache::put('tagGroupRight'.$this->partner_id, $tagGroupRight, now()->addMinutes(20));

		}
		/*
		$tagGroupRight=$tagGroupData->map( function ( $tagGroup){
			return [
				'title' => $tagGroup->title,
				'value' => $tagGroup->id,
			];
			}
		)->pluck( 'title', 'value'); */

		/*
		$statusList=CallActionStatus::where('partner_id','=',$this->partner_id)
		->orderBy('position', 'asc')->get()
		->map( function ( $statusList ) {
			return [
				'title' => $statusList->title,
				'value' => $statusList->id,
			];
		} )->pluck( 'title', 'value')->prepend(trans('lead.all'), ''); */

		$statusListCache = cache('statusList'.$this->partner_id);
		if(isset($statusListCache) && $statusListCache!=""){
			$statusList=$statusListCache;
		}else{
			$statusList=CallActionStatus::where('partner_id','=',$this->partner_id)
			->orderBy('position', 'asc')->get()
			->map( function ( $statusListData ) {
				return [
					'title' => $statusListData->title,
					'value' => $statusListData->id,
				];
			})->pluck( 'title', 'value')->prepend(trans('lead.all'), '');

			Cache::put('statusList'.$this->partner_id, $statusList, now()->addMinutes(20));
		}
		
		
		//salesList
		$listUserCache="1";
		if(isset($listUser) && $listUser!=""){
			$listUserCache=md5(implode(",",$listUser));
		}
		$salesListCache = cache('salesListCache'.$this->partner_id.$listUserCache.$userData->id);
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
			Cache::put('salesListCache'.$this->partner_id.$listUserCache.$salesListCache, $salesList, now()->addMinutes(10));
		}
		$pageDataCache = cache('pageDataCache'.$this->partner_id);
		if(isset($pageDataCache) && $pageDataCache!=""){
			$pageData=$pageDataCache;
		}else{
			if($userData->user_id==1){
				$pageData=Getdata::select('config_datas.*')
				->where('config_datas.partner_id','=',$this->partner_id)
				->where('config_datas.status',1)->orderBy('config_datas.id', 'desc')->get();
			}else{
				$pageData=Getdata::select('config_datas.*')
				->join('user_control_page','user_control_page.page_id','=','config_datas.page_id')
				->where('config_datas.partner_id','=',$this->partner_id)
			//	->whereIn('user_control_page.user_id',$listUser)
				->where('config_datas.status',1)->orderBy('config_datas.id', 'desc')->groupBy('user_control_page.page_id')->get();
			}
			Cache::put('pageDataCache'.$this->partner_id, $pageData, now()->addMinutes(20));
		}


		$totalpage=0;
		$leadsList=null;
		$leadsPage=null;
		$totalLead=0;
		$pageList=null;
		$pagenext=0;
		
		if(count($pageData)>0){	
			/*
			if(count($pageData)>0){
				if($page_id=="" || $page_id==0){
					$page_id=$pageData[0]["page_id"];
				}
			} */
			//$listUser=$this->userRepository->getAllUserOnPartner($this->partner_id);
			if(!isset($sales_id) || $sales_id=="" || $sales_id==0){
				if($userData->user_id==1){
					$sales_id=0;
				}else{
					$sales_id=$userData->id;
				}
				
			}
			$key=md5($partner_id.$page_id.$sales_id.$keyword.$status.$lead_id);
			$queryKey=md5(implode(",",request()->query()));
			$totalLeadCache = cache('leadsQueryCacheComment'.$key);
			$leadsPageCache= cache('leadsPageCacheComment'.$key.$queryKey);
			
			//if(isset($totalLeadCache) && $totalLeadCache!="" && $leadsPageCache!=""){
			//	$totalLead=$totalLeadCache;
			//	$leadsPage=$leadsPageCache;
			//}else{ 
				$group_id=$userData->group_id;
			$leadsQuery = Comment::select('leads.id', 'leads.opportunity', 'leads.phone', 'leads.URL', 'leads.tags', 'leads.partner_id', 'leads.sales_person_id', 'leads.photos', 'leads.psid','leads.lead_type','leads.title', 'leads.created_at', 'leads.updated_at', 'leads.user_id', 'leads.page_id', 'messenger_comment.message', 'messenger_comment.permalink_url', 'messenger_comment.parent_id', 'messenger_comment.post_id', 'messenger_comment.comment_id',  'messenger_comment.read')
				->leftJoin('leads','leads.psid','=','messenger_comment.psid')
				->where('leads.partner_id',$partner_id)
				->where('messenger_comment.comment_id','!=','')
				->where('messenger_comment.is_hidden',0)
				->where('messenger_comment.item','=','comment')
				->whereIn('messenger_comment.verb',array('add','edited'))
				->whereRaw('messenger_comment.parent_id=messenger_comment.post_id')
				->where('leads.locked',$locked)
				->where(function ($query)  use ($page_id,$listUser, $sales_id, $keyword, $status, $lead_id, $group_id){
					/*
					if($page_id!=""){
						$query->where('messenger_comment.page_id',$page_id);
					} */
					if($lead_id!="" && $lead_id!="0"){
						$query->where(function ($query1)  use ($lead_id){
							$query1->where('leads.id','=',$lead_id);
							$query1->whereOr('leads.psid','=',$lead_id);

						});
					}
					if(isset($group_id) && !in_array($group_id, array(43,46))){
						if($sales_id!="" && $sales_id!="0"){
							$query->where(function ($query1)  use ($sales_id){
								$query1->where('leads.sales_person_id','=',$sales_id);
								$query1->orWhere('leads.user_id','=',$sales_id);
								$query1->orWhere('leads.sales_person_id',0);
							});
						}else{
							$query->where(function ($query1)  use ($listUser){
								if(isset($listUser) && $listUser!="" && count($listUser)>0){
									$query1->whereIn('leads.sales_person_id',$listUser);
									$query1->orWhere('leads.sales_person_id',0);
								}
							});
						}
					}
					
					if($keyword!=""){
						$query->where(function ($query1)  use ($keyword){
							$query1->where('leads.opportunity', 'LIKE', "%{$keyword}%");
							$query1->orWhere('leads.email','LIKE', "%{$keyword}%");
							$query1->orWhere('leads.phone', 'LIKE', "%{$keyword}%");
						});
					}
					/*
					if($tags!=""){
						$query->whereRaw("FIND_IN_SET('$tags', leads.tags)");
					}*/
					if($status!=""){
						$query->where('leads.status','=',$status);
					}
				})
				->distinct('messenger_comment.comment_id')
				->orderBy('messenger_comment.read', 'asc')
				->orderBy('messenger_comment.time', 'desc');
				$totalLead=$leadsQuery->count();
				$leadsPage=$leadsQuery->paginate($limit)->appends(request()->query());
				//Cache::put('leadsQueryCacheComment'.$key, $totalLead, now()->addMinutes(1));
				//Cache::put('leadsPageCacheComment'.$key.$queryKey, $leadsPage, now()->addMinutes(1));
			//}
			$totalpage=$totalLead/$limit;
			$leadsList=$leadsPage->map( function ( $lead) use ($dateFormat){
				return [
					'id'            => $lead->id,
					'created_at'    => date($dateFormat,strtotime($lead->created_at)),
					'update_at'   	=> date($dateFormat,strtotime($lead->updated_at)),
					'opportunity' 	=> $lead->opportunity,
					'sale_id'  		=> $lead->sales_person_id,
					'sale_name'  	=> $lead->first_name,
					'product_id'    => $lead->product_id,
					'partner_id'    => $lead->partner_id,
					'product_name'  => $lead->product_name,
					'email'         => $lead->email,
					'tags'		    => $lead->tags,
					'phone'         => $lead->phone,
					'photos'        => $lead->photos,
					'psid'		    =>$lead->psid,
					'read'		    =>$lead->read,
					'page_id'	    =>$lead->page_id,
					'status' 		=>$lead->status,
					'lead_type'	    =>$lead->lead_type,
					'message'	    =>$lead->message,
					'parent_id'	    =>$lead->parent_id,
					'comment_id'    =>$lead->comment_id,
					'permalink_url' =>$lead->permalink_url,
					'status_title'	=>$lead->title,
					'sales_person_id'=> $lead->sales_person_id, 
					'icons'=> $lead->icons
				];
			}
			);
			$pageList=$pageData->map(function ( $pageListData ) {
				return [
					'title' => $pageListData->title,
					'value' => $pageListData->page_id,
				];
			} )->pluck( 'title', 'value')->prepend(trans('lead.all'), '');
			
			
			$project_id=$this->partner_id; 
			
			
			if($totalpage>1){
				$pagenext=2;
			} 
		}
		$this->generateParams();
		return view( 'user.lead.comment', compact( 'title', 'leadsList', 'leadsPage', 'salesList', 'pageList', 'totalLead', 'tagGroup', 'pagenext', 'tagGroupRight', 'approve', 'leadDetail', 'listUserAssign', 'statusList'));
	}

	public function pageloadingComment(Request $request) {
		$title = trans( 'lead.leads' );
		$dateFormat = "d/m/Y H:i:s";
		$sales_id = addslashes($request->sales_id) ;
		$page_id = addslashes($request->page_id) ;
		$keyword = addslashes($request->keyword) ;
		$page = addslashes($request->page);
		$autoload = addslashes($request->autoload);
		$status = addslashes($request->status);
		$datenow=date("Y-m-d H:i:s", strtotime("-30 day"));
		$lead_id = addslashes($request->lead);
		$start=1;
		if(isset($page) && $page>1){
			$start=$page;
		}
		$limit=20; 
		$userData=$this->userRepository->getUser();
		$this->partner_id=$userData->partner_id;
		$grouppermission=GroupUser::getGroup();
		$listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
		if(!isset($sales_id) || $sales_id=="" || $sales_id==0){
			if($userData->user_id==1){
				$sales_id=0;
			}else{
				$sales_id=$userData->id;
			}
			
		}
		if(!isset($locked) || $locked==""){
			$locked=0;
		}   
		$partner_id=$this->partner_id; 

		$leadsQuery = Comment::select('leads.id', 'leads.opportunity', 'leads.phone', 'leads.URL', 'leads.tags', 'leads.partner_id', 'leads.sales_person_id', 'leads.photos', 'leads.psid','leads.lead_type','leads.title', 'leads.created_at', 'leads.updated_at', 'leads.user_id', 'leads.page_id', 'messenger_comment.message', 'messenger_comment.read', 'messenger_comment.permalink_url', 'messenger_comment.parent_id', 'messenger_comment.post_id', 'messenger_comment.comment_id')
				->leftJoin('leads','leads.psid','=','messenger_comment.psid')
				->where('leads.partner_id',$partner_id)
				->where('messenger_comment.comment_id','!=','')
				->where('messenger_comment.is_hidden',0)
				->where('messenger_comment.item','=','comment')
				->whereIn('messenger_comment.verb',array('add','edited'))
				->whereRaw('messenger_comment.parent_id=messenger_comment.post_id')
				->where('leads.locked',$locked)
				->where(function ($query)  use ($page_id,$listUser, $sales_id, $keyword, $status, $lead_id){
					/*
					if($page_id!=""){
						$query->where('messenger_comment.page_id',$page_id);
					} */
					if($lead_id!="" && $lead_id!="0"){
						$query->where(function ($query1)  use ($lead_id){
							$query1->where('leads.id','=',$lead_id);
							$query1->whereOr('leads.psid','=',$lead_id);

						});
					}
					
					if($sales_id!="" && $sales_id!="0"){
						$query->where(function ($query1)  use ($sales_id){
							$query1->where('leads.sales_person_id','=',$sales_id);
							$query1->orWhere('leads.user_id','=',$sales_id);
						});
					}else{
						$query->where(function ($query1)  use ($listUser){
							if(isset($listUser) && $listUser!="" && count($listUser)>0){
								$query1->whereIn('leads.sales_person_id',$listUser);
								$query1->orWhere('leads.sales_person_id',0);
							}
						});
					}
					if($keyword!=""){
						$query->where(function ($query1)  use ($keyword){
							$query1->where('leads.opportunity', 'LIKE', "%{$keyword}%");
							$query1->orWhere('leads.email','LIKE', "%{$keyword}%");
							$query1->orWhere('leads.phone', 'LIKE', "%{$keyword}%");
						});
					}
					/*
					if($tags!=""){
						$query->whereRaw("FIND_IN_SET('$tags', leads.tags)");
					}*/
					if($status!=""){
						$query->where('leads.status','=',$status);
					}
				})
				->distinct('messenger_comment.comment_id')
				->orderBy('messenger_comment.read', 'asc')
				->orderBy('messenger_comment.time', 'desc');
			$totalLead=$leadsQuery->count();
			$totalpage=$totalLead/$limit;
			$leadsPage=$leadsQuery->paginate($limit)->appends(request()->query());
			$leadsList=$leadsPage->map( function ( $lead) use ($dateFormat){
                return [
                    'id'           => $lead->id,
					'created_at'   => date($dateFormat,strtotime($lead->created_at)),
					'update_at'    => date($dateFormat,strtotime($lead->updated_at)),
					'opportunity'  => $lead->opportunity,
					'sale_id'  	   => $lead->sales_person_id,
					'sale_name'    => $lead->first_name,
					'product_id'   => $lead->product_id,
					'partner_id'   => $lead->partner_id,
					'product_name' => $lead->product_name,
					'email'        => $lead->email,
					'tags'		   => $lead->tags,
                    'phone'        => $lead->phone,
					'psid'		   =>$lead->psid,
					'photos'       => $lead->photos,
					'message'	    =>$lead->message,
					'parent_id'	    =>$lead->parent_id,
					'comment_id'    =>$lead->comment_id,
					'permalink_url' =>$lead->permalink_url,
					'page_id'	   =>$lead->page_id,
					'status'	   =>$lead->status,
					'lead_type'	   =>$lead->type,
					'read'		   => $lead->read,
					'status_title'	=>$lead->title,
					'sales_person_id'=> $lead->sales_person_id, 
					'icons'=> $lead->icons, 
                ];
			}
		);
		$pagenext=0;
		if($totalpage>$page){
			$pagenext=$page+1;
		}
		return response()->json(['leadsList' => $leadsList, 'pagenext'=>$pagenext, 'totalpage']);
		//return view( 'user.lead.chat', compact( 'title', 'leadsList', 'leadsPage', 'totalLead'));
	}

	public function commentloading(Request $request) {
		$title = trans( 'lead.leads' );
		$dateFormat = "d/m/Y H:i:s";
		$sales_id = addslashes($request->sales_id) ;
		$page_id = addslashes($request->page_id) ;
		$page = addslashes($request->page);
		$autoload = addslashes($request->autoload);
		$status = addslashes($request->status);
		$datenow=date("Y-m-d H:i:s", strtotime("-30 day"));

		$start=1;
		if(isset($page) && $page>1){
			$start=$page;
		}
		$limit=20; 
		$userData=$this->userRepository->getUser();
		$this->partner_id=$userData->partner_id;
		//$grouppermission=GroupUser::getGroup();
		//$listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
		if(!isset($sales_id) || $sales_id=="" || $sales_id==0){
			if($userData->user_id==1){
				$sales_id=0;
			}else{
				$sales_id=$userData->id;
			}
			
		}
		if(!isset($locked) || $locked==""){
			$locked=0;
		}    
		$partner_id=$this->partner_id; 
		$leadsQuery = Lead::select('leads.id', 'leads.opportunity', 'chat_box.read', 'leads.phone', 'leads.URL', 'leads.tags', 'leads.partner_id', 'leads.sales_person_id', 'leads.photos', 'leads.psid','leads.lead_type','leads.title','leads.sales_person_id', 'leads.created_at', 'leads.updated_at', 'leads.user_id', 'leads.page_id')
			->join('messenger_comment','messenger_comment.psid','=','leads.psid')
			->where([['leads.partner_id',$partner_id],['leads.psid','!=',''],['leads.opportunity','!=','']])
			->where('chat_box.read',0)
			->where('leads.locked',$locked)
			->where(function ($query)  use ($page_id, $sales_id, $autoload, $status){
				$query->where(function ($query1)  use ($sales_id){
					$query1->where('leads.sales_person_id','=',$sales_id);
					$query1->whereOr('leads.user_id','=',$sales_id);
				});
				if($status!=""){
					$query->where('leads.status','=',$status);
				}
			}) 
			->distinct('leads.psid')->limit($limit)
			->orderBy('chat_box.date_create', 'desc')->get();
			$leadsList=$leadsQuery->map( function ( $lead) use ($dateFormat){
                return [
                    'id'           => $lead->id,
					'created_at'   => date($dateFormat,strtotime($lead->created_at)),
					'update_at'    => date($dateFormat,strtotime($lead->updated_at)),
					'opportunity'  => $lead->opportunity,
					'sale_id'  	   => $lead->sales_person_id,
					'sale_name'    => $lead->first_name,
					'product_id'   => $lead->product_id,
					'partner_id'   => $lead->partner_id,
					'product_name' => $lead->product_name,
					'email'        => $lead->email,
					'tags'		   => $lead->tags,
                    'phone'        => $lead->phone,
					'psid'		   =>$lead->psid,
					'photos'       => $lead->photos,
					'page_id'	   =>$lead->page_id,
					'status'	   =>$lead->status,
					'lead_type'	   =>$lead->type,
					'read'		   => $lead->read,
					'status_title'	=>$lead->title,
					'sales_person_id'=> $lead->sales_person_id, 
					'icons'=> $lead->icons,
                ];
			}
		);
		return response()->json(['leadsList' => $leadsList]);
		//return view( 'user.lead.chat', compact( 'title', 'leadsList', 'leadsPage', 'totalLead'));
	}


	public function historyComment(Request $request){
		$commentid =$request->comment_id;
		$timelast=$request->lasttime;
		$timeload=$request->timeload;

		$lastIdChat=$request->lastIdChat;
		$datenow=date("Y-m-d H:i:s", strtotime("-30 day"));
		$limit=50;
		if($timeload>0){
			$limit=5;
		}
		$pageDataCache = cache('historyComment'.$commentid.$lastIdChat.$timeload);
		/*
		if(isset($pageDataCache) && $pageDataCache!=""){
			$logData=$pageDataCache;
		}else{
			
			$logData=Comment::select('page_id', 'id', 'message', 'psid', 'updated_time', 'time', 'read', 'permalink_url', 'comment_id', 'parent_id')->where('updated_time','>=', $datenow)
			->where(function ($query) use ($commentid) {
				$query->where('parent_id', '=', $commentid);
			})->where(function ($query) use ($lastIdChat) {
				if($lastIdChat!="" && $lastIdChat>0){
					$query->where('id', '>', $lastIdChat);
				}
			})
			->orderBy("id", "desc")->offset(0)->limit($limit)->get();
			Cache::put('historyComment'.$commentid.$lastIdChat.$timeload, $logData, now()->addMinutes(1));

		}*/
		$logData=Comment::select('page_id', 'id', 'message', 'psid', 'updated_time', 'time', 'read', 'permalink_url', 'comment_id', 'parent_id')->where('updated_time','>=', $datenow)
			->where(function ($query) use ($commentid) {
				$query->where('parent_id', '=', $commentid);
			})->where(function ($query) use ($lastIdChat) {
				if($lastIdChat!="" && $lastIdChat>0){
					$query->where('id', '>', $lastIdChat);
				}
			})
			->orderBy("id", "desc")->offset(0)->limit($limit)->get();
		//$logData=Chatbox::whereNotNull('comment_id')->where('comment_id', '=', $commentid)->orderBy("id", "desc")->offset(0)->limit(1)->get();
		$logshow=array();
		$listIdUpdate=array();
		if($logData){
			foreach($logData as $key=>$values){
				$messenger="";
				$pre=$logData->get($key-1);
				$next=$logData->get($key+1);
				$idpre="";
				if($pre){
					$idpre=$pre["id"];
				}
				$idnext="";
				if($next){
					$idnext=$next["id"];
				}
				$listIdUpdate[]=$values["id"];
				$phone="";//$this->checkphone($values["extention"]); //str_replace(array("+84", "84"),array("0","0"),$values["extention"]);
				/*
				if(substr($phone,2)=='00'){
					$phone=substr($phone,1,strlen($phone)-1); extention
				} 
				$email="";
				if($this->validate_email($values["messenger"])!==false){
					$email=$this->validate_email($values["messenger"]);
				}*/

				$messenger="<div class='linechatcontent'>".$this->turnUrlIntoHyperlink(nl2br($values["message"]))."</div>";
				
				$logshow[]=array('id' => $values["id"], 'idpre' => $idpre, 'idnext' => $idnext, 'psid' => $values["receive_id"], 'date' => date("d/m/Y H:i:s", strtotime($values["updated_time"])), "messenger" =>$messenger, 'lasttime'=>$values["time"], 'read'=>$values["read"], "email"=>"",'page_id' => $values["page_id"], 'permalink_url' => "", 'comment_id' => $values["comment_id"]);
			}
		}
		if(count($listIdUpdate)>0){
			Comment::whereIn('id',$listIdUpdate)->where('read',0)->update(['read'=>1]);
		}  
        return $logshow;
	}
	 
	public function shorten_string($string, $wordsreturned)
	{
	$retval = $string;
	$string = preg_replace('/(?<=\S,)(?=\S)/', ' ', $string);
	$string = str_replace("\n", " ", $string);
	$array = explode(" ", $string);
	if (count($array)<=$wordsreturned)
	{
		$retval = $string;
	}
	else
	{
		array_splice($array, $wordsreturned);
		$retval = implode(" ", $array)." ...";
	}
	return $retval;
	}
	public function gimlead(Request $request){
		$user=$this->userRepository->getUser();
		$leads=$request->lead_id;
		$ghim=$request->ghim;
		$partner_id=$user->partner_id;
		if($user->id!="" && $leads!=""){
			$countTotalGhim=Lead::where('partner_id',$partner_id)->where('gim', 1)->where('sales_person_id', $user->id)->count('id');
			if($countTotalGhim<30){
				$lead_detail=Lead::where('partner_id',$partner_id)->where('id', $leads)->where('sales_person_id', $user->id)->first();
				if($lead_detail){
					$lead_detail->update(["gim"=>$ghim, "time_gim"=>date("Y-m-d H:i:s")]);
					if($ghim==1){
						$title="Ghim lead";
					}else{
						$title="Bỏ Gim lead";
					}
					$dataLogs = array(
						'user_id' => $user->id,
						'logs'=>$user["first_name"]." đã Ghem lead ".$lead_detail["id"]." lúc  ".date("Y-m-d H:i:s"),
						'logs_description'=>"",
						'created_at'=> date("Y-m-d H:i:s"),
						'lead_id'=>$leads,
					 );
					Logs::insert($dataLogs);
				}
				return response()->json(['success' => '1'], 200);
			}else{
				return response()->json(['success' => '0', 'mess'=>"Bạn đã Ghim quá 30 KH"], 200);
			}
			
		
			
		}else{
			return response()->json(['success' => '0', 'mess'=>"Có lỗi xảy ra trong quá trình Ghim"], 200);
		}
	}

	public function reporttags(Request $request){
		$user=$this->userRepository->getUser();
		$leads=$request->lead_id;
		$partner_id=$user->partner_id;
		if($user->id!="" && $leads!=""){
			$lead_detail=Lead::where('partner_id',$partner_id)->where('id', $leads)->first();
			if($lead_detail){

				$report_check=ReportTags::where('partner_id',$partner_id)->where('lead_id', $leads)->where('user_id', $lead_detail->sales_person_id)->first();
				if(!isset($report_check) || $report_check==""){
					 $dataReport = array(
						'lead_id' => $leads,
						'user_id'=>$lead_detail->sales_person_id,
						'user_report'=>$user->id,
						'number_report'=>1,
						'created_at'=> date("Y-m-d H:i:s")
					 );
					 ReportTags::insert($dataReport);
					 $dataLogs = array(
						'user_id' => $user->id,
						'logs'=>$user["first_name"]." đã báo sai tag của lead ".$lead_detail["id"]." lúc  ".date("Y-m-d H:i:s"),
						'logs_description'=>"",
						'created_at'=> date("Y-m-d H:i:s"),
						'lead_id'=>$leads,
					 );
					 Logs::insert($dataLogs);
				}else{
					$numberreport=$report_check->number_report+1;
					$report_check->update(['number_report'=>$numberreport]);
					$dataLogs = array(
						'user_id' => $user->id,
						'logs'=>$user["first_name"]." đã báo sai lần ".$numberreport." tag của lead ".$lead_detail["id"]." lúc  ".date("Y-m-d H:i:s"),
						'logs_description'=>"",
						'created_at'=> date("Y-m-d H:i:s"),
						'lead_id'=>$leads,
					 );
					 Logs::insert($dataLogs);
				}
				
			}
		
			return response()->json(['success' => '1'], 200);
		}else{
			return response()->json(['success' => '0'], 200);
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
