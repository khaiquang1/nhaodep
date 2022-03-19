<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\UserController;
use App\Repositories\InvoiceRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\SalesOrderRepository;
use App\Repositories\UserRepository;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Support\Facades\App;
use Yajra\Datatables\Datatables;

class SalesorderController extends UserController
{
    /**
     * @var QuotationRepository
     */
    private $salesOrderRepository;

    private $invoiceRepository;

    private $userRepository;

    public function __construct(
        SalesOrderRepository $salesOrderRepository,
        InvoiceRepository $invoiceRepository,
        UserRepository $userRepository
    )
    {
        parent::__construct();
        $this->salesOrderRepository = $salesOrderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->userRepository = $userRepository;

        view()->share('type', 'customers/sales_order');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('sales_order.sales_orders');
        return view('customers.sales_order.index', compact('title'));
    }

    public function show($saleorder)
    {
        $saleorder = $this->salesOrderRepository->find($saleorder);
        $title = trans('quotation.show');
        return view('customers.sales_order.show', compact('title', 'saleorder'));
    }
    public function data(Datatables $datatables)
    {
        $dateFormat = config('settings.date_format');
        $sales_orders = $this->salesOrderRepository->getAllForCustomer($this->userRepository->getUser()->id)
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
        return $datatables->collection($sales_orders)
            ->addColumn(
                'expired',
                '@if(strtotime(date("m/d/Y"))>strtotime("+".$payment_term." ",strtotime($exp_date)))
                                        <i class="fa fa-bell-slash text-danger" title="{{trans(\'sales_order.salesorder_expired\')}}"></i> 
                                     @else
                                      <i class="fa fa-bell text-warning" title="{{trans(\'sales_order.salesorder_will_expire\')}}"></i> 
                                     @endif'
            )
            ->addColumn('actions', '<a href="{{ url(\'customers/sales_order/\' . $id . \'/show\' ) }}"  title="{{ trans(\'table.details\') }}">
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>')
            ->removeColumn('id')
            ->rawColumns(['expired','actions'])->make();
    }
    public function printQuot($saleorder)
    {
        $saleorder = $this->salesOrderRepository->find($saleorder);
        $filename = 'SaleOrder-' . $saleorder->sale_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4','landscape');
        $pdf->loadView('saleorder_template.'.Settings::get('saleorder_template'), compact('saleorder'));
        return $pdf->download($filename . '.pdf');
    }

    public function ajaxCreatePdf($saleorder)
    {
        $saleorder = $this->salesOrderRepository->find($saleorder);
        $filename = 'SaleOrder-' . $saleorder->sale_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4','landscape');
        $pdf->loadView('saleorder_template.'.Settings::get('saleorder_template'), compact('saleorder'));
        $pdf->save('./pdf/' . $filename . '.pdf');
        $pdf->stream();
        echo url("pdf/" . $filename . ".pdf");

    }
}
