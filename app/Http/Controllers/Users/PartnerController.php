<?php

namespace App\Http\Controllers\Users;

use App\Repositories\ExcelRepository;
use App\Repositories\PartnerRepository;
use App\Repositories\UserRepository;
use App\Http\Controllers\UserController;
use App\Http\Requests\PartnerRequest;

use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;

class PartnerController extends UserController
{
    /** 
     * @var SalesTeamRepository 
     */
    /**
     * @var UserRepository
     */
    private $userRepository;
    private $partnerRepository;

    /**
     * @param SalesTeamRepository $salesTeamRepository
     * @param UserRepository $userRepository
     * @param ExcelRepository $excelRepository
     */
    public function __construct(PartnerRepository $partnerRepository,
                                UserRepository $userRepository)
    {
        parent::__construct();

        $this->userRepository = $userRepository;
        $this->partnerRepository = $partnerRepository;
        view()->share('type', 'partner');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = trans('partner.paerner_title');
        $partnerDataQuery = $this->partnerRepository->getAll(); 
        $totalPartner=$partnerDataQuery->count();
        $partnerDataList=$partnerDataQuery->paginate(15)->appends(request()->query());
        $partner=$partnerDataList->map(function ($partnerData) {
            return [
                'id' => $partnerData->id,
                'name' => $partnerData->name,
                'number_sales' => $partnerData->number_sales,
                'phone' => $partnerData->phone,
                'email' => $partnerData->email,
                'address' => $partnerData->address,
                'status' => $partnerData->status,
                'created_at' => $partnerData->created_at,
            ]; 
        });
        return view('user.partner.index', compact('title','partner','totalPartner','partnerDataList'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('partner.new');
        $this->generateParams();
        return view('user.partner.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(PartnerRequest $request)
    {
       $this->partnerRepository->createPartner($request->all());
        return redirect("partner");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($partner)
    {
        $title = trans('partner.edit');
        $this->generateParams();
        $partner = $this->partnerRepository->findPartner($partner);
        return view('user.partner.edit', compact('title', 'partner'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(PartnerRequest $request, $partner)
    {
        $partner_id = $partner;
        $this->partnerRepository->updatePartner($request->all(), $partner_id);
        return redirect("partner");
    }

    public function show($partner)
    {
        $partner = $this->partnerRepository->find($partner);
        $title = trans('partner.show');
        $action = "show";
        return view('user.partner.show', compact('title', 'partner','action'));
    }

    public function delete($partner)
    {
        $partner = $this->partnerRepository->find($partner);
        $title = trans('partner.delete');
        return view('user.partner.delete', compact('title', 'partner'));
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($partner)
    {
        $this->partnerRepository->deleteTeam($partner);
        return redirect('partner');
    }

    private function generateParams()
    {
    }



    public function postAjaxStore(SalesteamRequest $request)
    {
        $this->salesTeamRepository->create($request->except('created', 'errors', 'selected'));
        return response()->json([], 200);
    }
}
