<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\UserController;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Customer;
use App\Repositories\ContractRepository;
use Illuminate\Support\Facades\DB;
use Sentinel;
use App\Http\Requests;
use Yajra\Datatables\Datatables;

class ContractController extends UserController
{

    /**
     * @var ContractRepository
     */
    private $contractRepository;

    public function __construct(ContractRepository $contractRepository)
    {
        parent::__construct();

        view()->share('type', 'customers/contract');
        $this->contractRepository = $contractRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('contract.contracts');
        return view('customers.contract.index', compact('title'));
    }

    public function data(Datatables $datatables)
    {
        $companies = Company::where('main_contact_person', $this->user->id)->get();
        $contracts = $this->contractRepository->getAllForCustomer($companies)
            ->with('company','user')
            ->get()
            ->map(function ($contract) {
                return [
                    'id' => $contract->id,
                    'start_date' => $contract->start_date,
                    'end_date' => $contract->end_date,
                    'description' => $contract->description,
                    'name' => isset($contract->company) ? $contract->company->name : '',
                    'user' => isset($contract->user) ? $contract->user->full_name : '',
                ];
            });
        return Datatables::of($contracts)
            ->removeColumn('id')
            ->escapeColumns( [ 'actions' ] )->make();
    }

}
