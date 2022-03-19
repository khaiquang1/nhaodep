<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\UserController;
use App\Repositories\InvoicePaymentRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\UserRepository;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Sentinel;
use Yajra\Datatables\Datatables;

class InvoiceController extends UserController
{
    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;

    private $invoicePaymentRepository;

    private $userRepository;

    public function __construct(
        InvoiceRepository $invoiceRepository,
        InvoicePaymentRepository $invoicePaymentRepository,
        UserRepository $userRepository
    )
    {
        parent::__construct();

        view()->share('type', 'customers/invoice');
        $this->invoiceRepository = $invoiceRepository;
        $this->invoicePaymentRepository = $invoicePaymentRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $open_invoice_total = round($this->invoiceRepository->getAllOpenForCustomer($this->user->id)->sum('unpaid_amount'), 3);
        $overdue_invoices_total = round($this->invoiceRepository->all()->where('status','Overdue Invoice')->where('customer_id',Sentinel::getUser()->id)->sum('unpaid_amount'),3);
        $paid_invoices_total = round($this->invoiceRepository->getAll()->withDeleteList()->get()->where('status','Paid Invoice')->where('customer_id',Sentinel::getUser()->id)->sum('final_price'),3);
        $invoices_total_collection = round($this->invoiceRepository->all()->where('customer_id',Sentinel::getUser()->id)->sum('final_price'), 3);

        $title = trans('invoice.invoices');
        return view('customers.invoice.index', compact('title','open_invoice_total','overdue_invoices_total',
            'paid_invoices_total','invoices_total_collection'));
    }


    public function show($invoice)
    {
        $invoice = $this->invoiceRepository->find($invoice);
        $title = trans('invoice.show') . ' ' . $invoice->invoice_number;
        return view('customers.invoice.show', compact('title','invoice'));
    }

    public function data(Datatables $datatables)
    {
        $dateFormat = config('settings.date_format');
        $invoices = $this->invoiceRepository->getAllForCustomer($this->userRepository->getUser()->id)
            ->with('customer')
            ->get()
            ->map(function ($invoice) use ($dateFormat){
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => date($dateFormat, strtotime($invoice->invoice_date)),
                    'customer' => isset($invoice->customer) ? $invoice->customer->full_name : '',
                    'due_date' => date($dateFormat, strtotime($invoice->due_date)),
                    'final_price' => $invoice->final_price,
                    'unpaid_amount' => $invoice->unpaid_amount,
                    'status' => $invoice->status,
                    'payment_term' => isset($invoice->payment_term)?$invoice->payment_term:0,
                    'count_payment' => $invoice->receivePayment->count()
                ];
            });
        return $datatables->collection($invoices)
            ->addColumn('expired', '@if(strtotime(date("m/d/Y"))>strtotime("+".$payment_term."",strtotime($due_date)))
                                        <i class="fa fa-bell-slash text-danger" title="{{trans(\'invoice.invoice_expired\')}}"></i>
                                     @else
                                      <i class="fa fa-bell text-warning" title="{{trans(\'invoice.invoice_will_expire\')}}"></i>
                                     @endif')
            ->addColumn('actions', '<a href="{{ url(\'customers/invoice/\' . $id . \'/show\' ) }}"  title={{ trans("table.details")}}>
                                            <i class="fa fa-fw fa-eye text-primary"></i>  </a>')
            ->removeColumn('id')
            ->removeColumn('count_payment')
            ->removeColumn('payment_term')
            ->rawColumns(['expired','actions'])->make();
    }

    public function printQuot($invoice)
    {
        $invoice = $this->invoiceRepository->find($invoice);
        $filename = 'Invoice-' . $invoice->invoice_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4', 'landscape');
        $pdf->loadView('invoice_template.'.Settings::get('invoice_template'), compact('invoice'));
        return $pdf->download($filename . '.pdf');
    }

    public function ajaxCreatePdf($invoice)
    {
        $invoice = $this->invoiceRepository->find($invoice);
        $filename = 'Invoice-' . Str::slug($invoice->invoice_number);
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4','landscape');
        $pdf->loadView('invoice_template.'.Settings::get('invoice_template'), compact('invoice'));
        $pdf->save('./pdf/' . $filename . '.pdf');
        $pdf->stream();
        echo url("pdf/" . $filename . ".pdf");

    }
}
