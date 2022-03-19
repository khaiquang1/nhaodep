<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\InvoiceRepository;
use App\Repositories\QuotationRepository;
use Yajra\Datatables\Datatables;

class QuotationInvoiceListController extends UserController
{
    private $quotationRepository;
    private $invoiceRepository;

    public function __construct(
        QuotationRepository $quotationRepository,
        InvoiceRepository $invoiceRepository
    )
    {
        parent::__construct();
        $this->quotationRepository = $quotationRepository;
        $this->invoiceRepository = $invoiceRepository;

        view()->share('type', 'quotation_invoice_list');
    }

    public function index()
    {
        $title = trans('quotation.quotation_invoice_list');
        return view('user.quotation.quotation_invoice_list',compact('title'));
    }


    public function data(Datatables $datatables)
    {
        $quotationInvoiceList = $this->quotationRepository->getAll()->onlyQuotationInvoiceLists()->get()
            ->map(function ($quotationInvoiceList) {
                return [
                    'id' => $quotationInvoiceList->id,
                    'quotations_number' => $quotationInvoiceList->quotations_number,
                    'customer' => isset($quotationInvoiceList->customer) ? $quotationInvoiceList->customer->full_name : '',
                    'sales_team_id' => $quotationInvoiceList->salesTeam->salesteam,
                    'sales_person' => isset($quotationInvoiceList->salesPerson) ? $quotationInvoiceList->salesPerson->full_name : '',
                    'final_price' => $quotationInvoiceList->final_price,
                    'payment_term' => $quotationInvoiceList->payment_term,
                    'status' => $quotationInvoiceList->status
                ];
            });

        return $datatables->collection($quotationInvoiceList)
            ->addColumn('actions', '
                                    <a href="{{ url(\'quotation_invoice_list/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>')
            ->removeColumn('id')
            ->rawColumns(['actions'])->make();
    }
    public function invoiceList($id)
    {
        $invoice_id = $this->invoiceRepository->getAll()->where('quotation_id',$id)->get()->last();
        if(isset($invoice_id)){
            return redirect('invoice/' . $invoice_id->id . '/show');
        }else{
            $invoice = $this->invoiceRepository->getAll()->withDeleteList()->where('quotation_id',$id)->get()->first();
            if ($invoice->is_delete_list==1){
                return redirect('quotation_invoice_list')->withErrors(trans('quotation.invoice_deleted'));
            }else{
                return redirect('quotation_invoice_list')->withErrors(trans('quotation.converted_invoice'));
            }
        }
    }
}
