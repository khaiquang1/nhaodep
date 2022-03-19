<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\QuotationRepository;
use App\Repositories\SalesOrderRepository;
use Yajra\Datatables\Datatables;

class QuotationConvertedListController extends UserController
{
    private $quotationRepository;

    public $salesOrderRepository;

    public function __construct(
        QuotationRepository $quotationRepository,
        SalesOrderRepository $salesOrderRepository
    )
    {
        parent::__construct();
        $this->quotationRepository = $quotationRepository;
        $this->salesOrderRepository = $salesOrderRepository;
        view()->share('type', 'quotation_converted_list');
    } 

    public function index()
    {
        $title = trans('quotation.converted_list');
        return view('user.quotation.converted_list',compact('title'));
    }


    public function data(Datatables $datatables)
    {
        $convertedList = $this->quotationRepository->getAll()->onlyConvertedLists()->get()
            ->map(function ($convertedList) {
                return [
                    'id' => $convertedList->id,
                    'quotations_number' => $convertedList->quotations_number,
                    'customer' => isset($convertedList->customer) ? $convertedList->customer->full_name : '',
                    'sales_team_id' => $convertedList->salesTeam->salesteam,
                    'sales_person' => isset($convertedList->salesPerson) ? $convertedList->salesPerson->full_name : '',
                    'final_price' => $convertedList->final_price,
                    'payment_term' => $convertedList->payment_term,
                    'status' => $convertedList->status
                ];
            });

        return $datatables->collection($convertedList)
            ->addColumn('actions', '
                                    <a href="{{ url(\'quotation_converted_list/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>')
            ->removeColumn('id')
            ->rawColumns(['actions'])->make(); 
    }
    public function salesOrderList($id)
    {
        $salesorder_id = $this->salesOrderRepository->getAll()->where('quotation_id', $id)->get()->first();
        if(isset($salesorder_id)){
            return redirect('sales_order/' . $salesorder_id->id . '/show');
        }else{
            return redirect('quotation_converted_list')->withErrors(trans('quotation.sales_order_converted'));
        }
    }
}
