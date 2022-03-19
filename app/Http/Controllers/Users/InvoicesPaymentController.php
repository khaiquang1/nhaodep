<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Http\Requests\InvoiceReceivePaymentRequest;
use App\Repositories\CompanyRepository;
use App\Repositories\InvoicePaymentRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\OptionRepository;
use App\Repositories\UserRepository;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class InvoicesPaymentController extends UserController
{
    /**
     * @var InvoicePaymentRepository
     */
    private $invoicePaymentRepository;

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
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * @param InvoicePaymentRepository $invoicePaymentRepository
     * @param CompanyRepository $companyRepository
     * @param InvoiceRepository $invoiceRepository
     * @param UserRepository $userRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(InvoicePaymentRepository $invoicePaymentRepository,
                                CompanyRepository $companyRepository,
                                InvoiceRepository $invoiceRepository,
                                UserRepository $userRepository,
                                OptionRepository $optionRepository)
    {
        parent::__construct();

        $this->invoicePaymentRepository = $invoicePaymentRepository;
        $this->companyRepository = $companyRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->userRepository = $userRepository;
        $this->optionRepository = $optionRepository;

        view()->share('type', 'invoices_payment_log');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('invoices_payment_log.invoices_payment_log');
        return view('user.invoices_payment_log.index', compact('title'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('invoices_payment_log.new');
        $invoices = $this->invoiceRepository->getAll()
            ->where('status','Open Invoice')
            ->orWhere('status','Overdue invoice')
            ->orderBy('invoice_number', 'asc')
            ->pluck('invoice_number', 'id')->prepend(trans('invoice.invoice_number'),'');

        $this->generateParams();
        return view('user.invoices_payment_log.create', compact('title', 'invoices'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param InvoiceReceivePaymentRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(InvoiceReceivePaymentRequest $request)
    {
        $invoice = $this->invoiceRepository->find($request->invoice_id);
        $recive_payment = $this->invoicePaymentRepository->getAll()->count();
        if($recive_payment == 0){
            $total_fields = 0;
        }else{
            $total_fields = $this->invoicePaymentRepository->all()->last()->id;
        }

        $start_number = Settings::get('invoice_payment_prefix') ;

        $payment_no = Settings::get('invoice_payment_prefix').(is_int($start_number)?$start_number:0 + (isset($total_fields) ? $total_fields : 0) + 1);

        $request->merge(['payment_number'=> $payment_no,'lead_id'=>$invoice->lead_id]);
        $this->invoicePaymentRepository->createPayment($request->all());

        $unpaid_amount_new = round($invoice->unpaid_amount - $request->payment_received, 2);
        $user=$this->userRepository->getUser();
        $partner_id=$user->partner_id;
        
        if ($unpaid_amount_new <= '0') {
            $invoice_data = array(
                'unpaid_amount' => $unpaid_amount_new,
                'status' => 'paid',
            );
        } else {
            $invoice_data = array(
                'unpaid_amount' => $unpaid_amount_new,
            );
        }
        $invoice_data = array(
            'partner_id' => $partner_id,
        );
        
        $invoice->update($invoice_data);

        return redirect("invoices_payment_log");
    }

    public function show($invoiceReceivePayment)
    {
        $invoiceReceivePayment = $this->invoicePaymentRepository->find($invoiceReceivePayment);
        $invoice = $this->invoiceRepository->getAll()->withDeleteList()->find($invoiceReceivePayment->invoice_id);
        $title = trans('invoices_payment_log.show');
        $action = 'show';
        return view('user.invoices_payment_log.show', compact('title', 'action','invoiceReceivePayment','invoice'));
    }

    public function delete($invoiceReceivePayment)
    {
        $invoiceReceivePayment = $this->invoicePaymentRepository->find($invoiceReceivePayment);
        $title = trans('invoices_payment_log.delete');
        return view('user.invoices_payment_log.delete', compact('title', 'invoiceReceivePayment'));
    }

    public function destroy($invoiceReceivePayment)
    {
        $invoiceReceivePayment = $this->invoicePaymentRepository->find($invoiceReceivePayment);
        $invoiceReceivePayment->delete();
        return redirect('invoices_payment_log');
    }


    public function data(Datatables $datatables)
    {
        $invoice_payments = $this->invoicePaymentRepository->getAll()
            ->with('invoice','customer')
            ->get()->map(function ($ip) {
                $invoice = $this->invoiceRepository->getAll()->withDeleteList()->find($ip->invoice_id);
                return [
                    'id' => $ip->id,
                    'payment_number' =>  isset($ip->payment_number)?$ip->payment_number:'',
                    'payment_received' => $ip->payment_received,
                    'invoice_number' => isset($invoice->invoice_number)?$invoice->invoice_number:'',
                    'payment_method' => $ip->payment_method,
                    'payment_date' => $ip->payment_date,
                    'customer' => isset($ip->customer->full_name) ? $ip->customer->full_name:'',
                ];
            });

        return $datatables->collection($invoice_payments)
            ->addColumn('actions', '<a href="{{ url(\'invoices_payment_log/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}">
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>')
            ->removeColumn('id')
            ->rawColumns(['actions'])->make();
    }

    private function generateParams()
    {
        $payment_methods = $this->optionRepository->getAll()
            ->where('category', 'payment_methods')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title', 'value')->prepend(trans('invoice.payment_method'),'');

        view()->share('payment_methods', $payment_methods);
    }
    public function paymentLog(Request $request){
        $payment_details= $this->invoiceRepository->find($request->id );
        return $payment_details;
    }
}
