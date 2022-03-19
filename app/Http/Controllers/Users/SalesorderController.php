<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Common;
use App\Http\Controllers\UserController;
use App\Http\Requests\SaleorderRequest;
use App\Mail\SendQuotation;
use App\Repositories\CompanyRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\LeadRepository;

use App\Repositories\EmailRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\OptionRepository;
use App\Repositories\ProductRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\QuotationTemplateRepository;
use App\Repositories\SalesOrderRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Saleorder;
use App\Models\Tag;
use App\Models\Logs;
use App\Models\GroupUser;
use App\Models\CallActionStatus;
use App\Models\GroupLead;
use App\Models\SaleorderProduct;
use App\Models\Branch;
use App\Models\Lead;


use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Efriandika\LaravelSettings\Facades\Settings;

use App\Models\User;

use Yajra\Datatables\Datatables;

class SalesorderController extends UserController
{
    /**
     * @var QuotationRepository
     */
    private $salesOrderRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var SalesTeamRepository
     */
    private $salesTeamRepository;
    /**
     * @var ProductRepository
     */
    private $leadRepository;
    private $productRepository;
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    /**
     * @var QuotationTemplateRepository
     */
    private $quotationTemplateRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    private $invoiceRepository;

    private $emailRepository;

    private $customerRepository;

    /**
     * @param SalesOrderRepository $salesOrderRepository
     * @param UserRepository $userRepository
     * @param SalesTeamRepository $salesTeamRepository
     * @param ProductRepository $productRepository
     * @param CompanyRepository $companyRepository
     * @param QuotationTemplateRepository $quotationTemplateRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(SalesOrderRepository $salesOrderRepository,
                                UserRepository $userRepository,
                                SalesTeamRepository $salesTeamRepository,
                                ProductRepository $productRepository,
                                CompanyRepository $companyRepository,
                                QuotationTemplateRepository $quotationTemplateRepository,
                                OptionRepository $optionRepository,
                                InvoiceRepository $invoiceRepository,
                                CustomerRepository $customerRepository,
                                EmailRepository $emailRepository,
                                LeadRepository $leadRepository
    )
    {

        $this->middleware('authorized:sales_orders.read', ['only' => ['index', 'data']]);
        $this->middleware('authorized:sales_orders.write', ['only' => ['create', 'store', 'update', 'edit']]);
        $this->middleware('authorized:sales_orders.delete', ['only' => ['delete']]);

        parent::__construct();

        $this->salesOrderRepository = $salesOrderRepository;
        $this->userRepository = $userRepository;
        $this->salesTeamRepository = $salesTeamRepository;
        $this->productRepository = $productRepository;
        $this->companyRepository = $companyRepository;
        $this->quotationTemplateRepository = $quotationTemplateRepository;
        $this->optionRepository = $optionRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->customerRepository = $customerRepository;
        $this->emailRepository = $emailRepository;
        $this->leadRepository = $leadRepository;
        view()->share('type', 'sales_order');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = trans('sales_order.sales_orders');
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
       // $listUser=$this->userRepository->getAllStaffOfUser($userData->id);

       $grouppermission=GroupUser::getGroup();
       $listUser=$this->userRepository->getUserListSearch($grouppermission, $userData);
       
       // array_push($listUser, $userData->id);
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
        //Status list
        /*
		$statusList=CallActionStatus::where('partner_id','=',$this->partner_id)->whereIn('type',array(2,4))->orderBy('position', 'asc')->get()
		->map( function ( $statusList ) {
			return [
				'title' => $statusList->title,
				'value' => $statusList->id,
			];
		} )->pluck( 'title', 'value')
		->prepend(trans('lead.all'), ''); */
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
		$groupLead = GroupLead::where("partner_id",$this->partner_id)->orderBy("position", "asc")
			->get()->map(function ($list) {
				return [
					'title' => $list->name,
					'value' =>  $list->id,
				];
            })->pluck( 'title', 'value')->prepend(trans('lead.select_group_client'), '');

        $salesOrderQuery = Saleorder::select('sales_orders.*', 'users.first_name', 'leads.opportunity as customer_name')
			->join('sales_order_products','sales_order_products.saleorder_id','=','sales_orders.id')
            ->leftJoin('users','users.id','=','sales_orders.sales_person_id')
            ->join('leads','leads.id','=','sales_orders.lead_id')
			->leftJoin('products','products.id','=','sales_order_products.product_id')
			->where('sales_orders.partner_id',$this->partner_id)
			->where(function ($query)  use ($starting_date, $ending_date,$status, $function,$UTM_Source, $keyword, $product_id, $tags, $group_id){
				
                if($starting_date!=""){
					$query->where('sales_orders.date_ship','>=',$starting_date);
				}
				if($ending_date!=""){
					$query->where('sales_orders.date_ship','<=',$ending_date);
                }  
				if($status!=""){
					$query->whereIn('sales_orders.status_client',explode(",",$status));
				}
				if($product_id!="" && $product_id!="0"){
					$query->where('products.product_id','=',$product_id);
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
				
			})->where(function ($query1)  use ($keyword){
                if($keyword!=""){
                        $query1->where('leads.opportunity', 'LIKE', "%{$keyword}%");
                        $query1->orWhere('sales_orders.sale_number','LIKE', "%{$keyword}%");
						$query1->orWhere('leads.email','LIKE', "%{$keyword}%");
						$query1->orWhere('leads.phone', 'LIKE', "%{$keyword}%");
				}
            })
            ->where(function ($query2)  use ($sales_id){
                if($sales_id!=""){
					$query2->where('sales_orders.sales_person_id','=',$sales_id);
					$query2->whereOr('sales_orders.user_id','=',$sales_id);
				}
            })
            ->where(function ($query3)  use ($listUser,$userData){
                if($userData->user_id!=1 && $listUser!=""){
                        $query3->whereIn('sales_orders.sales_person_id',$listUser);
                        $query3->orWhereIn('sales_orders.user_id',$listUser);
                }
            })
			->distinct()
			//->groupBy('leads.phone')
			->orderBy('sales_orders.created_at', 'DESC');
			//$totalLead=$leadsQuery->count();
			$totalOrder=count($salesOrderQuery->get());
			$salesorderPage=$salesOrderQuery->paginate(20)->appends(request()->query());
			$salesorderList=$salesorderPage->map( function ( $salesorder) use ($dateFormat){
                return [
                    'id'           => $salesorder->id,
					'created_at'   => date($dateFormat,strtotime($salesorder->created_at)),
					'sale_number' => $salesorder->sale_number,
                    'customer_name' => $salesorder->customer_name,
                    'lead_id' => $salesorder->lead_id,
					'date_ship'  => $salesorder->date_ship,
					'total'  => $salesorder->total,
                    'payment'  => $salesorder->first_name,
                    'shipping_fee'  => $salesorder->shipping_fee,
                    'shipping_term'  => $salesorder->shipping_term,
                    'tax_amount'  => $salesorder->tax_amount,
                    'grand_total'  => $salesorder->grand_total,
                    'final_price'   => $salesorder->final_price,      
                    'discount'  => $salesorder->discount,
                    'is_invoice_list'   => $salesorder->is_invoice_list,    
                    'date_exp'  => $salesorder->date_exp,
              
                ];
			}
        );
        $this->generateParams();
        return view( 'user.sales_order.index', compact( 'title', 'salesorderList', 'salesorderPage', 'salesList', 'productList', 'sourceList', 'leadGroupSource', 'totalOrder', 'date_select', 'keyword', 'product_id', 'groupLead'));
    }


        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function kanban(Request $request)
    {
        $title = trans('sales_order.sales_orders');
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
       // $listUser=$this->userRepository->getAllStaffOfUser($userData->id);
        $grouppermission=GroupUser::getGroup();
        $listUser=$this->userRepository->getUserListSearch($grouppermission, $userData);
       // array_push($listUser, $userData->id);
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
		//Status list
		$statusList=CallActionStatus::where('partner_id','=',$this->partner_id)->orderBy('position', 'asc')->get()
		->map( function ( $statusList ) {
			return [
				'title' => $statusList->title,
				'value' => $statusList->id,
			];
		} )->pluck( 'title', 'value')
		->prepend(trans('lead.all'), '');
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
		$groupLead = GroupLead::where("partner_id",$this->partner_id)->orderBy("position", "asc")
			->get()->map(function ($list) {
				return [
					'title' => $list->name,
					'value' =>  $list->id,
				];
            })->pluck( 'title', 'value')->prepend(trans('lead.select_group_client'), '');
            

        $salesOrderQuery = Saleorder::select('sales_orders.*', 'users.first_name', 'leads.opportunity as customer_name', 'products.product_name as product_name')
			->join('sales_order_products','sales_order_products.saleorder_id','=','sales_orders.id')
            ->leftJoin('users','users.id','=','sales_orders.sales_person_id')
            ->join('leads','leads.id','=','sales_orders.lead_id')
			->leftJoin('products','products.id','=','sales_order_products.product_id')
			->where('sales_orders.partner_id',$this->partner_id)
			->where(function ($query)  use ($starting_date, $ending_date,$status, $function,$listUser,$UTM_Source, $keyword, $product_id, $tags, $group_id){
				
				if($starting_date!=""){
					$query->where('sales_orders.date_ship','>=',$starting_date);
				}
				if($ending_date!=""){
					$query->where('sales_orders.date_ship','<=',$ending_date);
                } 
                if($listUser!=""){
					$query->whereIn('sales_orders.sales_person_id',$listUser);
                } 
				if($status!=""){
					$query->whereIn('sales_orders.status',explode(",",$status));
				}
				if($product_id!="" && $product_id!="0"){
					$query->where('products.id','=',$product_id);
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
				
			})->where(function ($query1)  use ($keyword){
                if($keyword!=""){
                        $query1->where('leads.opportunity', 'LIKE', "%{$keyword}%");
                        $query1->orWhere('sales_orders.sale_number','LIKE', "%{$keyword}%");
						$query1->orWhere('leads.email','LIKE', "%{$keyword}%");
						$query1->orWhere('leads.phone', 'LIKE', "%{$keyword}%");
				}
            })
            ->where(function ($query2)  use ($sales_id){
                if($sales_id!=""){
					$query2->where('sales_orders.sales_person_id','=',$sales_id);
					$query2->whereOr('sales_orders.user_id','=',$sales_id);
				}
            })->where(function ($query3)  use ($listUser,$userData){
                if($userData->user_id>1 && $listUser!=""){
                        $query3->whereIn('sales_orders.sales_person_id',$listUser);
                        $query3->orWhereIn('sales_orders.user_id',$listUser);
                }
            })
			->distinct()
			//->groupBy('leads.phone')
			->orderBy('sales_orders.created_at', 'DESC');
			//$totalLead=$leadsQuery->count();
            $totalOrder=count($salesOrderQuery->get());
            $salesorderPage=$salesOrderQuery->get();
		//	$salesorderPage=$salesOrderQuery->paginate(1000)->appends(request()->query());
			$salesorderList=$salesorderPage->map( function ( $salesorder) use ($dateFormat){
                return [
                    'id'           => $salesorder->id,
					'sale_number' => $salesorder->sale_number,
                    'customer_name' => $salesorder->customer_name,
                    'lead_id' => $salesorder->lead_id,
					'date_ship'  => $salesorder->date_ship,
					'total'  => $salesorder->total,
                    'payment'  => $salesorder->first_name,
                    'shipping_fee'  => $salesorder->shipping_fee,
                    'shipping_term'  => $salesorder->shipping_term,
                    'tax_amount'  => $salesorder->tax_amount,
                    'grand_total'  => $salesorder->grand_total,
                    'final_price'   => $salesorder->final_price,      
                    'discount'  => $salesorder->discount,
                    'is_invoice_list'   => $salesorder->is_invoice_list,    
                    'date_exp'  => $salesorder->date_exp,
                    'product_name'=> $salesorder->product_name,
                    'status_client'=> $salesorder->status_client
              
                ];
			}
        );
        $this->generateParams();
        return view( 'user.sales_order.kanban', compact( 'title', 'salesorderList', 'salesorderPage', 'salesList', 'productList', 'statusList', 'sourceList', 'leadGroupSource', 'totalOrder', 'date_select', 'keyword', 'product_id', 'groupLead'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $title = trans('sales_order.create');
        $lead_id  = addslashes($request->lead_id);
        $userData=$this->userRepository->getUser();
        $productsLead=null;
        if(isset($lead_id) && $lead_id!="" && $lead_id!=0){
            $leadDetail=Lead::where('id',$lead_id)->first();
            if($leadDetail && $leadDetail["product_id"]!=0 &&  $leadDetail["product_id"]!=""){
                $productsLead = Product::where('id',$leadDetail["product_id"])->first();
            }
        }
    
        $this->generateParams();

        return view('user.sales_order.create', compact('title', 'lead_id', 'userData', 'productsLead'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SaleorderRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(SaleorderRequest $request)
    {
        $user=$this->userRepository->getUser();
        $partner_id=$user->partner_id;
        if(empty($request->qtemplate_id)){
            $request->merge(['qtemplate_id'=>0]);
        }
        $saleorder = $this->salesOrderRepository->getAll()->withDeleteList()->get()->count();
        if($saleorder == 0){
            $total_fields = 0;
        }else{
            $total_fields = $this->salesOrderRepository->getAll()->withDeleteList()->get()->last()->id;
        }
        $start_number = Settings::get('sales_start_number');

        $saleorder_no = "FCRM" . (is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields : 0) + 1);
        $arrayupdate=$request->all();
        if($request->sales_person_id=="" || $request->sales_person_id==0){
			$arrayupdate["sales_person_id"]=$user->id;
        }
        if($request->date_ship=="" || $request->date_ship==0 || $request->date_ship=='0000-00-00'){
			$arrayupdate["date_ship"]=date("Y-m-d");
        }
        if($request->date_exp=="" || $request->date_exp==0 || $request->date_ship=='0000-00-00'){
			$arrayupdate["date_exp"]=date("Y-m-d",strtotime('+5 days'));
        }
        $arrayupdate["sale_number"]=$saleorder_no;
        $arrayupdate["is_delete_list"]=0;
        $arrayupdate["is_invoice_list"]=0;
        $arrayupdate["partner_id"]=$partner_id;
        if(isset($arrayupdate["status_client"]) && $arrayupdate["status_client"]!="" && $arrayupdate["lead_id"]!=""){
            Lead::where('id',$arrayupdate["lead_id"])->where('partner_id',$partner_id)->update(['status'=>$arrayupdate["status_client"]]);
        }
        unset($arrayupdate["taxestotal"]);
        $this->salesOrderRepository->createSalesOrder($arrayupdate);
        //Saleorder::insert($arrayupdate);
        if ($request->status == trans('sales_order.draft_salesorder')){
            return redirect("sales_order/draft_salesorders");
        }else{
            return redirect("sales_order");
        }
    }

    public function edit($saleorder)
    {
        $saleorder = $this->salesOrderRepository->find($saleorder);
        $title = trans('sales_order.edit');
        $userData=$this->userRepository->getUser();
        $this->generateParams();

        $this->emailRecipients($saleorder->lead_id);
        /*
        $sales_team = $this->salesTeamRepository->find($saleorder->sales_team_id);
        $team_leader = $this->userRepository->all()->where('id',$sales_team->team_leader)->pluck('full_name','id')->toArray();
        $sales_team_members = $sales_team->members->pluck('full_name','id')->toArray();
        $main_staff = $team_leader+$sales_team_members; */
        return view('user.sales_order.edit', compact('title', 'saleorder','userData'));
    }

    public function update(SaleorderRequest $request, $saleorder)
    {
        if(empty($request->qtemplate_id)){
            $request->merge(['qtemplate_id'=>0]);
        }
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $saleorder_id = $saleorder;
        $arrayupdate=$request->all();
        unset($arrayupdate["taxestotal"]);
        if($request->date_ship=="" || $request->date_ship==0 || $request->date_ship=='0000-00-00'){
			$arrayupdate["date_ship"]=date("Y-m-d");
        }
        if($request->date_exp=="" || $request->date_exp==0 || $request->date_ship=='0000-00-00'){
			$arrayupdate["date_exp"]=date("Y-m-d",strtotime('+5 days'));
        }
        $this->salesOrderRepository->updateSalesOrder($arrayupdate,$saleorder_id);
        if(isset($arrayupdate["status_client"]) && $arrayupdate["status_client"]!="" && $arrayupdate["lead_id"]!=""){
            Lead::where('id',$arrayupdate["lead_id"])->where('partner_id',$partner_id)->update(['status'=>$arrayupdate["status_client"]]);
        }
        if ($request->status == trans('sales_order.draft_salesorder')){
            return redirect("sales_order/draft_salesorders");
        }else{
            return redirect("sales_order");
        }
    }

    public function show($saleorder)
    {
        $saleorder = $this->salesOrderRepository->find($saleorder);
        $title = trans('sales_order.show');
        $this->generateParams();
        $this->emailRecipients($saleorder->lead_id);
        $action = 'show';
        return view('user.sales_order.show', compact('title', 'saleorder','action'));
    }

    public function delete($saleorder)
    {
        $saleorder = $this->salesOrderRepository->find($saleorder);
        $title = trans('sales_order.delete');
        $this->generateParams();
        return view('user.sales_order.delete', compact('title', 'saleorder'));
    }

    public function destroy($saleorder)
    {
        $saleorder = $this->salesOrderRepository->find($saleorder);
        $saleorder->update(['is_delete_list' => 1]);
        return redirect('salesorder_delete_list');
    }

    public function data(Datatables $datatables)
    {
        $dateFormat = config('settings.date_format');
        $sales_order = $this->salesOrderRepository->getAll()
            ->where('status',trans('sales_order.send_salesorder'))
            ->with('user', 'customer')
            ->get()
            ->map(function ($saleOrder) use ($dateFormat){
                return [
                    'id' => $saleOrder->id,
                    'sale_number' => $saleOrder->sale_number,
                    'customer' => isset($saleOrder->customer) ?$saleOrder->customer->full_name : '',
                    'final_price' => $saleOrder->final_price,
                    'date' => date($dateFormat, strtotime($saleOrder->date)),
                    'exp_date' => date($dateFormat, strtotime($saleOrder->exp_date)),
                    'payment_term' => $saleOrder->payment_term,
                    'status' => $saleOrder->status
                ];
            });
        return $datatables->collection($sales_order)
            ->addColumn(
                'expired',
                '@if(strtotime(date("m/d/Y"))>strtotime("+".$payment_term." ",strtotime($exp_date)))
                                        <i class="fa fa-bell-slash text-danger" title="{{trans(\'sales_order.salesorder_expired\')}}"></i> 
                                     @else
                                      <i class="fa fa-bell text-warning" title="{{trans(\'sales_order.salesorder_will_expire\')}}"></i> 
                                     @endif'
            )
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'sales_orders.write\']) || Sentinel::inRole(\'admin\') )
                                        
                                     @endif
                                     @if(Sentinel::getUser()->hasAccess([\'sales_orders.read\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'sales_order/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                     <a href="{{ url(\'sales_order/\' . $id . \'/print_quot\' ) }}" title="{{ trans(\'table.print\') }}">
                                            <i class="fa fa-fw fa-print text-primary "></i>  </a>
                                    @endif
                                     @if(Sentinel::getUser()->hasAccess([\'sales_orders.delete\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'sales_order/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                     @endif')
            ->removeColumn('id')
            ->rawColumns(['actions','expired'])->make();
    }

    public function draftIndex(){
        $title=trans('sales_order.draft_salesorder');
        return view('user.sales_order.draft_salesorders', compact('title'));
    }
    public function draftSalesOrders(Datatables $datatables)
    {
        $dateFormat = config('settings.date_format');
        $sales_order = $this->salesOrderRepository->getAll()
            ->where('status',trans('sales_order.draft_salesorder'))
            ->with('user', 'customer')
            ->get()
            ->map(function ($saleOrder) use ($dateFormat){
                return [
                    'id' => $saleOrder->id,
                    'sale_number' => $saleOrder->sale_number,
                    'customer' => isset($saleOrder->customer) ?$saleOrder->customer->full_name : '',
                    'final_price' => $saleOrder->final_price,
                    'date' => date($dateFormat, strtotime($saleOrder->date)),
                    'exp_date' => date($dateFormat, strtotime($saleOrder->exp_date)),
                    'payment_term' => $saleOrder->payment_term,
                    'status' => $saleOrder->status
                ];
            });
        return $datatables->collection($sales_order)
            ->addColumn(
                'expired',
                '@if(strtotime(date("m/d/Y"))>strtotime("+".$payment_term." ",strtotime($exp_date)))
                                        <i class="fa fa-bell-slash text-danger" title="{{trans(\'sales_order.salesorder_expired\')}}"></i> 
                                     @else
                                      <i class="fa fa-bell text-warning" title="{{trans(\'sales_order.salesorder_will_expire\')}}"></i> 
                                     @endif'
            )
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'sales_orders.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'sales_order/\' . $id . \'/edit\' ) }}"  title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning "></i>  </a>
                                     @endif
                                     @if(Sentinel::getUser()->hasAccess([\'sales_orders.read\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'sales_order/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                     <a href="{{ url(\'sales_order/\' . $id . \'/print_quot\' ) }}" title="{{ trans(\'table.print\') }}">
                                            <i class="fa fa-fw fa-print text-primary "></i>  </a>
                                    @endif
                                     @if(Sentinel::getUser()->hasAccess([\'sales_orders.delete\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'sales_order/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                     @endif')
            ->removeColumn('id')
            ->rawColumns(['actions','expired'])->make();
    }

    public function ajaxQtemplatesProducts($qtemplate)
    {
        $qtemplateProduct = $this->quotationTemplateRepository->find($qtemplate);
        $templateProduct = [];
        foreach ($qtemplateProduct->qTemplateProducts as $product){
            $templateProduct[] = $product;
        }
        return $templateProduct;
    }


    public function printQuot($saleorder)
    {
        $saleorder = $this->salesOrderRepository->find($saleorder);
        $filename = 'SalesOrder-' . $saleorder->sale_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4','landscape');
        $pdf->loadView('saleorder_template.'.Settings::get('saleorder_template'), compact('saleorder'));
        return $pdf->download($filename . '.pdf');
    }

    public function ajaxCreatePdf($saleorder)
    {
        $saleorder = $this->salesOrderRepository->find($saleorder);
        $filename = 'SalesOrder-' . $saleorder->sale_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4','landscape');
        $pdf->loadView('saleorder_template.'.Settings::get('saleorder_template'), compact('saleorder'));
        $pdf->save('./pdf/' . $filename . '.pdf');
        $pdf->stream();
        echo url("pdf/" . $filename . ".pdf");

    }

    public function sendSaleorder(Request $request)
    {
        $email_subject = $request->email_subject;
	    $to_customers = $this->customerRepository->all()->whereIn('user_id', $request->recipients);
        $email_body = $request->message_body;
        $message_body = Common::parse_template($email_body);
        $saleorder_pdf = $request->saleorder_pdf;
        $site_email = Settings::get('site_email');
        if (!empty($to_customers) && !filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
            foreach ($to_customers as $item) {
                if (!filter_var($item->user->email, FILTER_VALIDATE_EMAIL) === false) {
                    Mail::to($item->user->email)->send(new SendQuotation([
                        'from' => $site_email,
                        'subject' => $email_subject,
                        'message_body' => $message_body,
                        'quotation_pdf' => $saleorder_pdf
                    ]));
                }
                $this->emailRepository->create([
                    'assign_customer_id' => $item->id,
                    'from' => $this->userRepository->getUser()->id,
                    'to' => $item->user_id,
                    'subject' => $email_subject,
                    'message' => $message_body
                ]);
            }
            echo '<div class="alert alert-success">' . trans('sales_order.success') . '</div>';
        }
        else {
            echo '<div class="alert alert-danger">' . trans('invoice.error') . '</div>';
        }
    }

    public function makeInvoice($saleorder)
    {
        $user = $this->userRepository->getUser();
        $saleorder = $this->salesOrderRepository->find($saleorder);
        $invoice = $this->invoiceRepository->getAll()->withDeleteList()->get()->count();
        if($invoice == 0){
            $total_fields = 0;
        }else{
            $total_fields = $this->invoiceRepository->getAll()->withDeleteList()->get()->last()->id;
        }
        $start_number = Settings::get('invoice_start_number');
        $invoice_number = Settings::get('invoice_prefix') . (is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields : 0) + 1);
        $listdata=[
            'order_id' => $saleorder->id,
            'customer_id' => $saleorder->customer_id,
            'lead_id' => $saleorder->lead_id,
            'partner_id' => $saleorder->partner_id,
            'sales_person_id' => $saleorder->sales_person_id,
            'sales_team_id' => $saleorder->sales_team_id,
            'invoice_number' => $invoice_number,
            'invoice_date' =>date("Y-m-d"),
            'invoice_deadline_date' => date("Y-m-d",strtotime($saleorder->date_exp)),
            'payment_term' => isset($saleorder->payment_term)?$saleorder->payment_term:0,
            'status' => 'Open Invoice',
            'total' => $saleorder->total,
            'tax_amount' => $saleorder->tax_amount,
            'grand_total' => $saleorder->grand_total,
            'unpaid_amount' => $saleorder->final_price,
            'discount' => $saleorder->discount,
            'final_price' => $saleorder->final_price,
            'user_id' => $user->id];
           
        $invoice = $this->invoiceRepository->create($listdata);
        $list =[];
        if (!empty($saleorder->salesOrderProducts->count() > 0)) {
            foreach ($saleorder->salesOrderProducts as $key=>$item) {
                $temp['quantity']=$item->pivot->quantity;
                $temp['price']=$item->pivot->price;
                $list[$item->pivot->product_id]=$temp;
            }
        }
        $invoice->invoiceProducts()->attach($list);

        $saleorder->update(['is_invoice_list' => 1]);
        return redirect('invoice');
    }


    private function generateParams()
    {
        $userData=$this->userRepository->getUser();
        $this->partner_id=$userData->partner_id;

        $products = Product::where('partner_id',$this->partner_id)->orderBy("id", "desc")->get();

        $qtemplates = null;
        /*
        $this->quotationTemplateRepository->getAll()
	            ->pluck('quotation_template', 'id')
	            ->prepend(trans('dashboard.select_template'), ''); */


        $companies = $this->companyRepository->getAll()->orderBy("name", "asc")
	            ->pluck('name', 'id')
	            ->prepend(trans('dashboard.select_company'), '');

        $listUserSales=$this->userRepository->getAllStaffOfUser($userData->id);
        
        $staffs="";
        if($listUserSales){
            $staffs=User::select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
            ->whereIn('users.id',$listUserSales)
            ->get()
            ->map( function ( $salesList ) {
                return [ 
                    'title' => $salesList->first_name." ".$salesList->last_name,
                    'value' => $salesList->id,
                ];
            } ) ->pluck( 'title', 'value' )
            ->prepend(trans('dashboard.select_staff'), '');
		}else{
			$staffs=array(''=>trans('dashboard.select_staff'));
        }

       //Status list
        $statusListData=CallActionStatus::select('call_action_status.*')
		//->join('group_client_status','group_client_status.client_status_id','=','call_action_status.id')
		->where('call_action_status.partner_id','=',$this->partner_id)
		->whereIn('call_action_status.type',array(2,4))
        ->orderBy('call_action_status.position', 'asc')->groupBy('call_action_status.id')->get();
        $listFull=$statusListData;
        $statusList=$statusListData->map( function ( $statusList ) {
			return [
				'title' => $statusList->title,
				'value' => $statusList->id,
			];
        } )->pluck( 'title', 'value')
        ->prepend(trans('clientstatus.select_type'), '');
        
        /*
        $staffs = $this->userRepository->getStaff()
	            ->pluck('full_name', 'id')
	            ->prepend(trans('dashboard.select_staff'), ''); */
/* 
        $salesteams = $this->salesTeamRepository->getAll()
	            ->pluck('salesteam', 'id')
	            ->prepend(trans('dashboard.select_sales_team'), '');

       $customers = $this->userRepository->getParentCustomers()
	            ->pluck('full_name', 'id')
                ->prepend(trans('dashboard.select_customer'), ''); */
       /* $customers = $this->leadRepository->getAll()->where('partner_id',$this->partner_id)
	            ->pluck('opportunity', 'id')
                ->prepend(trans('dashboard.select_customer'), ''); */

        $leads = $this->leadRepository->getAll()->where('partner_id',$this->partner_id)
	            ->pluck('opportunity', 'id')
                ->prepend(trans('dashboard.select_customer'), '');
               
        
        $paymentmentData = $this->optionRepository->getAll()->where( 'partner_id', $this->partner_id)->where( 'category', 'payment_term' )->get();
        if($paymentmentData=="" || count($paymentmentData)<=0){
            $paymentmentData = $this->optionRepository->getAll()->where('partner_id', 0)->where( 'category', 'payment_term')->get();
        }
        $paymentmethod=$paymentmentData->map( function ( $payment) {
                    return [
                        'title' => $payment->title,
                        'value' => $payment->value,
                    ];
                } )->pluck( 'title', 'value' )
                ->prepend(trans('sales_order.payment_method'), '');      
                
        $shippingData = $this->optionRepository->getAll()->where( 'partner_id', $this->partner_id)->where( 'category', 'shipping_term' )->get();
        
        if($shippingData=="" || count($shippingData)<=0){
            $shippingData = $this->optionRepository->getAll()->where( 'partner_id', 0)->where( 'category', 'shipping_term' )->get();
        }
        $shippingmethod=$shippingData->map( function ( $shipping ) {
                    return [
                        'title' => $shipping->title,
                        'value' => $shipping->value,
                    ];
                } )->pluck( 'title', 'value' )
                ->prepend(trans('sales_order.shipping_term'), ''); 
        $branch=Branch::where('partner_id',$this->partner_id)->get()->map( function ( $branch) {
                    return [
                        'title' => $branch->name,
                        'value' => $branch->id,
                    ];
                } )->pluck( 'title', 'value' )
                ->prepend(trans('branch.branch_select'), '');   
             /*
        $companies_mail = $this->userRepository->getAll()->get()->filter(function ($user) {
            return $user->inRole('customer');
        })->pluck('full_name', 'id'); */

        $statuses = $this->optionRepository->getAll()
            ->where('category', 'sales_order_status')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title', 'value')->prepend(trans('quotation.status'), '');
        $sales_tax = Settings::get('sales_tax');
        
        		//Status list
		$statustColor=CallActionStatus::where('partner_id','=',$this->partner_id)->whereIn('type',array(2,4))->orderBy('position', 'asc')->get();

        view()->share('statuses', $statuses);
        view()->share('products', $products);
        view()->share('qtemplates', $qtemplates);
        view()->share('companies', $companies);
        view()->share('staffs', $staffs);
        view()->share('statusList', $statusList);
        view()->share('statustColor', $listFull);
        //view()->share('salesteams', $salesteams);
        //view()->share('customers', $customers);
       // view()->share('companies_mail', $companies_mail);
        view()->share('leads', $leads);
        view()->share('branch', $branch);

        view()->share('paymentmethod', $paymentmethod);
        view()->share('shippingmethod', $shippingmethod);
        
        view()->share('sales_tax', isset($sales_tax) ? floatval($sales_tax) : 1);
    }

    private function emailRecipients($lead_id){
       // $email_recipients = $this->userRepository->getParentCustomers()->where('id',$lead_id)->pluck('full_name','id');
        $email_recipients = Lead::where('id',$lead_id)->pluck('contact_name','id');

        view()->share('email_recipients', $email_recipients);
    }
    public function updateOrderStatus(Request $request){
		$user=$this->userRepository->getUser();
		$sales_order_id=$request->sales_order_id;
		$status=$request->status_from;
		$statusTo=$request->status_to;
		if($sales_order_id!="" && $status!="" && $statusTo!=""){
			$salesorderDetail = Saleorder::where("id",$sales_order_id)->first();
            Saleorder::where('id', $sales_order_id)->where('partner_id',$user->partner_id)->update(['status_client'=>$statusTo]);
            Lead::where('id', $salesorderDetail["lead_id"])->where('partner_id',$user->partner_id)->update(['status'=>$statusTo]);
			$statusFrom=CallActionStatus::where('id',$status)->first();
			$statusTo=CallActionStatus::where('id',$statusTo)->first();
			$dataLogs = array(
				'user_id' => $user->id,
				'logs'=>"Chuyển tình trạng đơn hàng từ (".$statusFrom["title"].") đến (".$statusTo["title"].")",
				'logs_description'=>"",
				'created_at'=> date("Y-m-d H:i:s"),
                'lead_id'=>$salesorderDetail["lead_id"],
                'item'=>'sales_order',
                'item_id'=>$sales_order_id
			 );
			Logs::insert($dataLogs);
			return response()->json(['success' => 'success'], 200);
		}else{
			return response()->json(['success' => 'NoSuccess'], 200);
		}
		return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
    }
    
    //Add Call logs
	public function addOrderLog(Request $request)
	{
		$user=$this->userRepository->getUser();
		$data = array(
		   'user_id' => $user->id,
		   'sales_order_id'=>$request->input('sales_order_id'),
		   'date_call'=> date("Y-m-d H:i:s"),
	   );
	   $rules = array(
		   'user_id' => 'required',
		   'sales_order_id' => 'required',
	   );
	   $logtext=$request->input('logs_text');
	   $sales_order_id=$request->input('sales_order_id');
	   $logs_description=$request->input('logs_description');
	   $tags=$request->input('tags');
	   if ($logtext && $sales_order_id) {
			$salesOrderDetail = Saleorder::where("id",$sales_order_id)->first();
			if($salesOrderDetail ){
				$tagsLead=$salesOrderDetail["tags"];
				// Add to log
				if($request->input('tags')!=""){
					$tagsLead=$salesOrderDetail["tags"].", ".$tags;
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
                    'item_id'=>$sales_order_id,
                    'item'=>"sales_order",
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
    

    public function history(Request $request){
		$item_id =$request->item_id;
		$logData=Logs::where('item_id',$item_id)->where('item','sales_order')->orderBy("id", "desc")->paginate(50)->appends(request()->query());
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
}
