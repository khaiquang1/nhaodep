<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Common;
use App\Http\Controllers\UserController;
use App\Http\Requests\QuotationRequest;
use App\Mail\SendQuotation;
use App\Repositories\CompanyRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\EmailRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\OptionRepository;
use App\Repositories\ProductRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\QuotationTemplateRepository;
use App\Repositories\SalesOrderRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class QuotationController extends UserController
{
    /**
     * @var QuotationRepository
     */
    private $quotationRepository;
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

    private $customerRepository;

    private $salesOrderRepository;

    private $invoiceRepository;

    private $emailRepository;

    /**
     * QuotationController constructor.
     * @param QuotationRepository $quotationRepository
     * @param UserRepository $userRepository
     * @param SalesTeamRepository $salesTeamRepository
     * @param ProductRepository $productRepository
     * @param CompanyRepository $companyRepository
     * @param QuotationTemplateRepository $quotationTemplateRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(QuotationRepository $quotationRepository,
                                UserRepository $userRepository,
                                SalesTeamRepository $salesTeamRepository,
                                ProductRepository $productRepository,
                                CompanyRepository $companyRepository,
                                QuotationTemplateRepository $quotationTemplateRepository,
                                OptionRepository $optionRepository,
                                CustomerRepository $customerRepository,
                                SalesOrderRepository $salesOrderRepository,
                                InvoiceRepository $invoiceRepository,
                                EmailRepository $emailRepository
)
    {
        parent::__construct();

        $this->middleware('authorized:quotations.read', ['only' => ['index', 'data']]);
        $this->middleware('authorized:quotations.write', ['only' => ['create', 'store', 'update', 'edit']]);
        $this->middleware('authorized:quotations.delete', ['only' => ['delete']]);

        $this->quotationRepository = $quotationRepository;
        $this->userRepository = $userRepository;
        $this->salesTeamRepository = $salesTeamRepository;
        $this->productRepository = $productRepository;
        $this->companyRepository = $companyRepository;
        $this->quotationTemplateRepository = $quotationTemplateRepository;
        $this->optionRepository = $optionRepository;
        $this->customerRepository = $customerRepository;
        $this->salesOrderRepository = $salesOrderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->emailRepository = $emailRepository;

        view()->share('type', 'quotation');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('quotation.quotations');
        return view('user.quotation.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('quotation.create');

        $this->generateParams();

        return view('user.quotation.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param QuotationRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(QuotationRequest $request)
    {

        if(empty($request->qtemplate_id)){
            $request->merge(['qtemplate_id'=>0]);
        }
        $quotation = $this->quotationRepository->getAll()->withDeleteList()->get()->count();
        if($quotation == 0){
            $total_fields = 0;
        }else{
            $total_fields = $this->quotationRepository->getAll()->withDeleteList()->get()->last()->id;
        }
        $start_number = Settings::get('quotation_start_number') ;
        $quotation_no = Settings::get('quotation_prefix') . (is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields : 0) + 1);
        $request->merge(['quotations_number'=> $quotation_no,'is_delete_list'=>0,'is_converted_list'=>0,'is_quotation_invoice_list'=>0]);
        $this->quotationRepository->createQuotation($request->all());
        if ($request->status == trans('quotation.draft_quotation')){
            return redirect("quotation/draft_quotations");
        }else{
            return redirect("quotation");
        }
    }

    public function edit($quotation)
    {
        $quotation = $this->quotationRepository->find($quotation);
        $title = trans('quotation.edit');

        $this->generateParams();
        $this->emailRecipients($quotation->customer_id);

        $sales_team = $this->salesTeamRepository->find($quotation->sales_team_id);
        $team_leader = $this->userRepository->all()->where('id',$sales_team->team_leader)->pluck('full_name','id')->toArray();
        $sales_team_members = $sales_team->members->pluck('full_name','id')->toArray();
        $main_staff = $team_leader+$sales_team_members;

        return view('user.quotation.edit', compact('title', 'quotation','main_staff'));
    }

    public function update(QuotationRequest $request, $quotation)
    {
        if(empty($request->qtemplate_id)){
            $request->merge(['qtemplate_id'=>0]);
        }
        $quotation_id = $quotation;
        $this->quotationRepository->updateQuotation($request->all(),$quotation_id);

        if ($request->status == trans('quotation.draft_quotation')){
            return redirect("quotation/draft_quotations");
        }else{
            return redirect("quotation");
        }
    }

    public function show($quotation)
    {
        $quotation = $this->quotationRepository->find($quotation);
        $title = trans('quotation.show');
        $action = 'show';
        $this->generateParams();
        $this->emailRecipients($quotation->customer_id);
        return view('user.quotation.show', compact('title', 'quotation','action'));
    }

    public function delete($quotation)
    {
        $quotation = $this->quotationRepository->find($quotation);
        $title = trans('quotation.delete');
        $this->generateParams();
        return view('user.quotation.delete', compact('title', 'quotation'));
    }

    public function destroy($quotation)
    {
        $quotation = $this->quotationRepository->find($quotation);
        $quotation->update(['is_delete_list' => 1]);
        return redirect('quotation');
    }

    /**
     * @return mixed
     */
    public function data(Datatables $datatables)
    {
        $dateFormat = config('settings.date_format');
        $listUser=$this->userRepository->getAllStaffOfUser(0);
        $quotations = $this->quotationRepository->getAll()->whereIn('sales_person_id',$listUser)
            ->where([
                ['status','!=','Draft Quotation']
            ])
            ->with('user', 'customer')
            ->get()
            ->map(function ($quotation) use ($dateFormat){
                return [
                    'id' => $quotation->id,
                    'quotations_number' => $quotation->quotations_number,
                    'customer' => isset($quotation->customer) ? $quotation->customer->full_name : '',
                    'final_price' => $quotation->final_price,
                    'date' => date($dateFormat, strtotime($quotation->date)),
                    'exp_date' => date($dateFormat, strtotime($quotation->exp_date)),
                    'payment_term' => $quotation->payment_term,
                    'status' => $quotation->status
                ];
            });

        return $datatables->collection($quotations)
            ->addColumn(
                'expired',
                '@if(strtotime(date("m/d/Y"))>strtotime("+".$payment_term." ",strtotime($exp_date)))
                                        <i class="fa fa-bell-slash text-danger" title="{{trans(\'quotation.quotation_expired\')}}"></i> 
                                     @else
                                      <i class="fa fa-bell text-warning" title="{{trans(\'quotation.quotation_will_expire\')}}"></i> 
                                     @endif'
            )
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'quotations.write\']) || Sentinel::inRole(\'admin\'))
                                    
                                     @endif
                                     @if(Sentinel::getUser()->hasAccess([\'quotations.read\']) || Sentinel::inRole(\'admin\'))
                                    <a href="{{ url(\'quotation/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                     <a href="{{ url(\'quotation/\' . $id . \'/print_quot\' ) }}" title="{{ trans(\'table.print\') }}">
                                            <i class="fa fa-fw fa-print text-primary "></i>  </a>
                                    @endif
                                    
                                     @if(Sentinel::getUser()->hasAccess([\'quotations.delete\']) || Sentinel::inRole(\'admin\'))
                                   <a href="{{ url(\'quotation/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                   @endif')
            ->removeColumn('id')
            ->rawColumns(['actions','expired'])->make();
    }

    public function draftIndex(){
        $title=trans('quotation.draft_quotations');
        return view('user.quotation.draft_quotations', compact('title'));
    }
    public function draftQuotations(Datatables $datatables)
    {
        $dateFormat = config('settings.date_format');
        $quotations = $this->quotationRepository->getAll()
            ->where('status',trans('quotation.draft_quotation'))
            ->with('user', 'customer')
            ->get()
            ->map(function ($quotation) use ($dateFormat){
                return [
                    'id' => $quotation->id,
                    'quotations_number' => $quotation->quotations_number,
                    'customer' => isset($quotation->customer) ? $quotation->customer->full_name : '',
                    'final_price' => $quotation->final_price,
                    'date' => date($dateFormat, strtotime($quotation->date)),
                    'exp_date' => date($dateFormat, strtotime($quotation->exp_date)),
                    'payment_term' => $quotation->payment_term,
                    'status' => $quotation->status
                ];
            });

        return $datatables->collection($quotations)
            ->addColumn(
                'expired',
                '@if(strtotime(date("m/d/Y"))>strtotime("+".$payment_term." ",strtotime($exp_date)))
                                        <i class="fa fa-bell-slash text-danger" title="{{trans(\'quotation.quotation_expired\')}}"></i> 
                                     @else
                                      <i class="fa fa-bell text-warning" title="{{trans(\'quotation.quotation_will_expire\')}}"></i> 
                                     @endif'
            )
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'quotations.write\']) || Sentinel::inRole(\'admin\'))
                                    <a href="{{ url(\'quotation/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}" >
                                            <i class="fa fa-fw fa-pencil text-warning"></i>  </a>
                                     @endif
                                     @if(Sentinel::getUser()->hasAccess([\'quotations.read\']) || Sentinel::inRole(\'admin\'))
                                    <a href="{{ url(\'quotation/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                     <a href="{{ url(\'quotation/\' . $id . \'/print_quot\' ) }}" title="{{ trans(\'table.print\') }}">
                                            <i class="fa fa-fw fa-print text-primary "></i>  </a>
                                    @endif                                
                                     @if(Sentinel::getUser()->hasAccess([\'quotations.delete\']) || Sentinel::inRole(\'admin\'))
                                   <a href="{{ url(\'quotation/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                   @endif')
            ->removeColumn('id')
            ->rawColumns(['actions','expired'])->make();
    }

    function confirmSalesOrder($quotation)
    {
        $user = $this->userRepository->getUser();
        $quotation = $this->quotationRepository->find($quotation);
        $salesOrder = $this->salesOrderRepository->getAll()->withDeleteList()->get()->count();
        if($salesOrder == 0){
            $total_fields = 0;
        }else{
            $total_fields = $this->salesOrderRepository->getAll()->withDeleteList()->get()->last()->id;
        }
        $start_number = Settings::get('sales_start_number');
        $sale_no = Settings::get('sales_prefix') . (is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields : 0) + 1);

        $saleorder = $this->salesOrderRepository->create([
            'sale_number' => $sale_no,
            'customer_id' => $quotation->customer_id,
            'date' => date(config('settings.date_format')),
            'exp_date' => $quotation->expire_date,
            'qtemplate_id' => $quotation->qtemplate_id,
            'payment_term' => isset($quotation->payment_term)?$quotation->payment_term:0,
            "sales_person_id" => $quotation->sales_person_id,
            "sales_team_id" => $quotation->sales_team_id,
            "terms_and_conditions" => $quotation->terms_and_conditions,
            "total" => $quotation->total,
            "tax_amount" => $quotation->tax_amount,
            "grand_total" => $quotation->grand_total,
            "discount" => is_null($quotation->discount)?0:$quotation->discount,
            "final_price" => $quotation->final_price,
            'status' => 'Draft sales order',
            'user_id' => $user->id,
            'quotation_id' => $quotation->id
        ]);

        $list =[];
        if (!empty($quotation->quotationProducts->count() > 0)) {
            foreach ($quotation->quotationProducts as $key=>$item) {
                $temp['quantity']=$item->pivot->quantity;
                $temp['price']=$item->pivot->price;
                $list[$item->pivot->product_id]=$temp;
            }
        }
        $saleorder->salesOrderProducts()->attach($list);

        $quotation->update(['is_converted_list' => 1]);

        return redirect('sales_order/draft_salesorders');
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


    public function printQuot($quotation)
    {
        $quotation = $this->quotationRepository->find($quotation);
        $filename = 'Quotation-' . $quotation->quotations_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4','landscape');
        $pdf->loadView('quotation_template.'.Settings::get('quotation_template'), compact('quotation'));
        return $pdf->download($filename . '.pdf');
    }

    public function ajaxCreatePdf($quotation)
    {
        $quotation = $this->quotationRepository->find($quotation);
        $filename = 'Quotation-' .Str::slug($quotation->quotations_number);
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4','landscape');
        $pdf->loadView('quotation_template.'.Settings::get('quotation_template'), compact('quotation'));
        $pdf->save('./pdf/' . $filename . '.pdf');
        $pdf->stream();
        echo url("pdf/" . $filename . ".pdf");

    }

    public function sendQuotation(Request $request)
    {
        $email_subject = $request->email_subject;
        $to_customers = $this->customerRepository->all()->whereIn('user_id', $request->recipients);
        $email_body = $request->message_body;
        $message_body = Common::parse_template($email_body);
        $quotation_pdf = $request->quotation_pdf;

        $site_email = Settings::get('site_email');
        if (!empty($to_customers) && !filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
            foreach ($to_customers as $item) {
                 if (!filter_var($item->user->email, FILTER_VALIDATE_EMAIL) === false) {
                     Mail::to($item->user->email)->send(new SendQuotation([
                         'from' => $site_email,
                         'subject' => $email_subject,
                         'message_body' => $message_body,
                         'quotation_pdf' => $quotation_pdf
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
            echo '<div class="alert alert-success">' . trans('quotation.success') . '</div>';
        } else {
            echo '<div class="alert alert-danger">' . trans('invoice.error') . '</div>';
        }
    }

    public function makeInvoice($quotation)
    {
        $user = $this->userRepository->getUser();

        $quotation = $this->quotationRepository->find($quotation);
        if(!$quotation){
            abort(404);
        }
        $invoice = $this->invoiceRepository->getAll()->withDeleteList()->get()->count();
        if($invoice == 0){
            $total_fields = 0;
        }else{
            $total_fields = $this->invoiceRepository->getAll()->withDeleteList()->get()->last()->id;
        }

        $start_number = Settings::get('invoice_start_number');
        $invoice_number = Settings::get('invoice_prefix') . ( is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields : 0) + 1);

        $invoice = $this->invoiceRepository->create([
            'quotation_id' => $quotation->id,
            'customer_id' => $quotation->customer_id,
            'sales_person_id' => $quotation->sales_person_id,
            'sales_team_id' => $quotation->sales_team_id,
            'invoice_number' => $invoice_number,
            'invoice_date' => date(config('settings.date_format')),
            'due_date' => $quotation->expire_date,
            'payment_term' => isset($quotation->payment_term)?$quotation->payment_term:0,
            'status' => 'Open Invoice',
            'total' => $quotation->total,
            'tax_amount' => $quotation->tax_amount,
            'grand_total' => $quotation->grand_total,
            'unpaid_amount' => $quotation->final_price,
            'discount' => $quotation->discount,
            'final_price' => $quotation->final_price,
            'user_id' => $user->id
        ]);
        $list =[];
        if (!empty($quotation->quotationProducts->count() > 0)) {
            foreach ($quotation->quotationProducts as $key=>$item) {
                $temp['quantity']=$item->pivot->quantity;
                $temp['price']=$item->pivot->price;
                $list[$item->pivot->product_id]=$temp;
            }
        }
        $invoice->invoiceProducts()->attach($list);

        $quotation->update(['is_quotation_invoice_list' => 1]);
        return redirect('invoice');
    }

    private function generateParams()
    {
        $products = $this->productRepository->orderBy("id", "desc")->all();

        $qtemplates = $this->quotationTemplateRepository->getAll()
	            ->pluck('quotation_template', 'id')
	            ->prepend(trans('dashboard.select_template'), '');

        $companies = $this->companyRepository->getAll()->orderBy("name", "asc")
	            ->pluck('name', 'id')
	            ->prepend(trans('dashboard.select_company'), '');

        $staffs = $this->userRepository->getStaff()
	            ->pluck('full_name', 'id')
	            ->prepend(trans('dashboard.select_staff'), '');

        $salesteams = $this->salesTeamRepository->getAll()
                ->orderBy("id", "asc")
                ->pluck('salesteam', 'id')
                ->prepend(trans('quotation.sales_team_id'), '');

        $customers = $this->userRepository->getParentCustomers()
	            ->pluck('full_name', 'id')
	            ->prepend(trans('dashboard.select_customer'), '');

        $companies_mail = $this->userRepository->getAll()->get()->filter(function ($user) {
            return $user->inRole('customer');
        })->pluck('full_name', 'id');

        $statuses = $this->optionRepository->getAll()
            ->where('category', 'quotation_status')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title', 'value')->prepend(trans('quotation.status'), '');

        $sales_tax = Settings::get('sales_tax');

        view()->share('statuses', $statuses);
        view()->share('products', $products);
        view()->share('qtemplates', $qtemplates);
        view()->share('companies', $companies);
        view()->share('staffs', $staffs);
        view()->share('salesteams', $salesteams);
        view()->share('customers', $customers);
        view()->share('companies_mail', $companies_mail);
        view()->share('sales_tax', isset($sales_tax) ? floatval($sales_tax) : 1);
    }

    public function ajaxSalesTeamList( Request $request){
        $agent_name = $this->customerRepository->all()->where('user_id',$request->id)->pluck('sales_team_id','user_id');
        $agent_name = $agent_name[$request->id];
        $sales_team = $this->salesTeamRepository->all()->pluck('salesteam','id')->prepend(trans('quotation.sales_team_id'), '');
        return ['agent_name'=>$agent_name,'sales_team' => $sales_team];
    }
    private function emailRecipients($customer_id){
        $email_recipients = $this->userRepository->getParentCustomers()->where('id',$customer_id)->pluck('full_name','id');
        view()->share('email_recipients', $email_recipients);
    }
}
