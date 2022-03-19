<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\UserController;
use App\Repositories\CompanyRepository;
use App\Repositories\ContractRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\LeadRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\OptionRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\SalesOrderRepository;
use Carbon\Carbon;

class DashboardController extends UserController
{
    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;
    /**
     * @var QuotationRepository
     */
    private $quotationRepository;
    /**
     * @var SalesOrderRepository
     */
    private $salesOrderRepository;
    /**
     * @var ContractRepository
     */
    private $contractRepository;
    /**
     * @var OpportunityRepository
     */
    private $opportunityRepository;
    /**
     * @var LeadRepository
     */
    private $leadRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    private $companyRepository;

    /**
     * DashboardController constructor.
     * @param InvoiceRepository $invoiceRepository
     * @param QuotationRepository $quotationRepository
     * @param SalesOrderRepository $salesOrderRepository
     * @param ContractRepository $contractRepository
     * @param OpportunityRepository $opportunityRepository
     * @param LeadRepository $leadRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(
        InvoiceRepository $invoiceRepository,
        QuotationRepository $quotationRepository,
        SalesOrderRepository $salesOrderRepository,
        ContractRepository $contractRepository,
        OpportunityRepository $opportunityRepository,
        LeadRepository $leadRepository,
        OptionRepository $optionRepository,
        CompanyRepository $companyRepository
    )
    {
        parent::__construct();

        $this->invoiceRepository = $invoiceRepository;
        $this->quotationRepository = $quotationRepository;
        $this->salesOrderRepository = $salesOrderRepository;
        $this->contractRepository = $contractRepository;
        $this->opportunityRepository = $opportunityRepository;
        $this->leadRepository = $leadRepository;
        $this->optionRepository = $optionRepository;
        $this->companyRepository = $companyRepository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [];
        for($i=11;$i>=0;$i--)
        {
            $data[] =
                [
                    'month' =>Carbon::now()->subMonth($i)->format('M'),
                    'year' =>Carbon::now()->subMonth($i)->format('Y'),
                    'invoices_unpaid'=>$this->invoiceRepository->getAllForCustomer($this->user->id)->where('status','Overdue Invoice')->where('created_at','LIKE',
                        Carbon::now()->subMonth($i)->format('Y-m').'%')->sum('unpaid_amount'),
                    'quotations' => $this->quotationRepository->getAllForCustomer($this->user->id)->where([
                        ['status','!=',trans('quotation.draft_quotation')]
                    ])->where('created_at','LIKE',
                        Carbon::now()->subMonth($i)->format('Y-m').'%')->count(),
                ];
        }

        return view('customers.index', compact('data'));

    }
}
