<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\ExcelRepository;
use App\Repositories\UserRepository;
use App\Repositories\ClientstatusRepository;
use App\Repositories\OptionRepository;

use Illuminate\Http\Request;
use App\Models\Clientstatus;
use App\Models\Tag;
use App\Models\GroupLead;


use App\Repositories\SentinelAuthAdapter;

use Yajra\Datatables\Datatables;
use App\Helpers\ExcelfileValidator;

class ClientstatusController extends UserController
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ExcelRepository
     */
    private $excelRepository;
    private $clientstatusRepository;
    private $optionRepository;
    
    /**
     * @param SalesTeamRepository $salesTeamRepository
     * @param UserRepository $userRepository
     * @param ExcelRepository $excelRepository
     */
    public function __construct(UserRepository $userRepository,
                                ExcelRepository $excelRepository, ClientstatusRepository $clientstatusRepository, OptionRepository $optionRepository)
    {
        $this->middleware( 'authorized:leads.read', [ 'only' => [ 'index', 'data' ] ] );
		$this->middleware( 'authorized:leads.write', [ 'only' => [ 'create', 'store', 'update', 'edit' ] ] );
		$this->middleware( 'authorized:leads.delete', [ 'only' => [ 'delete' ] ] );

        parent::__construct();
        $this->userRepository = $userRepository;
        $this->clientstatusRepository = $clientstatusRepository;
        $this->optionRepository = $optionRepository;

        view()->share('type', 'clientstatus');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = trans('clientstatus.title');
        $typestatus=$request->type;
        $this->generateParams();
        return view('user.clientstatus.index', compact('title', 'typestatus'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('clientstatus.new');


        $this->generateParams();
       
        return view('user.clientstatus.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
		$request->merge([
			'partner_id' => $partner_id,
		]);
        $status=0;
        $alldata=$request->all();
        if(isset($alldata["status"]) && $alldata["status"]!=""){
            $status=$alldata["status"];
        }
        $alldata["status"]= $status;
        Clientstatus::create($alldata);
        return redirect("clientstatus");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($clientstatusid)
    {
        $title = trans('clientstatus.edit');
        $this->generateParams();
        $userData=$this->userRepository->getUser();
        $this->partner_id=$userData->partner_id;
        //$typeList = $this->optionRepository->getAll()->where( 'category', 'call_status_type' )->get()
        $typeList = GroupLead::where('partner_id', $this->partner_id)->get()->map( function ( $title ) {
            return [
                'title' => $title->name,
                'value' => $title->id,
            ];
        } )->pluck( 'title', 'value' )
        ->prepend(trans('lead.select_function'), '');

        $clientstatus =Clientstatus::find($clientstatusid);
        return view('user.clientstatus.edit', compact('title', 'clientstatus','typeList'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $clientstatus)
    {
        $clientstatus = $this->clientstatusRepository->find($clientstatus);
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
		$request->merge([
			'partner_id' => $partner_id,
		]);
        $status=0;
        $alldata=$request->all();
        if(isset($alldata["status"]) && $alldata["status"]!=""){
            $status=$alldata["status"];
        }
        $alldata["status"]= $status;

        $clientstatus->update($alldata);
        return redirect("clientstatus");
    }

    public function show($clientstatus)
    {
        $clientstatus = Clientstatus::find($clientstatus);
        $title = trans('clientstatus.show');
        $action = "show";
        return view('user.clientstatus.show', compact('title', 'clientstatus','action'));
    }

    public function delete($clientstatus)
    {
        $clientstatus = Clientstatus::find($clientstatus);
        $title = trans('clientstatus.delete');
        return view('user.clientstatus.delete', compact('title', 'clientstatus'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($clientstatus)
    {
       // $this->salesTeamRepository->deleteTeam($clientstatus);
        return redirect('clientstatus');
    }

    public function data(Request $request, Datatables $datatables)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $typestatus=$request->typestatus;
        $i=0;
        $clientstatus = Clientstatus::select('call_action_status.*','group_client.name as group_name')->join('group_client','group_client.id','=','call_action_status.type')->where('call_action_status.partner_id',$partner_id)
            ->where(function ($query)  use ($typestatus){
                if($typestatus!=""){
                    $query->where('call_action_status.type',$typestatus);
                }
            })
            ->get()
            ->map(function ($clientstatus, $key){
            return [
                'id' => $clientstatus->id,
                'stt' => ($key+1),
                'title' => $clientstatus->title,
                'color_bg' => $clientstatus->color_bg,
                'color_text' => $clientstatus->color_text,
                'position_text' => $clientstatus->position,
                'type' => $clientstatus->type,
                'group_name' => $clientstatus->group_name,
                'status' =>  (isset($clientstatus->status) && $clientstatus->status==1)?"Kích hoạt":"Không kích hoat",
            ];
        }); 
        return $datatables->collection($clientstatus)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'leads.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'clientstatus/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-edit text-warning"></i>  </a>
                                     @endif')
            ->addColumn('position', '<input type="textbox" name="position-{{$id}}" value="{{$position_text}}" onchange="return updateposition({{$id}}, this.value);" style="width:120px"/>')
            ->addColumn('type_text', '<a href="{{ url(\'clientstatus?type=\' . $type . \'\' ) }}">{{$group_name}}</a>')
            ->removeColumn('id')
            ->rawColumns(['actions', 'type_text', 'position'])->make();
    }

    private function generateParams()
    {
        $userData=$this->userRepository->getUser();
        $this->partner_id=$userData->partner_id;
     //   $staffs = $this->userRepository->getParentStaff()->pluck('full_name', 'id')->prepend(trans(''), '');
        $typeList = GroupLead::where('partner_id', $this->partner_id)->get()->map( function ( $title ) {
            return [
                'title' => $title->name,
                'value' => $title->id,
            ];
        } )->pluck( 'title', 'value' )
        ->prepend(trans('lead.select_function'), '');
    /*
        $typeList = $this->optionRepository->getAll()->where( 'category', 'call_status_type' )->get()
            ->map( function ( $title ) {
                return [
                    'title' => $title->title,
                    'value' => $title->value,
                ];
            } )->pluck( 'title', 'value' )
            ->prepend("Tất cả", ''); */
        view()->share( 'typeList', $typeList);

    }
    public function updateposition(Request $request){
		$userData=$this->userRepository->getUser();

        $position=$request->position;
		$id=$request->id;
        $partner_id=$userData->partner_id;

		if($id!="" && $position!=""){
            $clientstatusid = Clientstatus::where("id",$id)->where("partner_id",$partner_id)->first();
            if($clientstatusid){
                Clientstatus::where('id', $id)->update(['position'=>$position]);
            }
			return response()->json(['success' => 'success'], 200);
		}else{
			return response()->json(['success' => 'NoSuccess'], 200);
		}
		return response()->json(['error' => trans('dashboard.not_valid_data')], 500);
	}
}
