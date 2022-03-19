<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\UserController;
use App\Repositories\CompanyRepository;
use App\Repositories\InvoicePaymentRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\OptionRepository;
use App\Repositories\UserRepository;
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

        view()->share('type', 'customers/invoices_payment_log');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('invoices_payment_log.invoices_payment_log');
        return view('customers.invoices_payment_log.index', compact('title'));
    }

    public function show($invoiceReceivePayment)
    {
        $invoiceReceivePayment = $this->invoicePaymentRepository->getAllForCustomer($this->user->id)->find($invoiceReceivePayment);
        $invoice = $this->invoiceRepository->getAll()->withDeleteList()->find($invoiceReceivePayment->invoice_id);
        if (!isset($invoiceReceivePayment)){
            abort(404);
        }
        $title = trans('invoices_payment_log.show');
        $action = 'show';
        return view('customers.invoices_payment_log.show', compact('title', 'action','invoiceReceivePayment','invoice'));
    }


    public function data(Datatables $datatables)
    {
        $invoice_payments = $this->invoicePaymentRepository->getAllForCustomer($this->user->id)
            ->with('customer','user')
            ->get()->map(function ($ip) {
                $invoice = $this->invoiceRepository->getAll()->withDeleteList()->find($ip->invoice_id);
                return [
                    'id' => $ip->id,
                    'payment_number' =>  $ip->payment_number,
                    'payment_received' => $ip->payment_received,
                    'invoice_number' => isset($invoice->invoice_number)?$invoice->invoice_number:'',
                    'payment_method' => $ip->payment_method,
                    'payment_date' => $ip->payment_date,
                    'customer' => isset($ip->customer->full_name) ? $ip->customer->full_name:'',
                ];
            });

        return $datatables->collection($invoice_payments)
            ->addColumn('actions', '<a href="{{ url(\'customers/invoices_payment_log/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}">
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>')
            ->removeColumn('id')
            ->rawColumns(['expired','actions'])->make();
    }

    public function paymentLog(Request $request){
        $payment_details= $this->invoiceRepository->getAll()->where( 'id', $request->id )->get()->first();
        return $payment_details;
    }
}
