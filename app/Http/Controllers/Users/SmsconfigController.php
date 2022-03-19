<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\ExcelRepository;
use App\Repositories\UserRepository;

use Illuminate\Http\Request;
use App\Models\Smsconfig;
use App\Models\DeviceToken;
use App\Repositories\SentinelAuthAdapter;

use Yajra\Datatables\Datatables;
use App\Helpers\ExcelfileValidator;

class SmsconfigController extends UserController
{
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
    public function __construct(UserRepository $userRepository,
                                ExcelRepository $excelRepository)
    {
        $this->middleware( 'authorized:leads.read', [ 'only' => [ 'index', 'data' ] ] );
		$this->middleware( 'authorized:leads.write', [ 'only' => [ 'create', 'store', 'update', 'edit' ] ] );
		$this->middleware( 'authorized:leads.delete', [ 'only' => [ 'delete' ] ] );

        parent::__construct();
        $this->userRepository = $userRepository;
        view()->share('type', 'smsconfig');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('smsconfig.title');
        return view('user.smsconfig.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('smsconfig.new');

        $this->generateParams();

        return view('user.smsconfig.create', compact('title'));
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
        $data=new Smsconfig;
        $partner_id=$userData->partner_id;
        $dataPost=$request->all();
        $data->partner_id= $partner_id;
        $data->name= $dataPost["name"];
        $data->device= $dataPost["device"];
        $data->limit_sms= $dataPost["limit_sms"];
        $status=0;
        if(isset($dataPost["status"]) && $dataPost["status"]!=""){
            $status=1;
        }
        $data->status= $status;
        $data->note= $dataPost["note"];
        $user_id=0;
        if($dataPost["device"]!=""){
            $checkUser=DeviceToken::where('uuid','=',$dataPost["device"])->where('status',1)->first();
            if($checkUser && $checkUser!=""){
                $user_id=$checkUser["user_id"];
            }
        }
        $data->user_id=$user_id;
        $data->save();
        $id=$data->id;
       // $id=Smsconfig::create($request->all());
        return redirect("smsconfig");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($groupid)
    {
        $title = trans('smsconfig.edit');

        $this->generateParams();
        $smsconfig =Smsconfig::find($groupid);
        return view('user.smsconfig.edit', compact('title', 'smsconfig'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $smsconfig)
    {
        $smsconfig=Smsconfig::find($smsconfig);
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $dataPost=$request->all();
        $smsconfig->partner_id= $partner_id;
        $smsconfig->name= $dataPost["name"];
        $smsconfig->device= $dataPost["device"];
        $smsconfig->limit_sms= $dataPost["limit_sms"];
        $status=0;
        if(isset($dataPost["status"]) && $dataPost["status"]!=""){
            $status=1;
        }
        $smsconfig->status= $status;
        $smsconfig->note= $dataPost["note"];
        $user_id=0;
        if($dataPost["device"]!=""){
            $checkUser=DeviceToken::where('uuid','=',$dataPost["device"])->where('status',1)->first();
            if($checkUser && $checkUser!=""){
                $user_id=$checkUser["user_id"];
            }
        }
        $smsconfig->user_id=$user_id;
        $smsconfig->update();
      //  Smsconfig::where('id', $smsconfig)->update(['name'=>$dataPost["name"], 'description'=>$dataPost["description"], 'status'=>$dataPost["status"], 'client_status'=>implode(",",$dataPost["client_status"])]);

        return redirect("smsconfig");
    }

    public function show($smsconfig)
    {
        $smsconfig = Smsconfig::find($smsconfig);
        $title = trans('smsconfig.show');
        $action = "show";
        return view('user.smsconfig.show', compact('title', 'smsconfig','action'));
    }

    public function delete($smsconfig)
    {
        $smsconfigdata = Smsconfig::find($smsconfig);
        $title = trans('smsconfig.delete');
        if($smsconfigdata){
            Smsconfig::where("id",$smsconfig)->delete();
        }
        return redirect("smsconfig");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($smsconfig)
    {
       // $this->salesTeamRepository->deleteTeam($smsconfig);
        return redirect('smsconfig');
    }

    public function data(Datatables $datatables)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $i=0;
        $smsconfig = Smsconfig::select('partner_device.*')->where('partner_device.partner_id',$partner_id)
            ->get() 
            ->map(function ($smsconfig, $key){
            return [
                'id' => $smsconfig->id,
                'stt' => ($key+1),
                'name' => $smsconfig->name,
                'device' => $smsconfig->device,
                'limit_sms' => $smsconfig->limit_sms,
                'total_sms_last_sent' => $smsconfig->total_sms_last_sent,
                'status' =>  (isset($smsconfig->status) && $smsconfig->status==1)?"Kích hoạt":"Không kích hoat",
            ];
        }); 
        return $datatables->collection($smsconfig)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'leads.write\']) || Sentinel::inRole(\'admin\'))
            <a href="{{ url(\'smsconfig/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                <i class="fa fa-fw fa-pencil text-warning"></i>  </a>
            @endif
            @if(Sentinel::getUser()->hasAccess([\'leads.delete\']) || Sentinel::inRole(\'admin\'))
                                     <a href="{{ url(\'smsconfig/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i> </a>
									@endif')
            ->removeColumn('id')
            ->rawColumns(['actions'])->make();
    }

    private function generateParams()
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $listUser=$this->userRepository->getAllStaffOfUser($userData->id);
        $deviceToken = DeviceToken::select('device_token.*','users.first_name','users.last_name')->join("users",'users.id','=','device_token.user_id')->where('status',1)->where('device_token.status',1)->where('device_token.platform','android')->whereIn('device_token.user_id',$listUser)->groupBy('device_token.user_id')->get()->map(function ($list) {
            return [
                'title' => $list->first_name." ".$list->last_name."(".$list->uuid.")",
                'value' =>  $list->uuid,
            ];
        })->pluck( 'title', 'value')->prepend(trans('smsconfig.select_device'), '');;
        view()->share( 'deviceToken', $deviceToken);
    }
}
