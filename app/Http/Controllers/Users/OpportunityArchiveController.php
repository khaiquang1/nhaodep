<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\OpportunityRepository;
use Yajra\Datatables\Datatables;
class OpportunityArchiveController extends UserController
{
    private $opportunityRepository;

    public function __construct(
        OpportunityRepository $opportunityRepository
    )
    {
        parent::__construct();
        $this->opportunityRepository = $opportunityRepository;

        view()->share('type', 'opportunity_archive');
    }
    public function index()
    {
        $title = trans('opportunity.archive');
        return view('user.opportunity_archive.index',compact('title'));
    }

    public function show($opportunity)
    {
        $opportunity = $this->opportunityRepository->getAll()->onlyArchived()->find($opportunity);
        $title = 'Show Archive';
        $action = 'show';
        return view('user.opportunity_archive.show', compact('title', 'opportunity','action','company_name'));
    }
    public function data(Datatables $datatables)
    {
        $dateFormat= config('settings.date_format');
        $opportunityArchive = $this->opportunityRepository->getAll()->onlyArchived()->get()
            ->map(function ($opportunityArchive) use ($dateFormat) {
                return [
                    'id' => $opportunityArchive->id,
                    'opportunity' => $opportunityArchive->opportunity,
                    'company' => isset($opportunityArchive->companies->name)?$opportunityArchive->companies->name:null,
                    'next_action' => date($dateFormat,strtotime($opportunityArchive->next_action)),
                    'stages' => $opportunityArchive->stages,
                    'expected_revenue' => $opportunityArchive->expected_revenue,
                    'probability' => $opportunityArchive->probability,
                    'sales_team_id' => @$opportunityArchive->salesTeam->salesteam,
                    'salesteam' =>  isset($opportunityArchive->staffs->full_name)?$opportunityArchive->staffs->full_name:null,
                    'lost_reason' => $opportunityArchive->lost_reason,
                ];
            });

        return $datatables->collection($opportunityArchive)

            ->addColumn('actions', '
                                    <a href="{{ url(\'opportunity_archive/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                    ')
            ->removeColumn('id')
            ->rawColumns(['actions'])->make();
    }
}
