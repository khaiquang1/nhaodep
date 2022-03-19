<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Common;
use App\Http\Controllers\UserController;
use App\Http\Requests\InvoiceMailRequest;
use App\Http\Requests\InvoiceRequest;
use App\Mail\SendQuotation;
use App\Models\Paymentmethod;
use App\Models\Invoice;
use App\Models\User;


use App\Repositories\CompanyRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\EmailRepository;
use App\Repositories\InvoicePaymentRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\OptionRepository;
use App\Repositories\ProductRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\QuotationTemplateRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Yajra\Datatables\Datatables;

class InvoiceController extends UserController
{
	/**
	 * @var CompanyRepository
	 */
	private $companyRepository;
	/**
	 * @var InvoiceRepository
	 */
	private $invoiceRepository;
	/**
	 * @var UserRepository
	 */
	private $userRepository;
	/**
	 * @var QuotationRepository
	 */
	private $quotationRepository;
	/**
	 * @var SalesTeamRepository
	 */
	private $salesTeamRepository;
	/**
	 * @var ProductRepository
	 */
	private $productRepository;
	/**
	 * @var QuotationTemplateRepository
	 */
	private $quotationTemplateRepository;
	/**
	 * @var OptionRepository
	 */
	private $optionRepository;

    private $invoicePaymentRepository;

    private $customerRepository;

    private $emailRepository;

    /**
     * InvoiceController constructor.
     * @param CompanyRepository $companyRepository
     * @param InvoiceRepository $invoiceRepository
     * @param UserRepository $userRepository
     * @param QuotationRepository $quotationRepository
     * @param SalesTeamRepository $salesTeamRepository
     * @param ProductRepository $productRepository
     * @param QuotationTemplateRepository $quotationTemplateRepository
     * @param OptionRepository $optionRepository
     * @param InvoicePaymentRepository $invoicePaymentRepository
     * @param CustomerRepository $customerRepository
     * @param EmailRepository $emailRepository
     */
	public function __construct(
		CompanyRepository $companyRepository,
		InvoiceRepository $invoiceRepository,
		UserRepository $userRepository,
		QuotationRepository $quotationRepository,
		SalesTeamRepository $salesTeamRepository,
		ProductRepository $productRepository,
		QuotationTemplateRepository $quotationTemplateRepository,
		OptionRepository $optionRepository,
        InvoicePaymentRepository $invoicePaymentRepository,
        CustomerRepository $customerRepository,
        EmailRepository $emailRepository
	) {
		$this->middleware('authorized:invoices.read', ['only' => ['index', 'data']]);
		$this->middleware('authorized:invoices.write', ['only' => ['create', 'store', 'update', 'edit']]);
		$this->middleware('authorized:invoices.delete', ['only' => ['delete']]);

		parent::__construct();

		$this->companyRepository = $companyRepository;
		$this->invoiceRepository = $invoiceRepository;
		$this->userRepository = $userRepository;
		$this->quotationRepository = $quotationRepository;
		$this->salesTeamRepository = $salesTeamRepository;
		$this->productRepository = $productRepository;
		$this->quotationTemplateRepository = $quotationTemplateRepository;
		$this->optionRepository = $optionRepository;
        $this->invoicePaymentRepository = $invoicePaymentRepository;
        $this->customerRepository = $customerRepository;
        $this->emailRepository = $emailRepository;

		view()->share('type', 'invoice');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$title = trans('invoice.invoices');
		$dateFormat = config('settings.date_format');
		$date  = addslashes($request->starting_date);
		$sales_id = addslashes($request->sales_id) ;
		$status = addslashes($request->status);
		$keyword  = addslashes($request->keyword) ;
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
        $partner_id=$userData->partner_id;
		$this->generateParams();
		$invoicesQuery = Invoice::select('invoices.*','leads.id as lead_id', 'leads.opportunity', 'leads.email', 'leads.phone')->join('leads','leads.id','=','invoices.lead_id')
		->where('invoices.partner_id',$partner_id)
		/*
		->where([
			['invoices.status','!=', trans('invoices.paid_invoice')]
		]) */
		->where(function ($query)  use ($starting_date, $sales_id,$status, $keyword){
			if($starting_date!=""){
				$query->where('invoices.invoice_date','>=',$starting_date);
			}
			if($status!=""){
				$query->whereIn('invoices.status',explode(",",$status));
			}
			
			if($sales_id!="" && $sales_id!="0"){
				$query->where('invoices.sales_person_id',$sales_id);
			}
			
		})->where(function ($query1)  use ($keyword){
			if($keyword!=""){
					$query1->where('invoices.payment_term', 'LIKE', "%{$keyword}%");
					$query1->orWhere('leads.opportunity','LIKE', "%{$keyword}%");
					$query1->orWhere('leads.email','LIKE', "%{$keyword}%");
					$query1->orWhere('leads.phone', 'LIKE', "%{$keyword}%");
			}
		})->with('receivePayment')->distinct()
		->orderBy('invoices.created_at', 'DESC');
		//$totalLead=$leadsQuery->count();
		$totalInvoice=count($invoicesQuery->get());
		$invoicesPage=$invoicesQuery->paginate(20)->appends(request()->query());
		$invoicesList=$invoicesPage->map( function ( $invoice){
			return [
				'id' => $invoice->id,
				'invoice_number' => $invoice->invoice_number,
				'invoice_date' => date("Y-m-d", strtotime($invoice->invoice_date)),
				'customer' => $invoice->opportunity,
				'invoice_deadline_date' => date("Y-m-d", strtotime($invoice->invoice_deadline_date)),
				'final_price' => $invoice->final_price,
				'unpaid_amount' => $invoice->unpaid_amount,
				'status' => $invoice->status,
				'lead_id' => $invoice->lead_id,
				'payment_term' =>Paymentmethod::where('id',$invoice->payment_id)->pluck('name'),
				'count_payment' => $invoice->receivePayment->count(),
		  
			];
		});


        return view('user.invoice.index', compact('title', 'invoicesList', 'invoicesPage'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		$title = trans('invoice.new');

		$this->generateParams();

		return view('user.invoice.create', compact('title'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param InvoiceRequest|Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(InvoiceRequest $request)
	{

		$user=$this->userRepository->getUser();
		$partner_id=$user->partner_id;
		
        if (empty($request->qtemplate_id)) {
            $request->merge(['qtemplate_id' => 0]);
        }
        $invoice = $this->invoiceRepository->getAll()->withDeleteList()->get()->count();
        if (0 == $invoice) {
            $total_fields = 0;
        } else {
            $total_fields = $this->invoiceRepository->getAll()->withDeleteList()->get()->last()->id;
        }
        if($request->status == trans('invoice.paid_invoice')){
            $request->merge(['unpaid_amount' => 0]);
        }else{
            $request->merge(['unpaid_amount' => $request->final_price]);

		}
		$request->merge(['partner_id' =>$partner_id]);

        $start_number = Settings::get('invoice_start_number') ;
        $invoice_number = Settings::get('invoice_prefix').(is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields : 0) + 1);
        $request->merge(['invoice_number' => $invoice_number]);

        $this->invoiceRepository->createInvoice($request->all());

		return redirect("invoice");
	}

	public function edit($invoice)
	{
	    $invoice = $this->invoiceRepository->find($invoice);
		$title = trans('invoice.edit').' '.$invoice->invoice_number;
		$this->generateParams();
		$this->emailRecipients($invoice->customer_id);

		return view('user.invoice.edit', compact('title', 'invoice'));
	}

	public function update(InvoiceRequest $request, $invoice)
	{
        $invoice = $this->invoiceRepository->find($invoice);
        if (!$invoice){
            abort(404);
        }
        $final_price = $invoice->final_price;
        $unpaid_amout = $invoice->unpaid_amount;
        if($request->final_price > $final_price){
            $unpaid_amout = $unpaid_amout + ( $request->final_price - $final_price );
        }else{
            $unpaid_amout = $unpaid_amout - ( $final_price - $request->final_price );
        }
		$user=$this->userRepository->getUser();
		$partner_id=$user->partner_id;

        if (empty($request->qtemplate_id)) {
            $request->merge(['qtemplate_id' => 0]);
        }
        if($request->status == trans('invoice.paid_invoice')){
            $request->merge(['unpaid_amount' => 0]);
        }else{
            $request->merge(['unpaid_amount' => $unpaid_amout]);
		}
		$request->merge(['partner_id' =>$partner_id]);
        $this->invoiceRepository->updateInvoice($request->all(), $invoice->id);

        return redirect('invoice');
	}

	public function show($invoice)
	{
        $invoice = $this->invoiceRepository->find($invoice);
		$title = trans('invoice.show');
		$action = 'show';
		$this->generateParams();
        $this->emailRecipients($invoice->customer_id);

		return view('user.invoice.show', compact('title', 'invoice', 'action'));
	}

	public function delete($invoice)
	{
        $invoice = $this->invoiceRepository->find($invoice);
		$title = trans('invoice.delete');
		$this->generateParams();

		return view('user.invoice.delete', compact('title', 'invoice'));
	}

	public function destroy($invoice)
	{
        $invoice = $this->invoiceRepository->find($invoice);
        $invoice->update(['is_delete_list' => 1]);

		return redirect('invoice');
	}


	 public function data(Datatables $datatables, Request $request)
	{
		$dateFormat = config('settings.date_format');
		$starting_date  = addslashes($request->starting_date);
		$sales_id = addslashes($request->sales_id) ;
		$status = addslashes($request->status);
		$keyword  = addslashes($request->keyword) ;
		$userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
		$invoices = Invoice::select('invoices.*','leads.id', 'leads.contact_name', 'leads.email', 'leads.phone')->join('leads','leads.id','=','invoices.lead_id')
			->where('invoices.partner_id',$partner_id)
			/*
            ->where([
                ['invoices.status','!=', trans('invoices.paid_invoice')]
			]) */
			->where(function ($query)  use ($starting_date, $sales_id,$status, $keyword){
				if($starting_date!=""){
					$query->where('invoices.invoice_date','>=',$starting_date);
				}
				if($status!=""){
					$query->whereIn('invoices.status',explode(",",$status));
				}
				
				if($sales_id!="" && $sales_id!="0"){
					$query->where('invoices.sales_person_id',$sales_id);
				}
				
			})->where(function ($query1)  use ($keyword){
                if($keyword!=""){
                        $query1->where('invoices.payment_term', 'LIKE', "%{$keyword}%");
                        $query1->orWhere('leads.contact_name','LIKE', "%{$keyword}%");
						$query1->orWhere('leads.email','LIKE', "%{$keyword}%");
						$query1->orWhere('leads.phone', 'LIKE', "%{$keyword}%");
				}
            })
			->with('receivePayment')
			->get()
			->map(function ($invoice) use($dateFormat) {
					return [
						'id' => $invoice->id,
						'invoice_number' => $invoice->invoice_number,
                        'invoice_date' => date($dateFormat, strtotime($invoice->invoice_date)),
						'customer' => $invoice->contact_name,
                        'invoice_deadline_date' => date($dateFormat, strtotime($invoice->invoice_deadline_date)),
						'final_price' => $invoice->final_price,
						'unpaid_amount' => $invoice->unpaid_amount,
						'status' => $invoice->status,
						'payment_term' =>Paymentmethod::where('id',$invoice->payment_id)->pluck('name'),
						'count_payment' => $invoice->receivePayment->count(),
					];
				}
			);

		return $datatables->collection($invoices)
			->addColumn(
				'expired',
				'@if(strtotime()>strtotime($invoice_deadline_date))
                                        <i class="fa fa-bell-slash text-danger" title="{{trans(\'invoice.invoice_expired\')}}"></i> 
                                     @else
                                      <i class="fa fa-bell text-warning" title="{{trans(\'invoice.invoice_will_expire\')}}"></i> 
                                     @endif'
			)

			->addColumn(
				'actions',
				'@if(Sentinel::getUser()->hasAccess([\'invoices.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'invoice/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning"></i> </a>
                                     @endif
                                     @if(Sentinel::getUser()->hasAccess([\'invoices.read\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'invoice/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                     <a href="{{ url(\'invoice/\' . $id . \'/print_quot\' ) }}" title="{{ trans(\'table.print\') }}">
                                            <i class="fa fa-fw fa-print text-primary "></i>  </a>
                                    @endif
                                     @if((Sentinel::getUser()->hasAccess([\'invoices.delete\']) || Sentinel::inRole(\'admin\')) && $count_payment==0)
                                        <a href="{{ url(\'invoice/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                     @endif'
			)
			->removeColumn('id')
			->removeColumn('count_payment')
			->removeColumn('payment_term')
			->rawColumns(['actions','expired'])->make();
	}

	function ajaxCustomerDetails($user)
	{
	    $user = $this->userRepository->find($user);
		$details = array();

		$details['email'] = $user->email;
		$details['address'] = $user->address;

		echo json_encode($details);
	}


	public function printQuot($invoice)
	{
        $invoice = $this->invoiceRepository->find($invoice);
		$filename = 'Invoice-'.$invoice->invoice_number;
		$pdf = App::make('dompdf.wrapper');

		$pdf->setPaper('a4','landscape');
		$print_type = trans('invoice.invoice_no');
		$pdf->loadView('invoice_template.'.Settings::get('invoice_template'), compact('invoice', 'print_type'));

		return $pdf->download($filename.'.pdf');
	}

	public function ajaxCreatePdf($invoice)
	{
        $invoice = $this->invoiceRepository->find($invoice);
		$filename = 'Invoice-'.Str::slug($invoice->invoice_number);
		$pdf = App::make('dompdf.wrapper');
		$pdf->setPaper('a4','landscape');
		$print_type = trans('invoice.invoice_no');
		$pdf->loadView('invoice_template.'.Settings::get('invoice_template'), compact('invoice', 'print_type'));
		$pdf->save('./pdf/'.$filename.'.pdf');
		$pdf->stream();
		echo url("pdf/".$filename.".pdf");

	}

	/**
	 * @param InvoiceMailRequest $request
	 */
	public function sendInvoice(InvoiceMailRequest $request)
	{
		$email_subject = $request->email_subject;
        $to_customers = $this->customerRepository->all()->whereIn('user_id', $request->recipients);
		$email_body = $request->message_body;
		$message_body = Common::parse_template($email_body);
		$invoice_pdf = $request->invoice_pdf;

        $site_email = Settings::get('site_email');
		if (!empty($to_customers) && !filter_var(Settings::get('site_email'), FILTER_VALIDATE_EMAIL) === false) {
            foreach ($to_customers as $item) {
                if (!filter_var($item->user->email, FILTER_VALIDATE_EMAIL) === false) {
                    Mail::to($item->user->email)->send(new SendQuotation([
                        'from' => $site_email,
                        'subject' => $email_subject,
                        'message_body' => $message_body,
                        'quotation_pdf' => $invoice_pdf
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
			echo '<div class="alert alert-success">'.trans('invoice.success').'</div>';
		} else {
			echo '<div class="alert alert-danger">'.trans('invoice.error').'</div>';
		}
	}

	private function generateParams()
	{
		$partner_id=0;
        $userData=$this->userRepository->getUser();
        if($userData){
            $partner_id= $userData->partner_id;
        }
		$customers = $this->userRepository->getParentCustomers()
				->pluck('full_name', 'id')
				->prepend(trans('dashboard.select_customer'), '');
        $open_invoice_total = round($this->invoiceRepository->getAllOpen()->where('partner_id',$partner_id)->sum('unpaid_amount'), 3);
        $overdue_invoices_total = round($this->invoiceRepository->getAllOverdue()->where('partner_id',$partner_id)->sum('unpaid_amount'), 3);
		$paid_invoices_total = round($this->invoiceRepository->getAll()->withDeleteList()->get()->where('partner_id',$partner_id)->where('status','paid')->sum('final_price'));
		
        $invoices_total_collection = round($this->invoiceRepository->all()->where('partner_id',$partner_id)->where('is_delete_list',0)->sum('final_price'), 3);

		$payment_methods = $this->optionRepository->getAll()
			->where('category', 'payment_methods')
			->get()
			->map(
				function ($title) {
					return [
						'title' => $title->title,
						'value' => $title->value,
					];
				}
			)->pluck('title', 'value');

		$companies = $this->companyRepository->getAll()->orderBy("name","asc")
			             ->pluck('name', 'id')
			             ->prepend(trans('dashboard.select_company'), '');

		$statuses = $this->optionRepository->getAll()
			->where('category', 'invoice_status')
			->get()
			->map(
				function ($title) {
					return [
						'title' => $title->title,
						'value' => $title->value,
					];
				}
			)->pluck('title', 'value')->prepend(trans('invoice.status'), '');
		/*
		$payment_term = array(
			'' => trans('dashboard.select_payment_term'),
			Settings::get('payment_term1') => Settings::get('payment_term1').' Days',
			Settings::get('payment_term2') => Settings::get('payment_term2').' Days',
			Settings::get('payment_term3') => Settings::get('payment_term3').' Days',
			'0' => 'Immediate Payment',
		); */
		$payment_term = Paymentmethod::where('partner_id',$partner_id)
		->where('status',1)
		->orderBy("position", "asc")
		->get()
		->map(function ($payment) {
			return [
				'title' => $payment->name,
				'value'   => $payment->id,
			];
		})->pluck('title','value')->prepend(trans('invoice.select_payment'), '');

		$staff_care=$this->userRepository->getAllUserOnPartner($userData->id);
		$qtemplates = null;//$this->quotationTemplateRepository->getAll()
			//	->pluck('quotation_template', 'id')
			//	->prepend(trans('dashboard.select_template'), '');

		$salesteams =$this->salesTeamRepository->getAll()
				->where('partner_id', $partner_id)
				->pluck('salesteam', 'id')
				->prepend(trans('dashboard.select_sales_team'), '');

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
		$products = $this->productRepository->orderBy("id", "desc")->all();

		$month_overdue = round($this->invoiceRepository->getAllOverdueMonth()->where( 'partner_id', $partner_id)->sum('unpaid_amount'), 3);
		$month_paid = round($this->invoiceRepository->getAllPaidMonth()->where('partner_id', $partner_id)->sum('final_price'), 3);
		$month_open = round($this->invoiceRepository->getAllOpenMonth()->where('partner_id', $partner_id)->sum('final_price'), 3);

		$companies_mail = $this->userRepository->getAll()->get()->filter(
			function ($user) {
				return $user->inRole('customer');
			}
		)->pluck('full_name', 'id');

		$sales_tax = Settings::get('sales_tax');
		
		$invoicestatus = $this->optionRepository->getAll()->where( 'category', 'status_invoice')->get()
		->map( function ( $title ) {
			return [
				'title' => $title->title,
				'value' => $title->value,
			];
		} )->pluck( 'title', 'value' )
		->prepend(trans('invoice.status'), '');
		$paymentmentData = $this->optionRepository->getAll()->where( 'partner_id', $partner_id)->where( 'category', 'payment_term' )->get();
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
		view()->share('salesList', $staffs);
		view()->share('invoice_status', $invoicestatus);
        view()->share('payment_term', $payment_term);
        view()->share('customers', $customers);
        view()->share('open_invoice_total', $open_invoice_total);
        view()->share('overdue_invoices_total', $overdue_invoices_total);
        view()->share('paid_invoices_total', $paid_invoices_total);
        view()->share('invoices_total_collection', $invoices_total_collection);
        view()->share('statuses', $statuses);
        view()->share('companies', $companies);
        view()->share('payment_methods', $payment_methods);
        view()->share('qtemplates', $qtemplates);
        view()->share('salesteams', $salesteams);
        view()->share('staffs', $staffs);
        view()->share('products', $products);
        view()->share('month_overdue', $month_overdue);
        view()->share('month_paid', $month_paid);
        view()->share('month_open', $month_open);
        view()->share('companies_mail', $companies_mail);
        view()->share('sales_tax', isset($sales_tax) ? floatval($sales_tax) : 1);
    }

    private function emailRecipients($customer_id){
        $email_recipients = $this->userRepository->getParentCustomers()->where('id',$customer_id)->pluck('full_name','id');
        view()->share('email_recipients', $email_recipients);
    }
}
