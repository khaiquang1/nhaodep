<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Http\Requests\CallRequest;
use App\Repositories\CallRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\UserRepository;
use Yajra\Datatables\Datatables;

class OpportunityCallController extends UserController
{
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    private $callRepository;

    private $opportunityRepository;

    public function __construct(CompanyRepository $companyRepository,
                                UserRepository $userRepository,
                                CallRepository $callRepository,
                                OpportunityRepository $opportunityRepository
    )
    {
        parent::__construct();

        $this->companyRepository = $companyRepository;
        $this->userRepository = $userRepository;
        $this->callRepository = $callRepository;
        $this->opportunityRepository = $opportunityRepository;

        view()->share('type', 'opportunitycall');
    }

    public function index($opportunity)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $title = trans('call.opportunity_calls');
        return view('user.opportunitycall.index', compact('title', 'opportunity'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($opportunity)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $title = trans('call.opportunity_new');

        $this->generateParams();
        return view('user.opportunitycall.create', compact('title', 'opportunity'));
    }

    public function store($opportunity, CallRequest $request)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $request->merge(['company_id'=>$opportunity->company_name]);
        $user_id = $this->userRepository->getUser()->id;
        $opportunity->calls()->create($request->all(), ['user_id' => $user_id]);

        return redirect("opportunitycall/" . $opportunity->id);
    }

    public function edit($opportunity, $call)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $call = $this->callRepository->find($call);
        $title = trans('call.opportunity_edit');

        $this->generateParams();

        return view('user.opportunitycall.create', compact('title', 'call', 'opportunity'));
    }


    public function update(CallRequest $request, $opportunity, $call)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $call = $this->callRepository->find($call);
        $request->merge(['company_id'=>$opportunity->company_name]);
        $call->update($request->all());

        return redirect("opportunitycall/" . $opportunity->id);
    }


    public function delete($opportunity, $call)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $call = $this->callRepository->find($call);
        $title = trans('call.opportunity_delete');
        $this->generateParams();
        return view('user.opportunitycall.delete', compact('title', 'call', 'opportunity'));
    }

    public function destroy($opportunity, $call)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $call = $this->callRepository->find($call);
        $call->delete();
        return redirect('opportunitycall/' . $opportunity->id);
    }

    public function data($opportunity,Datatables $datatables)
    {
        $opportunity = $this->opportunityRepository->find($opportunity);
        $calls = $opportunity->calls()
            ->with('responsible', 'company')
            ->get()
            ->map(function ($call) use ($opportunity) {
                $companies = $this->companyRepository->getAll()->orderBy("name", "asc")
                    ->pluck('name', 'id')
                    ->prepend(trans('company.company_name'), '');
                return [
                    'id' => $call->id,
                    'date' => $call->date,
                    'call_summary' => $call->call_summary,
                    'duration' => $call->duration,
                    'company' => $companies[$opportunity->company_name] ,
                    'call_type_id' => $opportunity->id,
                    'responsible' => isset($call->responsible->full_name) ?$call->responsible->full_name : '',
                ];
            });
        return $datatables->collection($calls)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'logged_calls.write\']) || Sentinel::inRole(\'admin\'))
<a href="{{ url(\'opportunitycall/\' . $call_type_id . \'/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning"></i>  </a>
                                            @endif
                                            @if(Sentinel::getUser()->hasAccess([\'logged_calls.delete\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'opportunitycall/\' . $call_type_id . \'/\' . $id . \'/delete\' ) }}"  title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                            @endif')
            ->removeColumn('id')
            ->removeColumn('call_type_id')
            ->rawColumns(['actions'])->make();
    }

    private function generateParams()
    {
        $companies = $this->companyRepository->getAll()->orderBy("name", "asc")
	            ->pluck('name', 'id')
	            ->prepend(trans('dashboard.select_company'), '');

        $staffs = $this->userRepository->getParentStaff()
	            ->pluck('full_name', 'id')
	            ->prepend(trans('dashboard.select_staff'), '');

        view()->share('staffs', $staffs);
        view()->share('companies', $companies);
    }

}
