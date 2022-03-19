<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\CompanyRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\UserRepository;
use Yajra\Datatables\Datatables;

class OpportunityDeleteListController extends UserController
{

    private $userRepository;

    private $opportunityRepository;

    private $companyRepository;

    public function __construct(
        UserRepository $userRepository,
        OpportunityRepository $opportunityRepository,
        CompanyRepository $companyRepository
    )
    {
        parent::__construct();
        $this->opportunityRepository = $opportunityRepository;
        $this->userRepository = $userRepository;
        $this->companyRepository = $companyRepository;


        view()->share('type', 'opportunity_delete_list');
    }
    public function index()
    {
        $title = trans('opportunity.delete_list');
        return view('user.opportunity_delete_list.index',compact('title'));
    }

    public function show($opportunity)
    {
        $opportunity = $this->opportunityRepository->getAll()->onlyDeleteLists()->find($opportunity);
        $title = 'Show Delete List';
        $action = 'show';
        $this->generateParams();
        return view('user.opportunity_delete_list.show', compact('title', 'opportunity','action'));
    }

    public function delete($opportunity){
        $opportunity = $this->opportunityRepository->getAll()->onlyDeleteLists()->find($opportunity);
        $title = 'Restore Delete List';
        $action = 'delete';
        $this->generateParams();
        return view('user.opportunity_delete_list.restore', compact('title', 'opportunity','action'));
    }

    public function restoreOpportunity($opportunity)
    {
        $opportunity = $this->opportunityRepository->getAll()->onlyDeleteLists()->find($opportunity);
        $opportunity->update(['is_delete_list'=>0]);
        return redirect('opportunity');
    }

    public function data(Datatables $datatables)
    {
        $dateFormat= config('settings.date_format');
        $opportunityDeleteList = $this->opportunityRepository->getAll()->onlyDeleteLists()->get()
            ->map(function ($opportunityDeleteList) use ($dateFormat) {
                return [
                    'id' => $opportunityDeleteList->id,
                    'opportunity' => $opportunityDeleteList->opportunity,
                    'company' => $opportunityDeleteList->companies->name ? $opportunityDeleteList->companies->name: null,
                    'next_action' => date($dateFormat,strtotime($opportunityDeleteList->next_action)),
                    'stages' => $opportunityDeleteList->stages,
                    'expected_revenue' => $opportunityDeleteList->expected_revenue,
                    'probability' => $opportunityDeleteList->probability,
                    'sales_team_id' => $opportunityDeleteList->salesTeam->salesteam,
                    'salesteam' => $opportunityDeleteList->salesTeam->salesteam ? $opportunityDeleteList->salesTeam->salesteam : null,
                ];
            });

        return $datatables->collection($opportunityDeleteList)

            ->addColumn('actions', '
                                    <a href="{{ url(\'opportunity_delete_list/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                    @if(Sentinel::getUser()->hasAccess([\'opportunities.write\']) || Sentinel::inRole(\'admin\'))
                                    <a href="{{ url(\'opportunity_delete_list/\' . $id . \'/restore\' ) }}"  title="{{ trans(\'table.restore\') }}">
                                            <i class="fa fa-fw fa-undo text-success"></i> </a>
                                       @endif')
            ->removeColumn('id')
            ->rawColumns(['actions'])->make();
    }
    private function generateParams(){
        $user = $this->userRepository->all();
        $company_name = $this->companyRepository->all()->pluck('name','id');
        view()->share('user', $user);
        view()->share('company_name',$company_name);
    }
}
