<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\InvoiceRepository;
use App\Repositories\SalesOrderRepository;
use Yajra\Datatables\Datatables;

class SalesorderInvoiceListController extends UserController
{
    private $salesOrderRepository;
    private $invoiceRepository;

    public function __construct(
        SalesOrderRepository $salesOrderRepository,
        InvoiceRepository $invoiceRepository
    )
    {
        parent::__construct();
        $this->salesOrderRepository = $salesOrderRepository;
        $this->invoiceRepository = $invoiceRepository;
        view()->share('type', 'salesorder_invoice_list');
    }

    public function index()
    {
        $title = trans('sales_order.salesorder_invoice_list');
        return view('user.sales_order.salesorder_invoice_list',compact('title'));
    }


    public function data(Datatables $datatables)
    {
        $salesOrderDeleteList = $this->salesOrderRepository->getAll()->onlyInvoiceConvertedLists()->get()
            ->map(function ($salesOrderDeleteList) {
                return [
                    'id' => $salesOrderDeleteList->id,
                    'sale_number' => $salesOrderDeleteList->sale_number,
                    'date' => $salesOrderDeleteList->date,
                    'exp_date' => $salesOrderDeleteList->exp_date,
                    'payment_term' => $salesOrderDeleteList->payment_term,
                    'customer' => isset($salesOrderDeleteList->customer) ?$salesOrderDeleteList->customer->full_name : '',
                    'person' => isset($salesOrderDeleteList->user) ?$salesOrderDeleteList->user->full_name : '',
                    'final_price' => $salesOrderDeleteList->final_price,
                    'status' => $salesOrderDeleteList->status
                ];
            });

        return $datatables->collection($salesOrderDeleteList)
            ->addColumn('actions', '
                                    <a href="{{ url(\'salesorder_invoice_list/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>')
            ->removeColumn('id')
            ->rawColumns(['actions'])->make();
    }
    public function invoiceList($id)
    {
        $invoice_id = $this->invoiceRepository->getAll()->where('order_id', $id)->get()->first();
        if(isset($invoice_id)){
            return redirect('invoice/' . $invoice_id->id . '/show');
        }else{
            return redirect('salesorder_invoice_list')->withErrors(trans('quotation.converted_invoice'));
        }
    }
}
