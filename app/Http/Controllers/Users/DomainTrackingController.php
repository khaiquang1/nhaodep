<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Thumbnail;
use App\Http\Controllers\UserController;
use App\Repositories\ExcelRepository;
use App\Repositories\OptionRepository;
use App\Http\Requests\DomainTrackingRequest;
use App\Repositories\DomainTrackingRepository;
use App\Models\DomainTracking;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Storage;
use Validator;

class DomainTrackingController extends UserController
{

    /**
     * @var DomainTrackingRepository
     */
    private $domainTrackingRepository;
    /**
     * @var ExcelRepository
     */
    private $excelRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * @param DomainTrackingRepository $DomainTrackingRepository
     * @param ExcelRepository $excelRepository
     * @param OptionRepository $optionRepository
     */
    public function __construct(DomainTrackingRepository $domainTrackingRepository, OptionRepository $optionRepository)
    {

        parent::__construct();

        $this->domainTrackingRepository = $domainTrackingRepository;
        $this->optionRepository = $optionRepository;

        view()->share('type', 'domain_tracking');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('domain_tracking.DomainTrackings');
        return view('user.domain_tracking.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('domain_tracking.new');
        //Source
		$domainTypeList = $this->optionRepository->getAll()->where( 'category', 'domain_type' )->get()
        ->map( function ( $title ) {
            return [
                'title' => $title->title,
                'value' => $title->value,
            ];
        } )->pluck( 'title', 'value')
        ->prepend(trans('domain_tracking.selectOne'), '');
        return view('user.domain_tracking.create', compact('title','domainTypeList'));
    }

    /**
     * Store a newly created resource in storage. 
     *
     * @param DomainTrackingRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = array(
            'domain' => $request->input('domain'),
            'domain_type' => $request->input('domain_type'),
            'ga_id' => $request->input('ga_id'),
            'status' => $request->input('status'),
        );
        $rules = array(
            'domain' => 'required',
            'domain_type' => 'required',
        );
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            DomainTracking::insert($data);
            return redirect("domain_tracking");
        }
    }

    public function edit($DomainTracking)
    {
        $DomainTracking = $this->domainTrackingRepository->find($DomainTracking);
        $title = trans('domain_tracking.edit');
        //Source
        $domainTypeList = $this->optionRepository->getAll()->where( 'category', 'domain_type' )->get()
        ->map( function ( $title ) {
            return [
                'title' => $title->title,
                'value' => $title->value,
            ];
        } )->pluck( 'title', 'value')
        ->prepend(trans('domain_tracking.selectOne'), '');
        return view('user.domain_tracking.edit', compact('title', 'DomainTracking','domainTypeList'));
    }

    public function update($id, Request $request)
    {
        $DomainTracking = $this->domainTrackingRepository->find($id);
        $data = array(
            'domain' => $request->input('domain'),
            'domain_type' => $request->input('domain_type'),
            'ga_id' => $request->input('ga_id'),
            'status' => $request->input('status'),
        );
        $rules = array(
            'domain' => 'required',
            'domain_type' => 'required',
        );
        
        $validator = Validator::make($data, $rules);
        if ($validator->passes()) {
            $result=$DomainTracking->update($data);
            return redirect("domain_tracking");
        }
        //return redirect("domain_tracking");
    }


    public function show($DomainTracking)
    {
        $DomainTracking = $this->domainTrackingRepository->find($DomainTracking);
        $action = "show";
        $title = trans('domain_tracking.view');
        return view('user.domain_tracking.show', compact('title', 'DomainTracking', 'action'));
    }

    //Code tracking
    public function codeTracking($DomainTracking)
    {
        $DomainTracking = $this->domainTrackingRepository->find($DomainTracking);
        $action = "show";
        if( $DomainTracking){
            //Usage
            $my_file=public_path() .('/uploads/codetracking/'.$DomainTracking->domain.'/code.txt');
            $pathdomain=public_path('/uploads/codetracking/'.$DomainTracking->domain);
            if(!is_dir($pathdomain)){
                mkdir($pathdomain);
            } 
            $token=base64_encode($DomainTracking->id."_".$DomainTracking->domain);
            $contents="<!-- Google Tag Manager -->
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','GTM-NS27Z5D');</script>
            <!-- End Google Tag Manager -->";
            $handle = @fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
            @fwrite($handle, $contents);
            //Storage::put($path, $contents);
            if (ob_get_length()) ob_end_clean();
            $path = public_path('/uploads/codetracking/'.$DomainTracking->domain.'/code.txt');
            if (file_exists($path)) {
                return response()->download($path);
            }
        }
        return "";
        
    }

    public function delete($DomainTracking)
    {
        $DomainTracking = $this->domainTrackingRepository->find($DomainTracking);
        $DomainTracking->delete();
        return redirect("domain_tracking");
    }
     
    public function data(Datatables $datatables)
    {
        $DomainTrackings = $this->domainTrackingRepository->all()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'domain_type' => $p->domain_type,
                    'domain' => $p->domain,
                    'ga_id'=> $p->ga_id,
                    'status' => $p->status,
                    'date_create' => $p->date_create,
                    'code_tracking_link'=>url('domain_tracking/' . $p->id .'/code_tracking')
                ];
            });
        return $datatables->collection($DomainTrackings)
            ->addColumn('actions', '@if(Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'domain_tracking/\' . $id . \'/edit\' ) }}"  title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning "></i> </a>
                                     @endif
                                     <a href="{{ url(\'lead?domain=\' . $id.\'\') }}" title="{{ trans(\'table.details\') }}">
                                            <i class="fa fa-fw fa-eye text-primary"></i></a>
                                     @if(Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'domain_tracking/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
                                     @endif')              
            ->rawColumns(['actions'])->make();
    }
    private function generateParams()
    {
        $statuses = $this->optionRepository->getAll()
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title','value')->prepend(trans('Select Status'), '');
        $DomainTracking_types = $this->optionRepository->getAll()
            ->where('category', 'DomainTracking_type')
            ->get()
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value'   => $title->value,
                ];
            })->pluck('title','value')->prepend(trans('domain_tracking.domain_tracking_title'), '');
    }

    private function getDomainTrackingVariants($variants = [])
    {
        if (isset($variants)) {
            $variants = array_map(
                function ($v) {
                    return explode(':', $v);
                },
                explode(',', $variants)
            );
        }

        return $variants;
    }

}
