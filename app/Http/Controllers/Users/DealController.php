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
use App\Models\CallActionStatus;
use App\Models\Lead;
use App\Models\Logs;
use App\Models\Tag;
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

use App\Models\LeadsTags;
use Cache;
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
use App\Models\Salesteam;
use App\Models\SalesteamMember;


use Excel;
use Sentinel;

use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;
class DealController extends UserController {
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
		ProductRepository $productRepository
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

		view()->share( 'type', 'deal' );
	}

	public function index(Request $request) {

		$title = trans( 'lead.leads' );
		$dateFormat = config('settings.date_format');
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

		$locked = addslashes($request->locked);

		
		$start=1;
		if(isset($page) && $page>1){
			$start=$page;
		}
		$limit=20; 
		if(!isset($daterange ) || $daterange==""){
			$daterange="3-90";
		}

		if(isset($daterange) && $daterange!="" && $daterange!=0){
			$dateRange=explode("-",$daterange);

			/*
			switch ($daterange) {
				case 1:
					$starting_date=date("Y-m-d 00:00:00", strtotime('-90 days'));
					$ending_date=date("Y-m-d 23:59:59", strtotime('-2 days'));
					break;
				case 2:
					$starting_date=date("Y-m-d 00:00:00", strtotime('-120 days'));
					$ending_date=date("Y-m-d 23:59:59", strtotime('-91 days'));
					break;
				case 3:
					$starting_date=date("Y-m-d 00:00:00", strtotime('-180 days'));
					$ending_date=date("Y-m-d 23:59:59", strtotime('-121 days'));
					break;
				case 4:
					$starting_date=date("Y-m-d 00:00:00", strtotime('-720 days'));
					$ending_date=date("Y-m-d 23:59:59", strtotime('-181 days'));
					break;
				default:
					$starting_date=date("Y-m-d 00:00:00", strtotime('-90 days'));
					$ending_date=date("Y-m-d 23:59:59", strtotime('-2 days'));
			}*/
			$starting_date=date("Y-m-d 00:00:00", strtotime('-30 days'));
			$ending_date=date("Y-m-d 23:59:59", strtotime('-1 days'));
			if(count($dateRange)>0){
				$starting_date=date("Y-m-d 00:00:00", strtotime('-'.$dateRange[1].' days'));
				$ending_date=date("Y-m-d 23:59:59", strtotime('-'.$dateRange[0].' days'));
			}
			
	
			$date_select=date("m/d/Y",strtotime($starting_date))." - ".date("m/d/Y",strtotime($ending_date));
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

		$userData=$this->userRepository->getUser();
		$partner_id=$userData->partner_id;
		if(isset($_GET["demo1"])){
		//
			$grouppermission=GroupUser::getGroup();
			var_dump($grouppermission);
			$listUser=$this->userRepository->getUserListSearch($grouppermission, $userData);
			var_dump($listUser);
			die();
		//}else{
		//	$listUser=$this->userRepository->getAllStaffOfUser($userData->id);
		}
		//$grouppermission=GroupUser::getGroup();
		//$listUser=$this->userRepository->getUserListSearch($grouppermission, $userData);

		$grouppermission=GroupUser::getGroup();

		//$listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
		$listUser=$this->memberUsers($userData["id"]);
		if(!isset($locked) || $locked==""){
			$locked=0;
		} 

		$this->generateParams();
		//$listUser=$this->userRepository->getAllUserOnPartner($this->partner_id)
		$listStatusSearch=$status;
		$listStatusSearchList=0;
		if($listStatusSearch!="" && $listStatusSearch!="0" && $listStatusSearch!="1"){
			$listStatusSearchList=$listStatusSearch;
		}
		$type_statusList=0;
		if($type_status!="" && count($type_status)>0){
			$type_statusList=implode(",",$type_status);
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
		$leadsQuery = Lead::select('leads.id', 'leads.created_at', 'leads.updated_at', 'leads.created_at', 'leads.opportunity', 'leads.company_name', 'leads.client_name', 'leads.sales_person_id', 'leads.product_id', 'leads.partner_id', 'leads.email', 'leads.phone', 'leads.function', 'leads.UTM_Source', 'leads.UTM_Campaign', 'leads.UTM_Medium', 'leads.psid', 'leads.URL', 'leads.status', 'leads.title', 'leads.next_follow_up', 'leads.group_id', 'call_action_status.title as call_title', 'call_action_status.type', 'users.first_name', 'call_action_status.icons', 'group_client.name as group_name')
		->leftJoin('call_action_status','call_action_status.id','=','leads.status')
		->leftJoin('users','users.id','=','leads.sales_person_id')
		->join('group_client','group_client.id','=','leads.group_id');
		if($tags!="" && $tags!="0"){
			$leadsQuery->join('lead_tags','lead_tags.lead_id','=','leads.id');
		}
		$leadsQuery->where('leads.locked',$locked)
		->where('group_client.type_client',2)
		->where('leads.partner_id',$partner_id)->where(function ($query) use ($starting_date, $ending_date, $sales_id, $listStatusSearch, $function,$UTM_Source, $keyword, $product_id, $tags, $group_id, $type_status, $brand_id, $tuongtac){
			/*
			if($tuongtac!="" && $tuongtac!="0"){
				$date60daysago=date("Y-m-d H:i:s", strtotime("-90 days"));
				if($tuongtac!="" && $tuongtac=="3"){	
					$query->where('leads.date_last_update','<=', $date60daysago);
				}elseif($tuongtac!="" && $tuongtac=="1"){
					$query->where('leads.date_last_update','>=', $starting_date);
					$query->where('leads.date_last_update','<=', $ending_date);
				}elseif($tuongtac!="" && $tuongtac=="2"){
					$query->where('leads.date_last_update','>', $date60daysago);
				}
			} */

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
						$query1->where('leads.updated_at','>=',$starting_date);
						$query1->orWhere('leads.created_at','>=',$starting_date);
				});
				
			}
			if($ending_date!=""){
				$query->where(function ($query1) use($ending_date){
					$query1->where('leads.updated_at','<=',$ending_date);
					$query1->orWhere('leads.created_at','<=',$ending_date);
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
		->where(function ($query){
			$query->where('leads.phone','!=',"");
		})
		->distinct('leads.id')
		->orderBy('leads.id', 'DESC');
		$totalLead=$leadsQuery->count();

		//$totalLead=$leadsQuery->get()->count('leads.id');
		$lastPage=ceil($totalLead/$limit);
		$leadsPage=$leadsQuery->paginate($limit)->appends(request()->query());
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

		$leadGroupSource =Tag::where('partner_id',$partner_id)->where('group_client_id',44)->get()
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
						->where('partner_user.partner_id','=',$partner_id)
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
			->where('partner_user.partner_id','=',$partner_id)
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
		$statusList=CallActionStatus::where('partner_id','=',$partner_id)->whereIn('type',array(2,4))->orderBy('position', 'asc')->get()
		->map( function ( $statusList ) {
			return [
				'title' => $statusList->title,
				'value' => $statusList->id,
			];
		} )->pluck( 'title', 'value')
		->prepend(trans('lead.all'), '');
		//Product list
		$productList=Product::where('partner_id','=',$partner_id)->orderBy('product_name', 'desc')->get()
		->map( function ( $productList ) {
			return [
				'title' => $productList->product_name,
				'value' => $productList->id,
			];
		} )->pluck( 'title', 'value')
		->prepend(trans('lead.all'), '');
		//Source
		$sourceList = $this->optionRepository->getAll()->where('partner_id','=',$partner_id)->where( 'category', 'function_type' )->get()
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
			
			
			$callStatusQueryCache = cache('callStatusQueryCache2'.$group_id.$partner_id);
			if(isset($callStatusQueryCache) && $callStatusQueryCache!=""){
				$callStatusQuery=$callStatusQueryCache;
			}else{ 
				$callStatusQuery = CallActionStatus::select('call_action_status.*')->join('group_client','group_client.id','=','call_action_status.type')->where("call_action_status.partner_id",$partner_id)->where("group_client.type_client",2)
				->orderBy("call_action_status.position", "asc")
				->get();
				Cache::put('callStatusQueryCache2'.$partner_id.$group_id, $callStatusQuery, now()->addMinutes(100));
			} 
			
			$callStatus=$callStatusQuery->map(function ($list) {
				return [
					'id' => $list->id,
					'title' => $list->title,
					'value' =>  $list->id
				];
			})->pluck( 'title', 'value');	
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

		$project_id=$partner_id; 

		return view( 'user.deal.index', compact( 'title', 'leadsList', 'leadsPage', 'salesList', 'productList', 'statusList', 'sourceList', 'leadGroupSource', 'totalLead', 'date_select', 'keyword', 'product_id', 'project_id', 'fileList', 'callStatus', 'callStatusQuery', 'brand', 'daterange', 'tags', 'sales_id', 'lastPage', 'tuongtac', 'status')); 
	}

	public function kanban(Request $request) {
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
		
		$userData=$this->userRepository->getUser();
        $this->partner_id=$userData->partner_id;
		//$listUser=$this->userRepository->getAllStaffOfUser(0);
		//$listUser=$this->userRepository->getAllUserOnPartner($this->partner_id);
		//$listUser=$this->userRepository->getAllUserOnPartner($userData->id);
		$grouppermission=GroupUser::getGroup();
		//$listUser=$this->userRepository->getUserListSearch($grouppermission, $userData);
		$listUser=$this->memberUsers($userData["id"]);


		$leadsQuery = Lead::select('leads.*', 'call_action_status.title', 'call_action_status.type', 'users.first_name', 'products.product_name', 'call_action_status.icons', 'group_client.name as group_name')
			->join('call_action_status','call_action_status.id','=','leads.status')
			->leftJoin('users','users.id','=','leads.sales_person_id')
			->leftJoin('products','products.id','=','leads.product_id')
			->leftJoin('group_client','group_client.id','=','leads.group_id')
			->where('leads.partner_id',$this->partner_id)
			->where('leads.locked',0)
			->whereIn('call_action_status.type',array(2,4))
			->where(function ($query)  use ($starting_date, $ending_date, $sales_id, $status, $function,$listUser,$UTM_Source, $keyword, $product_id, $tags, $fileamthanh, $group_id){
				/*
				if($fileamthanh!="" && $fileamthanh==1){
					$query->where('logs_call.file_record','!=',"");
				} */
				if($starting_date!=""){
					$query->where('leads.created_at','>=',$starting_date);
					$query->whereOr('leads.updated_at','>=',$starting_date);

				}
				if($ending_date!=""){
					$query->where('leads.created_at','<=',$ending_date);
					$query->whereOr('leads.updated_at','<=',$ending_date);
				} 
				if($sales_id!="" && $sales_id!="0"){
					$query->where('leads.sales_person_id','=',$sales_id);
					$query->whereOr('leads.user_id','=',$sales_id);
				}
				if($status!=""){
					$query->whereIn('leads.status',explode(",",$status));
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
				if($keyword!=""){
					$query->where(function ($query1)  use ($keyword){
						$query1->where('leads.opportunity', 'LIKE', "%{$keyword}%");
						$query1->orWhere('leads.email','LIKE', "%{$keyword}%");
						$query1->orWhere('leads.phone', 'LIKE', "%{$keyword}%");

					});
				}
				if(isset($listUser) && $listUser!="" && count($listUser)>0){
					$query->whereIn('leads.sales_person_id',$listUser);
					$query->orWhereIn('leads.customer_care_id',$listUser);
					$query->orWhere('leads.sales_person_id',0);
				}
			})
			->distinct()->groupBy('leads.id')->orderBy('leads.updated_at', 'desc');
		//	->with( 'country', 'salesTeam');
			//$totalLead=$leadsQuery->count();
			$totalLead=count($leadsQuery->get());
			
			$leadsPage=$leadsQuery->paginate(1000)->appends(request()->query());
			$leadsList=$leadsPage->map( function ( $lead) use ($dateFormat){
                return [
                    'id'           => $lead->id,
					'created_at'   => date($dateFormat,strtotime($lead->created_at)),
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
                    'phone'        => $lead->phone,
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
		// Lead  source
		/*
		$leadGroupSource =Lead::selectRaw('UTM_Source')->where('partner_id',$this->partner_id)->groupBy('UTM_Source')->get()
		->map( function ( $leadGroupSource){
			return [
				'title' => $leadGroupSource->UTM_Source,
				'value' => $leadGroupSource->UTM_Source,
			];
			}
		)->pluck( 'title', 'value')
		->prepend(trans('lead.all'), ''); */

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
		$statusData=CallActionStatus::where('partner_id','=',$this->partner_id)->whereIn('type',array(2,4))->orderBy('position', 'asc')->get()
		->map( function ( $statusList ) {
			return [
				'title' => $statusList->title,
				'value' => $statusList->id,
			];
		} )->pluck( 'title', 'value');
		
		$statusData2=CallActionStatus::select('call_action_status.*')
		->join('group_client_status','group_client_status.client_status_id','=','call_action_status.id')
		->where('call_action_status.partner_id','=',$this->partner_id)
		->whereIn('call_action_status.type',array(2,4))
		->where(function ($query)  use ($group_id){
			if($group_id!="" && $group_id!="0"){
				$query->where('group_client_status.group_client_id','=',$group_id);
			}
		})
		->orderBy('call_action_status.position', 'asc')->groupBy('call_action_status.id')->get()->toArray();
		
		//Status list Kanban
		$statusListKanban=$statusData2;

		$statusList=$statusData->prepend(trans('lead.all'), '');
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
		$groupLead = GroupLead::where("partner_id",$this->partner_id)->orderBy("position", "asc")
			->get()->map(function ($list) {
				return [
					'title' => $list->name,
					'value' =>  $list->id,
				];
			})->pluck( 'title', 'value')->prepend(trans('lead.select_group_client'), '');
			
		$project_id=$this->partner_id; 

		return view( 'user.deal.kanban', compact( 'title', 'leadsList', 'leadsPage', 'salesList', 'productList', 'statusList', 'sourceList', 'leadGroupSource', 'totalLead', 'date_select', 'keyword', 'product_id', 'project_id', 'fileList', 'groupLead', 'statusListKanban'));
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
					return redirect( "deal/create?messenger=Thông tin KH đã tồn tại vui lòng kiểm tra lại thông tin" );
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
		return redirect( "deal" );
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
		array_push($listUser,$user_id);
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
					$query1->orWhereIn('sales_person_id',$listUser);
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
		$grouppermission=GroupUser::getGroup();
		$listUser=$this->userRepository->getUserListSearch($grouppermission, $userData);
		// Add customer
		$listCustomerData=array();
		$customerFieldData=CustomFieldsData::where('item',$lead_id)->get();
		if($customerFieldData){
			foreach($customerFieldData as $listData){
				$listCustomerData[$listData["field_id"]]=$listData["field_value"];
			}
		}
		
		return view( 'user.deal.edit', compact( 'lead', 'tagListData', 'title', 'calls', 'ward', 'district', 'cities', 'linkfull', 'userData', 'listCustomerData', 'tagListSelect') );
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

		return redirect( "deal" );
	}

	public function show( $lead ) {
        $lead = $this->leadRepository->find($lead);
		$title  = trans( 'lead.show' );
		$action = "show";
		$this->generateParams();
		return view( 'user.deal.show', compact( 'title', 'lead', 'action' ) );
	}

	public function delete( $lead ) {
        $lead = $this->leadRepository->find($lead);
		$title = trans( 'lead.delete' );
		$this->generateParams();
        $action = "delete";
		return view( 'user.deal.delete', compact( 'title', 'lead','action' ) );
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

		return redirect( 'deal' );
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
		$callStatus = CallActionStatus::select('call_action_status.*')->join('group_client_status','group_client_status.client_status_id','=','call_action_status.id')
		->where("group_client_status.partner_id",$partner_id)
		->where("group_client_status.group_client_id",$group)
		->whereIn('call_action_status.type',array(2,4))
		->orderBy("call_action_status.position", "asc")
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

		

		$callStatusQueryCache = cache('callStatusQueryCache'.$group_id.$partner_id);
		if(isset($callStatusQueryCache) && $callStatusQueryCache!=""){
			$callStatusQuery=$callStatusQueryCache;
		}else{
			$callStatusQuery = CallActionStatus::where("partner_id",$partner_id)
			->where(function ($query)  use ($group_id){
				if($group_id!="" && $group_id!="0"){
					$query->where('type',$group_id);
				}
			})
			->orderBy("position", "asc")
			->get();
			
			Cache::put('callStatusQueryCache'.$partner_id.$group_id, $callStatusQuery, now()->addMinutes(100));
		} 
		
		$callStatus=$callStatusQuery->map(function ($list) {
			return [
				'id' => $list->id,
				'title' => $list->title,
				'value' =>  $list->id
			];
		})->pluck( 'title', 'value');	


		

		$tagDataCache = cache('tagDataCache'.$partner_id.$group_id);
		if(isset($tagDataCache) && $tagDataCache!=""){
			$tagData=$tagDataCache;
		}else{
			$tagData = Tag::where("partner_id",$partner_id)->where('group_client_id',$group_id)->orderBy("position", "asc")
		->get();
			if(count($tagData)<=0){
			$tagData = Tag::where("partner_id",0)->where('group_client_id',$group_id)->orderBy("position", "asc")
			->get();
			}
			Cache::put('tagDataCache'.$partner_id.$group_id, $tagData, now()->addMinutes(10));
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
		return view( 'user.deal.import', compact( 'title' ) );
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
		return view( 'user.deal.importemail', compact( 'title', 'number_check_email', 'total_email_checked') );
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
		return redirect( "deal/importemail" );
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
		if($partner_id!=19){
			if($data->count() > 0)
			{
				foreach($data->toArray() as $key => $value)
				{
					
					$insert_data=null;
					$lead_code   = $value['ma'];
					$fullname  = $value['ten_khach_hang'];
					$ma_nhom_khach_hang=$value['ma_nhom_khach_hang'];
					$phone   = $value['dien_thoai'];
					$email   = $value['email'];
					$birthday   = $value['ngay_sinh'];
					$gender=$value['gioi_tinh'];
					$website   = $value['website'];
					$fax   = $value['fax'];
					$tax_code   = $value['ma_so_thue'];
					$internal_notes   =$value['mo_ta'];;
					$mobile=$value['nguoi_lien_he_so_dien_thoai'];
					$contactName=$value['nguoi_lien_he'];
					$address_2=$value['dia_chi_2'];
					$address=$value['dia_chi_1'];
					$city_title=$value['tinh_thanh'];
					$district_title=$value['quan_huyen'];
					$ward=$value['phuong_xa'];
					$priority    = 1;
					$utm_source  = "Import";
					$utm_campaign   = "";
					$partner_id  = $partner_id;
					$group_id  = $group_id;
					$product_id   = "";
					$product_name   ="";
					$birthday="";
					if(substr($phone,0,1)!="0"){
						$phone="0".$phone;
					}
					if($phone){
						$lead = new Lead;
						$countLead=Lead::where('phone',$phone)->where('partner_id',$partner_id)->first();
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
							$lead->internal_notes=$internal_notes;
							$lead->contact_name =$contactName;
							$lead->client_name=$fullname;
							$lead->user_id =$userData->id;
							$lead->sales_person_id =$userData->id;
							$lead->tags ="";
							$lead->sales_team_id =0;
							$lead->product_id ="";
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
							$lead->token="";
							$lead->save();
							var_dump($lead);
						}else{
							Lead::where('id', $countLead["id"])->where('partner_id',$partner_id)->update(['opportunity'=>$fullname, 'gender'=>$gender, 'fax'=>$fax, 'lead_code'=>$lead_code, 'email'=>$email, 'group_id'=>$group_id, 'function'=>$utm_source, 'tax_code'=>$tax_code, 'contact_name'=>$fullname, 'status'=>$status, 'city_id'=>$city_id, 'district_id'=>$disctrict_id, 'ward_id'=>$ward_id]);

						}
					}

				}
			}
		}else{
			if($data->count() > 0)
			{
				foreach($data->toArray() as $key => $value)
				{
					$insert_data=null;
					$lead_code   ="";
					$fullname  = $value['ten'];
					$ma_nhom_khach_hang=$value['ma_nhom_khach_hang'];
					$tinh_trang_khach_hang=$value['tinh_trang_khach_hang'];
					$nhomtruong=$value['nhom_truong'];
					$phone   = $value['dien_thoai'];
					$email   = $value['email'];
					$datengayhen=explode("/",$value['ngay_hen']);
					$ngay_hen   = $value['ngay_hen'];//$datengayhen[2]."-".$datengayhen[1]."-".$datengayhen[0];
					$congty   = $value['cong_ty'];
					$muc_tieu   = $value['muc_tieu'];
					$ngaysinh   = $value['ngay_sinh'];
					
					$sales_name   = $value['nhan_vien_sale'];
					$internal_notes   =$value['thong_tin_them'];;
					$contactName=$fullname;
					$san_pham=$value['san_pham'];
					$product_id   = 0;
					$product_name   =$value['san_pham'];
					if($san_pham!=""){
					$product_id=$this->productRepository->findByField('product_name',$san_pham)->pluck('id')->first();
					}
					
					$group_name=$value['ma_nhom_khach_hang'];
					$hinh_thuc_thuc_hoc_phi=$value['hinh_thuc_thuc_hoc_phi'];
					$chi_nhanh   = $value['chi_nhanh'];
					$brand_id=0;
					if($chi_nhanh!=""){
						$brand=Brand::where('partner_id',$partner_id)->where('name',$chi_nhanh)->first();
						$brand_id=$brand["id"];
					}
					$priority    = 1;
					$utm_source  = $value['utm_source'];
					$group_id  = $group_id;
					
					if(substr($phone,0,1)!="0"){
						$phone="0".$phone;
					}
					if($fullname!=""){
						$lead = new LeadsTemp;
							$lead->opportunity = $fullname;
							$lead->partner_id = $partner_id;
							$lead->ngay_hen = $ngay_hen;
							$lead->nhom_truong = $nhomtruong;
							$lead->lead_code = $lead_code;
							$lead->email = $email;
							$lead->ngay_sinh=$ngaysinh;
							$lead->phone =  $phone;
							$lead->function=$utm_source;
							$lead->chi_nhanh=$chi_nhanh;
							$lead->hinh_thuc_thanh_toan=$hinh_thuc_thuc_hoc_phi;
							$lead->sales_name=$sales_name;
							$lead->tinh_trang_khach_hang=$tinh_trang_khach_hang;

							$lead->internal_notes=$internal_notes;
							$lead->contact_name =$contactName;
							$lead->cong_ty=$congty;
							$lead->muc_tieu=$muc_tieu;
							$lead->user_id =$userData->id;
							$lead->sales_team_id =0;
							$lead->product_id =$product_id;
							$lead->product_name=$san_pham;
							$lead->brand_id=$brand_id;
							$lead->UTM_Source =$utm_source;
							$lead->status=$status;
							$lead->save();
					}

				}
			}
		}
		return redirect( "lead" );
	}
	public function postImportTempToMain( Request $request ) {
		$userData=$this->userRepository->getUser();
		$data = LeadsTemp::where('convert_data',0)->get();
		$status=$request->status;
		if($status==""){
			$status=0;
		}
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
				$internal_notes=$listData['internal_notes'];;
				$user_id=$listData['user_id'];;
				$UTM_Source=$listData['UTM_Source'];;
				$ngay_hen=$listData['ngay_hen'];;
				$muc_tieu=$listData['muc_tieu'];;
				$hinh_thuc_thanh_toan=$listData['hinh_thuc_thanh_toan'];;
				$sales_name=$listData['sales_name'];;
				$congty=$listData['cong_type'];;
				$ngaysinh=$listData['ngay_sinh'];;
				$status=$listData['status'];
				$cong_ty=$listData['cong_ty'];
				$brand_id=$listData['brand_id'];
				$product_name=$listData['product_name'];
				$product_id=$listData['product_id'];
				$tinh_trang_khach_hang=$listData['tinh_trang_khach_hang'];;
				$nhom_truong=$listData['nhom_truong'];
				$utm_source=$listData['UTM_Source'];
				$priority    = 1;
				$utm_campaign   = "";

				$partner_id  = $partner_id;
				if(substr($phone,0,1)!="0"){
					$phone="0".$phone;
				}
				if($phone){
					$lead = new Lead;
					$countLead=Lead::where('phone',$phone)->where('partner_id',$partner_id)->first();		
					
					if($countLead=="" || $countLead==null){
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
						$lead->birth_day=$birthday;
						$lead->phone =  $phone;
						$lead->function=$utm_source;
						$lead->cookie_id="";
						$lead->status=$status;
						
						$statusKhachHang=CallActionStatus::where('partner_id',$partner_id)->whereIn('type',array(2,4))->where('title',$tinh_trang_khach_hang)->first();
						$status=0;
						if($statusKhachHang){
							$status=$statusKhachHang["id"];
						} 
						$lead->internal_notes=$internal_notes;
						$lead->contact_name =$fullname;
						$lead->client_name=$cong_ty;
						$lead->user_id =$userData->id;
						
						$lead->tags ="";
						$lead->sales_team_id =0;
						$lead->product_id =$product_id;
						$lead->product_name =$product_name;
						$lead->brand_id =$brand_id;
						$lead->UTM_Source =$utm_source;
						$lead->UTM_Campaign=$utm_campaign;
						$sales=User::where('partner_id',$partner_id)->where('nick_name',$sales_name)->first();
						if($sales){
							$lead->sales_person_id =$sales["id"];
						}else{
							$lead->sales_person_id =$userData->id;
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
						$lead_id=$lead->id;

					}else{
						$lead_id=$countLead["id"];
						$statusKhachHang=CallActionStatus::where('partner_id',$partner_id)->whereIn('type',array(2,4))->where('title',$tinh_trang_khach_hang)->first();
						$status=0;
						if($statusKhachHang){
							$status=$statusKhachHang["id"];
						}
						Lead::where('id',$lead_id)->update(array('UTM_Source'=>$UTM_Source, 'function'=>$UTM_Source, 'status_title'=>$tinh_trang_khach_hang, 'status'=>$status));
					}
					if($product_id!="" && $product_id!=0){
					//	$productinsert=array('product_id'=>$product_id, 'lead_id'=>$lead_id, 'status'=>1);
					//	LeadProduct::insert($productinsert);
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
				LeadsTemp::where('id', $listData["id"])->update(['convert_data'=>1]);
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
		$type_status = addslashes($request->status);
		$function  = addslashes($request->function);
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
		$userData=$this->userRepository->getUser();
        $this->partner_id=$userData->partner_id;
		//$listUser=$this->userRepository->getAllStaffOfUser(0);
		$listUser=$this->memberUsers($userData["id"]);

		
		$leadsQuery = Lead::select('leads.*', 'call_action_status.title as statustitle', 'call_action_status.type', 'users.first_name', 'users.last_name', 'products.product_name', 'call_action_status.icons', 'group_client.name as group_name')
			->leftJoin('call_action_status','call_action_status.id','=','leads.status')
			->leftJoin('users','users.id','=','leads.sales_person_id')
			->leftJoin('products','products.id','=','leads.product_id')
			->leftJoin('group_client','group_client.id','=','leads.group_id')
			->where('leads.partner_id',$this->partner_id)
			->where(function ($query)  use ($starting_date, $ending_date, $sales_id, $status, $function, $listUser,  $keyword, $product_id, $tags, $group_id, $type_status, $UTM_Source){
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
				if($type_status!=""){
					$query->where('leads.status',$type_status);
				}
			})
			->orderBy('leads.id', 'DESC')->get()->toArray();
			
			$customer_data=$leadsQuery;
			$customer_array[] = array('ID', 'Lead Name', 'Ngày tạo', 'Email', 'Phone', 'Sales chăm sóc', 'Source direct', 'UTM_Source', 'UTM_Campaign', 'UTM_Medium', 'Tags', 'Nhóm', 'Tình trạng', 'Sản phẩm', 'Loại cuộc gọi');
			$i=0;
			foreach($customer_data as $customer)
			{
				$i++;
				$customer_array[] = array(
					'ID'  => $i,
					'Lead Name'   => $customer["opportunity"],
					'Ngày tạo'   => date($dateFormat,strtotime($customer["created_at"])),
					'Email'    => $customer["email"],
					'Phone'  => $customer["phone"],
					'Sales'   => $customer["first_name"]." ".$customer["last_name"],
					'Source direct'   => $customer["function"],
					'UTM_Source'   => $customer["UTM_Source"],
					'UTM_Campaign'   => $customer["UTM_Campaign"],
					'UTM_Medium'   => $customer["UTM_Medium"],
					'Tags'   => $customer["tags"],
					'Nhóm'   => $customer["group_name"],
					'Tình trạng'   => $customer["statustitle"],
					'Sản phẩm'   => $customer["product_name"],
					'Loại cuộc gọi'=> LogsCall::select('status')->where('lead_id',$customer["id"])->orderBy('id','desc')->first(),
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
	   $tags=$request->input('tags');
	   if ($logtext && $lead_id) {
			$leadDetail = Lead::where("id",$lead_id)->first();
			if($leadDetail ){
				$tagsLead=$leadDetail["tags"];
				// Add to log
				if($request->input('tags')!=""){
					$tagsLead=$leadDetail["tags"].", ".$tags;
				}
				$listPhoto=array();
				$photo="";
				
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
					'tags'=>$tagsLead,
					'created_at'=> date("Y-m-d H:i:s"),
					'lead_id'=>$lead_id,
					'photos'=>$photo
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


	public function plan(Request $request) {

		$title = "Báo cáo kế hoạch CSKH";
		$dateFormat = config('settings.date_format');
		$dateFormat = "d/m/Y H:i:s";
		$userData=$this->userRepository->getUser();
		$processstatus=0;
		$date  = addslashes($request->starting_date);
		$sales_id = addslashes($request->sales_id) ;
		
		$start=1;
		if(isset($page) && $page>1){
			$start=$page;
		}
		$limit=20; 
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

		//$grouppermission=GroupUser::getGroup();
		//$listUser=$this->userRepository->getUserListSearch($grouppermission, $userData);

		$grouppermission=GroupUser::getGroup();

		//$listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
		$listUser=$this->memberUsers($userData["id"]);
		if(!isset($locked) || $locked==""){
			$locked=0;
		} 

		$this->generateParams();
		//$listUser=$this->userRepository->getAllUserOnPartner($this->partner_id)

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
		//->where("group_client.type_client",2)
		/*
		$callStatusQueryCache = cache('callStatusQueryCache3'.$partner_id);
		if(isset($callStatusQueryCache) && $callStatusQueryCache!=""){
			$groupStatus=$callStatusQueryCache;
		}else{ 
			$groupStatus = CallActionStatus::select('call_action_status.*')->join('group_client','group_client.id','=','call_action_status.type')->where("call_action_status.partner_id",$partner_id)
			->orderBy("call_action_status.position", "asc")
			->get();
			Cache::put('callStatusQueryCache3'.$partner_id, $groupStatus, now()->addMinutes(100));
		} */

		$totalStatusGroupDetail=CallActionStatus::select('call_action_status.*', DB::raw('count(leads.id) as totalStatusGroup'), DB::raw('DATE_FORMAT(leads.updated_at, "%Y-%m-%d") as createdate'))
		->join('leads', 'leads.status', '=', 'call_action_status.id')
		->where('leads.partner_id',$partner_id)
		->where('call_action_status.status',1)
		->where('call_action_status.type_client',2)
		->where('call_action_status.partner_id',$partner_id)
        ->where(function ($query)  use ($starting_date, $ending_date){
            if($starting_date!=""){
				//$query->where(function ($query1) use($starting_date){
				$query->where('leads.updated_at','>=',$starting_date);
				//});
			}
			if($ending_date!=""){
				$query->where('leads.updated_at','<=',$ending_date);
			}
        })->where(function ($query)  use ($sales_id){
			if($sales_id!="" && $sales_id!="0"){
				$query->where(function ($query1)  use ($sales_id){
					$query1->where('leads.sales_person_id','=',$sales_id);
					$query1->orWhere('leads.customer_care_id','=',$sales_id);
					$query1->orWhere('leads.user_id','=',$sales_id);

				});
			}
		})
		->groupBy('call_action_status.id')->orderBy('call_action_status.position','asc')->get()->toArray();

		$totalStatusGroupDetailLead=CallActionStatus::select('call_action_status.*', DB::raw('count(leads.id) as totalStatusGroup'), DB::raw('DATE_FORMAT(leads.updated_at, "%Y-%m-%d") as createdate'))
		->join('leads', 'leads.status', '=', 'call_action_status.id')->where('leads.partner_id',$partner_id)
		->where('call_action_status.status',1)
		->where('call_action_status.type_client',1)
		->where('call_action_status.partner_id',$partner_id)
        ->where(function ($query)  use ($starting_date, $ending_date){
            if($starting_date!=""){
				//$query->where(function ($query1) use($starting_date){
				$query->where('leads.updated_at','>=',$starting_date);
				//});
			}
			if($ending_date!=""){
				$query->where('leads.updated_at','<=',$ending_date);
			}
        })->where(function ($query)  use ($sales_id){
			if($sales_id!="" && $sales_id!="0"){
				$query->where(function ($query1)  use ($sales_id){
					$query1->where('leads.sales_person_id','=',$sales_id);
					$query1->orWhere('leads.customer_care_id','=',$sales_id);
					$query1->orWhere('leads.user_id','=',$sales_id);

				});
			}
		})
		->groupBy('call_action_status.id')->orderBy('call_action_status.position','asc')->get()->toArray();

		$groupStatusDetail=CallActionStatus::select('call_action_status.*', DB::raw('count(leads.id) as tagOnDay'), DB::raw('DATE_FORMAT(leads.updated_at, "%Y-%m-%d") as createdate'))->join('leads', 'leads.status', '=', 'call_action_status.id')->where('leads.partner_id',$partner_id)
		->where('call_action_status.status',1)
        ->where(function ($query)  use ($starting_date, $ending_date){
            if($starting_date!=""){
				//$query->where(function ($query1) use($starting_date){
				$query->where('leads.updated_at','>=',$starting_date);
				//});
			}
			if($ending_date!=""){
				$query->where('leads.updated_at','<=',$ending_date);
			}
        })->where(function ($query)  use ($sales_id){
			if($sales_id!="" && $sales_id!="0"){
				$query->where(function ($query1)  use ($sales_id){
					$query1->where('leads.sales_person_id','=',$sales_id);
					$query1->orWhere('leads.customer_care_id','=',$sales_id);
					$query1->orWhere('leads.user_id','=',$sales_id);

				});
			}
		})
		->groupBy('createdate', 'call_action_status.id')->get()->toArray();
        $listNumberDate=null;
        if($groupStatusDetail){
            foreach($groupStatusDetail as $listData){
                $listNumberDate[$listData["id"]][$listData["createdate"]]=$listData["tagOnDay"];
            }
        }
		
		if(isset($listUser) && $listUser!=""){
			$salesList=User::join('partner_user','partner_user.user_id','=','users.id')
						->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
						->where('partner_user.partner_id','=',$partner_id)
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
			->where('partner_user.partner_id','=',$partner_id)
			->get()
			->map( function ( $salesList ) {
				return [ 
					'title' => $salesList->first_name." ".$salesList->last_name,
					'value' => $salesList->id,
				];
			} )->pluck( 'title', 'value')
			->prepend(trans('lead.all'), '');
		}
		$dateNumber = (int)(abs(strtotime($ending_date) - strtotime($starting_date))/(60*60*24));
		return view( 'user.deal.plan', compact( 'title', 'dateNumber', 'salesList', 'listNumberDate', 'starting_date', 'totalStatusGroupDetail', 'totalStatusGroupDetailLead')); 
	}
}
