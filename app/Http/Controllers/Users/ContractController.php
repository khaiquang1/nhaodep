<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Thumbnail;
use App\Http\Controllers\UserController;
use App\Http\Requests\ContractRequest;
use App\Models\Contract;
use App\Repositories\CompanyRepository;
use App\Repositories\ContractRepository;
use App\Repositories\UserRepository;
use Sentinel;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class ContractController extends UserController
{
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ContractRepository
     */
    private $contractRepository;

    /**
     * @param CompanyRepository $companyRepository
     * @param UserRepository $userRepository
     * @param ContractRepository $contractRepository
     * @internal param CompanyRepository $
     */
    public function __construct(CompanyRepository $companyRepository,
                                UserRepository $userRepository,
                                ContractRepository $contractRepository)
    {
        parent::__construct();

        $this->middleware('authorized:contracts.read', ['only' => ['index', 'data']]);
        $this->middleware('authorized:contracts.write', ['only' => ['create', 'store', 'update', 'edit']]);
        $this->middleware('authorized:contracts.delete', ['only' => ['delete']]);
        //$this->middleware('enabled:contracts');

        $this->companyRepository = $companyRepository;
        $this->userRepository = $userRepository;
        $this->contractRepository = $contractRepository;


        view()->share('type', 'contract');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('contract.contracts');
        return view('user.contract.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('contract.new');

        $this->generateParams();

        return view('user.contract.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ContractRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ContractRequest $request)
    {
        if ($request->hasFile('real_signed_contract_file')) {
            $file = $request->file('real_signed_contract_file');
            $file = $this->contractRepository->uploadRealSignedContract($file);

            $request->merge([
                'real_signed_contract' => $file->getFileInfo()->getFilename()
            ]);
            $this->generateThumbnail($file);
        }

        $this->contractRepository->create($request->except('real_signed_contract_file'));

        return redirect("contract");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Contract $contract
     * @return \Illuminate\Http\Response
     */
    public function edit(Contract $contract)
    {
        $title = trans('contract.edit');

        $this->generateParams();

        return view('user.contract.edit', compact('title', 'contract'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  ContractRequest $request
     * @param  Contract $contract
     * @return \Illuminate\Http\Response
     */
    public function update(ContractRequest $request, Contract $contract)
    {
        if ($request->hasFile('real_signed_contract_file')) {
            $file = $request->file('real_signed_contract_file');
            $file = $this->contractRepository->uploadRealSignedContract($file);

            $request->merge([
                'real_signed_contract' => $file->getFileInfo()->getFilename()
            ]);
            $this->generateThumbnail($file);
        }

        $contract->update($request->except('real_signed_contract_file'));

        return redirect("contract");
    }

    public function show(Contract $contract)
    {
        $title = trans('contract.show');
        $action = 'show';
        $this->generateParams();
        return view('user.contract.show', compact('title', 'contract','action'));
    }

    public function delete(Contract $contract)
    {
        $title = trans('contract.delete');
        $this->generateParams();
        return view('user.contract.delete', compact('title', 'contract'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Contract $contract
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contract $contract)
    {
        $contract->delete();
        return redirect("contract");
    }

    public function data(Datatables $datatables)
    {
        $contracts = $this->contractRepository->getAll()
            ->with('company', 'user')
            ->get()
            ->map(function ($contract) {
                return [
                    'id' => $contract->id,
                    'start_date' => $contract->start_date,
                    'end_date' => $contract->end_date,
                    'description' => $contract->description,
                    'name' => isset($contract->company) ? $contract->company->name : '',
                    'user' => isset($contract->responsible) ? $contract->responsible->full_name : '',
                ];
            });

        return $datatables->collection($contracts)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'contracts.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'contract/\' . $id . \'/edit\' ) }}"  title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning"></i> </a>
                                            @endif
                                     @if(Sentinel::getUser()->hasAccess([\'contracts.read\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'contract/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                    @endif
                                    @if(Sentinel::getUser()->hasAccess([\'contracts.delete\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'contract/\' . $id . \'/delete\' ) }}"  title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-times text-danger"></i></a>
                                     @endif')
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }

    private function generateParams()
    {
        $companies = $this->companyRepository->getAll()->orderBy("name", "asc")
	            ->pluck('name', 'id')
	            ->prepend(trans('dashboard.select_company'), '');

        $staffs = $this->userRepository->getStaff()->pluck('full_name', 'id')
                                             ->prepend(trans('dashboard.select_staff'), '');

        view()->share('companies', $companies);
        view()->share('staffs', $staffs);
    }


    /**
     * @param $file
     */
    private function generateThumbnail($file)
    {
        Thumbnail::generate_image_thumbnail(public_path() . '/uploads/contract/' . $file->getFileInfo()->getFilename(),
            public_path() . '/uploads/contract/' . 'thumb_' . $file->getFileInfo()->getFilename());
    }
}