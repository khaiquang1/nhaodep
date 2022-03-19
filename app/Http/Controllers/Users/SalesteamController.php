<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Http\Requests\SalesteamRequest;
use App\Repositories\ExcelRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Helpers\ExcelfileValidator;

class SalesteamController extends UserController
{
    /**
     * @var SalesTeamRepository
     */
    private $salesTeamRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ExcelRepository
     */
    private $excelRepository;

    /**
     * @param SalesTeamRepository $salesTeamRepository
     * @param UserRepository $userRepository
     * @param ExcelRepository $excelRepository
     */
    public function __construct(SalesTeamRepository $salesTeamRepository,
                                UserRepository $userRepository,
                                ExcelRepository $excelRepository)
    {
        $this->middleware('authorized:sales_team.read', ['only' => ['index', 'data']]);
        $this->middleware('authorized:sales_team.write', ['only' => ['create', 'store', 'update', 'edit']]);
        $this->middleware('authorized:sales_team.delete', ['only' => ['delete']]);

        parent::__construct();

        $this->salesTeamRepository = $salesTeamRepository;
        $this->userRepository = $userRepository;
        $this->excelRepository = $excelRepository;


        view()->share('type', 'salesteam');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('salesteam.salesteams');
        return view('user.salesteam.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('salesteam.new');

        $this->generateParams();

        return view('user.salesteam.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(SalesteamRequest $request)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $arrayupdate=$request->all();
        if($request->invoice_target=="" || $request->invoice_target!=0){
			$arrayupdate["invoice_target"]=str_replace(",","",$request->invoice_target);
        }
        $arrayupdate["partner_id"]=$partner_id;
        $this->salesTeamRepository->createTeam($arrayupdate);

        return redirect("salesteam");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($salesteam)
    {
        $title = trans('salesteam.edit');

        $this->generateParams();
        $salesteam = $this->salesTeamRepository->findTeam($salesteam);
        return view('user.salesteam.edit', compact('title', 'salesteam'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(SalesteamRequest $request, $salesteam)
    {
        $salesteam_id = $salesteam;
        /*
        $request->merge([
            'quotations' => isset($request->quotations) ? 1 : 0,
            'leads' => isset($request->leads) ? 1 : 0,
            'opportunities' => isset($request->opportunities) ? 1 : 0,
        ]); */
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $arrayupdate=$request->all();
        if($request->invoice_target=="" || $request->invoice_target!=0){
			$arrayupdate["invoice_target"]=str_replace(",","",$request->invoice_target);
        }
        $arrayupdate["partner_id"]=$partner_id;

        $this->salesTeamRepository->updateTeam($arrayupdate, $salesteam_id);
        return redirect("salesteam");
    }

    public function show($salesteam)
    {
        $salesteam = $this->salesTeamRepository->find($salesteam);
        $title = trans('salesteam.show');
        $action = "show";
        return view('user.salesteam.show', compact('title', 'salesteam','action'));
    }

    public function delete($salesteam)
    {
        $salesteam = $this->salesTeamRepository->find($salesteam);
        $title = trans('salesteam.delete');
        return view('user.salesteam.delete', compact('title', 'salesteam'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($salesteam)
    {
        $this->salesTeamRepository->deleteTeam($salesteam);
        return redirect('salesteam');
    }

    public function data(Datatables $datatables)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $salesteam = $this->salesTeamRepository->getAll()->where('partner_id',$partner_id)
            ->with('actualInvoice')
            ->get()
            ->map(function ($salesteam) {
            return [
                'id' => $salesteam->id,
                'salesteam' => $salesteam->salesteam,
                'target' => number_format($salesteam->invoice_target),
                'actual_invoice' => number_format($salesteam->actualInvoice->sum('grand_total')),
                'count_uses' => $salesteam->agentSalesteam->count() +
                    $salesteam->opportunitySalesteam->count() +
                    $salesteam->quotationSalesteam->count() +
                    $salesteam->salesorderSalesteam->count() +
                    $salesteam->actualInvoice->count()

            ];
        });

        return $datatables->collection($salesteam)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'sales_team.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'salesteam/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning"></i>  </a>
                                     @endif
                                     @if(Sentinel::getUser()->hasAccess([\'sales_team.read\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'salesteam/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                    @endif
                                     @if(Sentinel::getUser()->hasAccess([\'sales_team.delete\']) && $count_uses==0 || Sentinel::inRole(\'admin\') && $count_uses==0)
                                        <a href="{{ url(\'salesteam/\' . $id . \'/delete\' ) }}"  title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                     @endif')
            ->removeColumn('id')
            ->removeColumn('count_uses')
            ->rawColumns(['actions'])->make();
    }

    private function generateParams()
    {
        $staffs = $this->userRepository->getParentStaff()->pluck('full_name', 'id')->prepend(trans(''), '');
        view()->share('staffs', $staffs);
    }

    public function downloadExcelTemplate()
    {
        if (ob_get_length()) ob_end_clean();
        $path = base_path('resources/excel-templates/sales-teams.xlsx');

        if (file_exists($path)) {
            return response()->download($path);
        }

        return 'File not found!';
    }
    public function getImport()
    {
        $title = trans('salesteam.salesteams');
        return view('user.salesteam.import', compact('title'));
    }

    public function postImport(Request $request)
    {
        if(! ExcelfileValidator::validate( $request ))
        {
            return response('invalid File or File format', 500);
        }
        $reader = $this->excelRepository->load($request->file('file'));
        $data = [
            'salesteams' => $reader->all(),
            'staff' => $this->userRepository->getParentStaff()->map(function ($user) {
                return [
                    'text' => $user->full_name,
                    'id' => $user->id
                ];
            })->values(),
        ];
        return response()->json(compact('data'), 200);
    }

    public function postAjaxStore(SalesteamRequest $request)
    {
        $this->salesTeamRepository->create($request->except('created', 'errors', 'selected'));
        return response()->json([], 200);
    }
}
