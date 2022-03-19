<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\CompanyRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\SalesTeamRepository;

use App\Repositories\UserRepository;
use Yajra\Datatables\Datatables;

class OpportunityConvertedListController extends UserController
{
    private $userRepository;

    private $opportunityRepository;
    private $companyRepository;
    private $quotationRepository;
    private $salesTeamRepository;

    public function __construct(
        UserRepository $userRepository,
        OpportunityRepository $opportunityRepository,
        CompanyRepository $companyRepository,
        QuotationRepository $quotationRepository,
        SalesTeamRepository $salesTeamRepository
    )
    {
        parent::__construct();
        $this->opportunityRepository = $opportunityRepository;
        $this->userRepository = $userRepository;
        $this->companyRepository = $companyRepository;
        $this->quotationRepository = $quotationRepository;
        $this->salesTeamRepository = $salesTeamRepository;

        view()->share('type', 'opportunity_converted_list');
    }

    public function index()
    {

        $title = trans('opportunity.converted_list');
        return view('user.opportunity.converted_list',compact('title'));
    }


    public function data(Datatables $datatables)
    {
        $dateFormat = config('settings.date_format');
        $convertedList = $this->opportunityRepository->getAll()->onlyConvertedLists()->get()
            ->map(function ($convertedList) use ($dateFormat){
                return [
                    'id' => $convertedList->id,
                    'opportunity' => $convertedList->opportunity,
                    'company' => "",//$convertedList->companies->name ? $convertedList->companies->name: null,
                    'next_action' => date($dateFormat,strtotime($convertedList->next_action)),
                    'stages' => $convertedList->stages,
                    'expected_revenue' => number_format($convertedList->expected_revenue),
                    'probability' => $convertedList->probability,
                    'salesteam' => $convertedList->salesTeam ? $convertedList->salesTeam->salesteam : null,
                    'sales_persion' => $this->userRepository->getAll()->where('id','=',$convertedList->sales_person_id)->pluck('first_name')->first(),
                ];
            });
        return $datatables->collection($convertedList)
            ->addColumn('actions', '
                                    <a href="{{ url(\'convertedlist_view/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>')
            ->removeColumn('id')
            ->rawColumns(['actions'])->make();
    }
    public function quatationList($id)
    {
        $quotation_id = $this->quotationRepository->getAll()->where('opportunity_id', $id)->get()->first();
        if(isset($quotation_id)){
            return redirect('quotation/' . $quotation_id->id . '/show');
        }else{
            flash(trans('opportunity.converted_salesorder'))->error();
            return redirect('opportunity_converted_list');
        }
    }

}
