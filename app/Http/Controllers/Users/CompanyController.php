<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Thumbnail;
use App\Http\Controllers\UserController;
use App\Http\Requests\CompanyRequest;
use App\Repositories\CallRepository;
use App\Repositories\CityRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\CountryRepository;
use App\Repositories\EmailRepository;
use App\Repositories\InvoicePaymentRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\MeetingRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\SalesOrderRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\StateRepository;
use App\Repositories\UserRepository;
use App\Models\Country;

use Yajra\Datatables\Datatables;

class CompanyController extends UserController
{
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    /**
     * @var SalesTeamRepository
     */
    private $salesTeamRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
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

    private $countryRepository;

    private $stateRepository;

    private $cityRepository;

    private $invoicePaymentRepository;

    private $callRepository;

    private $meetingRepository;

    private $emailRepository;

    public function __construct(CompanyRepository $companyRepository,
                                SalesTeamRepository $salesTeamRepository,
                                UserRepository $userRepository,
                                InvoiceRepository $invoiceRepository,
                                QuotationRepository $quotationRepository,
                                SalesOrderRepository $salesOrderRepository,
                                CountryRepository $countryRepository,
                                StateRepository $stateRepository,
                                CityRepository $cityRepository,
                                InvoicePaymentRepository $invoicePaymentRepository,
                                CallRepository $callRepository,
                                MeetingRepository $meetingRepository,
                                EmailRepository $emailRepository
    )
    {
        parent::__construct();

        $this->middleware('authorized:contacts.read', ['only' => ['index', 'data']]);
        $this->middleware('authorized:contacts.write', ['only' => ['create', 'store', 'update', 'edit']]);
        $this->middleware('authorized:contacts.delete', ['only' => ['delete']]);

        $this->companyRepository = $companyRepository;
        $this->salesTeamRepository = $salesTeamRepository;
        $this->userRepository = $userRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->quotationRepository = $quotationRepository;
        $this->salesOrderRepository = $salesOrderRepository;
        $this->countryRepository = $countryRepository;
        $this->stateRepository = $stateRepository;
        $this->cityRepository = $cityRepository;
        $this->invoicePaymentRepository = $invoicePaymentRepository;
        $this->callRepository = $callRepository;
        $this->meetingRepository = $meetingRepository;
        $this->emailRepository = $emailRepository;

        view()->share('type', 'company');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('company.companies');
        return view('user.company.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('company.new');

        $this->generateParams();

        return view('user.company.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(CompanyRequest $request)
    {
        if ($request->hasFile('company_avatar_file')) {
            $file = $request->file('company_avatar_file');
            $file = $this->companyRepository->uploadAvatar($file);

            $request->merge([
                'company_avatar' => $file->getFileInfo()->getFilename(),
            ]);
            $this->generateThumbnail($file);
        }
        $userData=$this->userRepository->getUser();
        $this->partner_id=$userData->partner_id;
        if($this->partner_id){
            $request->merge([
                'partner_id' => $this->partner_id,
            ]);
        }
        $this->companyRepository->create($request->except('company_avatar_file'));

        return redirect("company");
    }

    public function edit($company)
    {
        $company = $this->companyRepository->find($company);
        $title = trans('company.edit');
        $states = $this->stateRepository->orderBy('name', 'asc')->findByField('country_id', $company->country_id)->pluck('name', 'id');
        $cities = $this->cityRepository->orderBy('name', 'asc')->findByField('state_id', $company->state_id)->pluck('name', 'id');

        $this->generateParams();

        return view('user.company.edit', compact('title', 'company','cities','states'));
    }

    public function update(CompanyRequest $request, $company)
    {
        $company = $this->companyRepository->find($company);
        if ($request->hasFile('company_avatar_file')) {
            $file = $request->file('company_avatar_file');
            $file = $this->companyRepository->uploadAvatar($file);

            $request->merge([
                'company_avatar' => $file->getFileInfo()->getFilename(),
            ]);
            $this->generateThumbnail($file);
        }
        $userData=$this->userRepository->getUser();
        $this->partner_id=$userData->partner_id;
        if($this->partner_id){
            $request->merge([
                'partner_id' => $this->partner_id,
            ]);
        }
        $company->update($request->except('company_avatar_file'));

        return redirect("company");
    }

    public function show($company)
    {
        $company = $this->companyRepository->find($company);
        $title = trans('company.details');
        $action = 'show';

        $agent_id = $company->customerCompany->pluck('user_id','user_id');
        $open_invoices = round($this->invoiceRepository->all()->where('status',trans('invoice.open_invoice'))->whereIn('customer_id',$agent_id)->sum('final_price'), 3);
        $overdue_invoices = round($this->invoiceRepository->all()->where('status',trans('invoice.overdue_invoice'))->whereIn('customer_id',$agent_id)->sum('unpaid_amount'), 3);
        $paid_invoices = round($this->invoiceRepository->getAll()->onlyPaidLists()->get()->whereIn('customer_id',$agent_id)->sum('final_price'),3);
        $total_invoices = round($this->invoiceRepository->all()->where('is_delete_list',0)->where('status',trans('invoice.open_invoice'))->whereIn('customer_id',$agent_id)->sum('final_price'),3);

        $quotations_total = round($this->quotationRepository->all()->whereIn('customer_id',$agent_id)->sum('final_price'), 3);;
        $salesorder_total = round($this->salesOrderRepository->all()->whereIn('customer_id',$agent_id)->sum('final_price'), 3);;

        $salesorder =  $this->salesOrderRepository->all()->whereIn('customer_id',$agent_id)->count();

        $invoices =  $this->invoiceRepository->getAll()->where([
            ['status','!=',trans('invoice.paid_invoice')]
        ])->whereIn('customer_id',$agent_id)->count();


        $quotations =  $this->quotationRepository->all()->whereIn('customer_id',$agent_id)->count();

        $calls = $this->callRepository->all()->where('company_id',$company->id)->count();

        $meeting = $this->meetingRepository->all()->where('company_name',$company->id)->count();

        $emails = $this->emailRepository->all()->whereIn('to',$agent_id)->count();

        return view('user.company.delete', compact('title', 'company','action','total_invoices','open_invoices','paid_invoices',
            'quotations_total','salesorder','quotations','invoices','calls','meeting','emails','overdue_invoices',
            'salesorder_total'));
    }

    public function delete($company)
    {
        $company = $this->companyRepository->find($company);
        $title = trans('company.delete');

        $agent_id = $company->customerCompany->pluck('user_id','user_id');
        $open_invoices = round($this->invoiceRepository->all()->where('status',trans('invoice.open_invoice'))->whereIn('customer_id',$agent_id)->sum('final_price'), 3);
        $overdue_invoices = round($this->invoiceRepository->all()->where('status',trans('invoice.overdue_invoice'))->whereIn('customer_id',$agent_id)->sum('unpaid_amount'), 3);
        $paid_invoices = round($this->invoiceRepository->getAll()->onlyPaidLists()->get()->whereIn('customer_id',$agent_id)->sum('final_price'),3);
        $total_invoices = round($this->invoiceRepository->all()->where('is_delete_list',0)->where('status',trans('invoice.open_invoice'))->whereIn('customer_id',$agent_id)->sum('final_price'),3);

        $quotations_total = round($this->quotationRepository->all()->whereIn('customer_id',$agent_id)->sum('final_price'), 3);;
        $salesorder_total = round($this->salesOrderRepository->all()->whereIn('customer_id',$agent_id)->sum('final_price'), 3);;

        $salesorder =  $this->salesOrderRepository->all()->whereIn('customer_id',$agent_id)->count();

        $invoices =  $this->invoiceRepository->getAll()->where([
            ['status','!=',trans('invoice.paid_invoice')]
        ])->whereIn('customer_id',$agent_id)->count();


        $quotations =  $this->quotationRepository->all()->whereIn('customer_id',$agent_id)->count();

        $calls = $this->callRepository->all()->where('company_id',$company->id)->count();

        $meeting = $this->meetingRepository->all()->where('company_name',$company->id)->count();

        $emails = $this->emailRepository->all()->whereIn('to',$agent_id)->count();

        return view('user.company.delete', compact('title', 'company','action','total_invoices','open_invoices','paid_invoices',
            'quotations_total','salesorder','quotations','invoices','calls','meeting','emails','overdue_invoices',
            'salesorder_total'));
    }

    public function destroy($company)
    {
        $company = $this->companyRepository->find($company);
        $company->delete();
        return redirect('company');
    }

    public function data(Datatables $datatables)
    {
        $company = $this->companyRepository->getAll()
            ->with('contactPerson','opportunityCompany')
            ->get()
            ->map(function ($comp) {
            return [
                'id' => $comp->id,
                'name' => $comp->name,
                'website' => $comp->website,
//                'customer' => isset($comp->contactPerson) ?$comp->contactPerson->full_name : '--',
                'phone' => $comp->phone,
                'count_uses' => $comp->customerCompany->count()+
                    $comp->opportunityCompany->count()
            ];
        });

        return $datatables->collection($company)

            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'contacts.write\']) || Sentinel::inRole(\'admin\'))
                                    <a href="{{ url(\'company/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning "></i> </a>
                                    @endif
                                    <a href="{{ url(\'company/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                    @if(Sentinel::getUser()->hasAccess([\'contacts.delete\']) && $count_uses==0 || Sentinel::inRole(\'admin\') && $count_uses==0)
                                    <a href="{{ url(\'company/\' . $id . \'/delete\' ) }}"  title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                       @endif')

            ->removeColumn('id')
            ->removeColumn('count_uses')
            ->rawColumns(['actions'])->make();
    }

    private function generateParams()    
    {
        $countries = Country::orderBy('name', 'asc')->get()->pluck('name','id')->prepend(trans('company.select_country'), '');
        //->pluck('name', 'id')->prepend(trans('company.select_country'), '')
        view()->share('countries', $countries);
    }
    /**
     * @param $file
     */
    private function generateThumbnail($file)
    {
        Thumbnail::generate_image_thumbnail(public_path() . '/uploads/company/' . $file->getFileInfo()->getFilename(),
            public_path() . '/uploads/company/' . 'thumb_' . $file->getFileInfo()->getFilename());
    }

}
