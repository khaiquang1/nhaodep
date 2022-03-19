<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\ExcelRepository;
use App\Repositories\UserRepository;
use App\Repositories\GroupclientRepository;

use Illuminate\Http\Request;
use App\Models\GroupLead;
use App\Models\Clientstatus;
use App\Models\GroupClientStatus;

use App\Repositories\SentinelAuthAdapter;

use Yajra\Datatables\Datatables;
use App\Helpers\ExcelfileValidator;

class GroupclientController extends UserController
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ExcelRepository
     */
    private $excelRepository;
    private $groupclientRepository;

    
    /**
     * @param SalesTeamRepository $salesTeamRepository
     * @param UserRepository $userRepository
     * @param ExcelRepository $excelRepository
     */
    public function __construct(UserRepository $userRepository,
                                ExcelRepository $excelRepository, GroupclientRepository $groupclientRepository)
    {
        $this->middleware( 'authorized:leads.read', [ 'only' => [ 'index', 'data' ] ] );
		$this->middleware( 'authorized:leads.write', [ 'only' => [ 'create', 'store', 'update', 'edit' ] ] );
		$this->middleware( 'authorized:leads.delete', [ 'only' => [ 'delete' ] ] );

        parent::__construct();
        $this->userRepository = $userRepository;
        $this->groupclientRepository = $groupclientRepository;

        view()->share('type', 'groupclient');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('groupclient.title');
        return view('user.groupclient.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('groupclient.new');

        $this->generateParams();

        return view('user.groupclient.create', compact('title'));
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
        $data=new GroupLead;
        $partner_id=$userData->partner_id;
        $dataPost=$request->all();
        $data->partner_id= $partner_id;
        $data->name= $dataPost["name"];
        $data->description= $dataPost["description"];
        $status=0;
        if(isset($dataPost["status"]) && $dataPost["status"]!=""){
            $status=1;
        }
        $data->status= $status;
        $data->client_status= implode(",",$dataPost["client_status"]);
        $data->save();
        $id=$data->id;
        $dataClientStatus =null;
        if(isset($dataPost["client_status"]) && count($dataPost["client_status"])>0){
            GroupClientStatus::where('group_client_id', $id)->where('partner_id',$partner_id)->delete();
            $statusclient=$dataPost["client_status"];
            for($i=0;$i<count($statusclient);$i++){
                $dataClientStatus[]= array('group_client_id'=>$id, 'client_status_id'=> $statusclient[$i], 'partner_id'=>$partner_id);
            }
        }
        if(count($dataClientStatus)>0){
            GroupClientStatus::insert($dataClientStatus); // Eloquent approach
        }
       // $id=GroupLead::create($request->all());

        return redirect("groupclient");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($groupid)
    {
        $title = trans('groupclient.edit');

        $this->generateParams();
        $groupclient =GroupLead::find($groupid);
        return view('user.groupclient.edit', compact('title', 'groupclient'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $groupclient)
    {
        $grouplead = $this->groupclientRepository->find($groupclient);
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
		$request->merge([
			'partner_id' => $partner_id,
        ]);
        //$data=new GroupLead;
        $dataPost=$request->all();
        $status=0;
        if(isset($dataPost["status"]) && $dataPost["status"]!=""){
            $status=1;
        }
        
        GroupLead::where('id', $groupclient)->update(['name'=>$dataPost["name"], 'description'=>$dataPost["description"], 'status'=>$status, 'client_status'=>implode(",",$dataPost["client_status"])]);
        $dataClientStatus =null;
        if(isset($dataPost["client_status"]) && count($dataPost["client_status"])>0){
            GroupClientStatus::where('group_client_id', $groupclient)->where('partner_id',$partner_id)->delete();
            $statusclient=$dataPost["client_status"];
            for($i=0;$i<count($statusclient);$i++){
                $dataClientStatus[]= array('group_client_id'=>$groupclient, 'client_status_id'=> $statusclient[$i], 'partner_id'=>$partner_id);
            }
        }
        if(count($dataClientStatus)>0){
            GroupClientStatus::insert($dataClientStatus); // Eloquent approach
        }

        return redirect("groupclient");
    }

    public function show($groupclient)
    {
        $groupclient = GroupLead::find($groupclient);
        $title = trans('groupclient.show');
        $action = "show";
        return view('user.groupclient.show', compact('title', 'groupclient','action'));
    }

    public function delete($groupclient)
    {
        $groupclient = GroupLead::find($groupclient);
        $title = trans('groupclient.delete');
        return view('user.groupclient.delete', compact('title', 'groupclient'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($groupclient)
    {
       // $this->salesTeamRepository->deleteTeam($groupclient);
        return redirect('groupclient');
    }

    public function data(Datatables $datatables)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $i=0;
        $groupclient = GroupLead::where('partner_id',$partner_id)
            ->get()
            ->map(function ($groupclient, $key){
            return [
                'id' => $groupclient->id,
                'stt' => ($key+1),
                'name' => $groupclient->name,
                'description' => $groupclient->description,
                'status' =>  (isset($groupclient->status) && $groupclient->status==1)?"Kích hoạt":"Không kích hoat",
            ];
        }); 
        return $datatables->collection($groupclient)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'leads.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'groupclient/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-edit text-warning"></i>  </a>
                                     @endif')
            ->removeColumn('id')
            ->rawColumns(['actions'])->make();
    }

    private function generateParams()
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        //$staffs = $this->userRepository->getParentStaff()->pluck('full_name', 'id')->prepend(trans(''), '');
        $statusGroup = Clientstatus::where('partner_id',$partner_id)->get();

        //view()->share('staffs', $staffs);
        view()->share( 'statusGroup', $statusGroup );
    }
}
