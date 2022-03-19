<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\UserController;
use App\Repositories\QuotationRepository;
use App\Repositories\SalesOrderRepository;
use App\Repositories\UserRepository;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Support\Facades\App;
use Yajra\Datatables\Datatables;

class QuotationController extends UserController
{

    /**
     * @var QuotationRepository
     */
    private $quotationRepository;

    private $salesOrderRepository;

    private $userRepository;

    public function __construct(
        QuotationRepository $quotationRepository,
        SalesOrderRepository $salesOrderRepository,
        UserRepository $userRepository
    )
    {
        parent::__construct();

        $this->quotationRepository = $quotationRepository;
        $this->salesOrderRepository = $salesOrderRepository;
        $this->userRepository = $userRepository;

        view()->share('type', 'customers/quotation');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('quotation.quotations');
        return view('customers.quotation.index', compact('title'));
    }

    public function show($quotation)
    {
        $quotation = $this->quotationRepository->find($quotation);
        $title = trans('quotation.show');
        $action = "show";
        return view('customers.quotation.show', compact('title', 'quotation','action'));
    }
    /**
     * @return mixed
     */
    public function data(Datatables $datatables)
    {
        $dateFormat = config('settings.date_format');
        $quotations = $this->quotationRepository->getAllForCustomer($this->userRepository->getUser()->id)
              ->where([
                  ['status','!=',trans('quotation.draft_quotation')]
            ])
            ->with('user', 'customer')
            ->get()
            ->map(function ($quotation) use ($dateFormat) {
                return [
                    'id' => $quotation->id,
                    'quotations_number' => $quotation->quotations_number,
                    'customer' => isset($quotation->customer) ?$quotation->customer->full_name : '',
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
                '@if(strtotime(date("m/d/Y")) > strtotime("+".$payment_term." ",strtotime($exp_date)))
                                        <i class="fa fa-bell-slash text-danger" title="{{trans(\'quotation.quotation_expired\')}}"></i> 
                                     @else
                                      <i class="fa fa-bell text-warning" title="{{trans(\'quotation.quotation_will_expire\')}}"></i> 
                                     @endif'
            )
            ->addColumn('actions', '<a href="{{ url(\'customers/quotation/\' . $id . \'/show\' ) }}"  title="{{ trans(\'table.details\') }}">
                                            <i class="fa fa-fw fa-eye text-primary"></i>  </a>
                                             @if(Sentinel::getUser()->hasAccess([\'sales_orders.write\']) || Sentinel::inRole(\'customer\') && $status == \'Send Quotation\' 
                                             && strtotime(date("m/d/Y"))<= strtotime("+".$payment_term." ",strtotime($exp_date)) )
                                            <a href="{{ url(\'customers/quotation/\' . $id . \'/accept_quotation\' ) }}" title="{{ trans(\'quotation.accept_quotation\') }}">
                                            <i class="fa fa-fw fa-check text-primary"></i> </a>
                                            <a href="{{ url(\'customers/quotation/\' . $id . \'/reject_quotation\' ) }}" title="{{ trans(\'quotation.reject_quotation\') }}">
                                            <i class="fa fa-fw fa-close text-danger"></i> </a>
                                    @endif
                                     <a href="{{ url(\'customers/quotation/\' . $id . \'/print_quot\' ) }}"  title="{{ trans(\'table.print\') }}">
                                            <i class="fa fa-fw fa-print text-warning"></i>  </a>')
            ->removeColumn('id')
            ->rawColumns(['actions','expired'])->make();
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
        $filename = 'Quotation-' . $quotation->quotations_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4','landscape');
        $pdf->loadView('quotation_template.'.Settings::get('quotation_template'), compact('quotation'));
        $pdf->save('./pdf/' . $filename . '.pdf');
        $pdf->stream();
        echo url("pdf/" . $filename . ".pdf");
    }

    function acceptQuotation($quotation){
        $quotation = $this->quotationRepository->find($quotation);
        $quotation->update(['status' => 'Quotation Accepted']);
        return redirect('customers/quotation')->with('success_message',trans('quotation.quotation_accepted'));
    }
    function rejectQuotation($quotation){
        $quotation = $this->quotationRepository->find($quotation);
        $quotation->update(['status' => 'Quotation Rejected']);
        return redirect('customers/quotation')->with('quotation_rejected',trans('quotation.quotation_rejected'));
    }
}
