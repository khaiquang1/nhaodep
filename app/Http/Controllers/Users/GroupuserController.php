<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\ExcelRepository;
use App\Repositories\UserRepository;
use App\Repositories\GroupuserRepository;

use Illuminate\Http\Request;
use App\Models\GroupUser;
use App\Models\Rolespartner;
use App\Models\User;

use App\Repositories\SentinelAuthAdapter;

use Yajra\Datatables\Datatables;
use App\Helpers\ExcelfileValidator;

class GroupuserController extends UserController
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ExcelRepository
     */
    private $excelRepository;
    private $groupuserRepository;

    
    /**
     * @param SalesTeamRepository $salesTeamRepository
     * @param UserRepository $userRepository
     * @param ExcelRepository $excelRepository
     */
    public function __construct(UserRepository $userRepository,
                                ExcelRepository $excelRepository, GroupuserRepository $groupuserRepository)
    {
        $this->middleware( 'authorized:leads.read', [ 'only' => [ 'index', 'data' ] ] );
		$this->middleware( 'authorized:leads.write', [ 'only' => [ 'create', 'store', 'update', 'edit' ] ] );
		$this->middleware( 'authorized:leads.delete', [ 'only' => [ 'delete' ] ] );

        parent::__construct();
        $this->userRepository = $userRepository;
        $this->groupuserRepository = $groupuserRepository;

        view()->share('type', 'groupuser');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('groupuser.title');
        return view('user.groupuser.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('groupuser.new');

        $this->generateParams();

        return view('user.groupuser.create', compact('title'));
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
        $data=new GroupUser;
        $partner_id=$userData->partner_id;
        $dataPost=$request->all();
        $data->partner_id= $partner_id;
        $data->name= $dataPost["name"];
        $data->description= $dataPost["description"];
        $status=0;
        if(isset($dataPost["status"]) && $dataPost["status"]!=""){
            $status= $dataPost["status"];
        }
        $data->status= $status;
//        $data->client_status= implode(",",$dataPost["client_status"]);
        $data->save();
        $id=$data->id;
        $grouplead = $this->groupuserRepository->find($id);
        foreach ($request->get('permissions', []) as $permission) {
           $grouplead->addPermission($permission);
        }
        $grouplead->save();
       // $id=Groupuser::create($request->all());
        return redirect("groupuser");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($groupid)
    {
        $title = trans('groupuser.edit');

        $this->generateParams();
        $groupuser =GroupUser::find($groupid);
        return view('user.groupuser.edit', compact('title', 'groupuser'));
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $groupuser)
    {
        $grouplead = $this->groupuserRepository->find($groupuser);
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
		$request->merge([
			'partner_id' => $partner_id,
        ]);
        //$data=new Groupuser;
        $dataPost=$request->all();
       // GroupUser::where('id', $groupuser)->update(['name'=>$dataPost["name"], 'description'=>$dataPost["description"], 'status'=>$dataPost["status"]]);
        $grouplead->name=$dataPost["name"];
        $grouplead->description=$dataPost["description"];
        $status=0;
        if(isset($dataPost["status"]) && $dataPost["status"]!=""){
            $status= $dataPost["status"];
        }
        $grouplead->status=$status;
        foreach ($grouplead->getPermissions() as $key => $item) {
            $grouplead->removePermission($key);
        }

        foreach ($request->get('permissions', []) as $permission) {
           $grouplead->addPermission($permission);
        }
        $grouplead->save();
       return redirect("groupuser");
    }

    public function show($groupuser)
    {
        $groupuser = GroupUser::find($groupuser);
        $title = trans('groupuser.show');
        $action = "show";
        return view('user.groupuser.show', compact('title', 'groupuser','action'));
    }

    public function delete($groupuser)
    {
        $groupuser = GroupUser::find($groupuser);
        $title = trans('groupuser.delete');
        return view('user.groupuser.delete', compact('title', 'groupuser'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($groupuser)
    {
       // $this->salesTeamRepository->deleteTeam($groupuser);
        return redirect('groupuser');
    }

    public function data(Datatables $datatables)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $i=0;
        $groupuser = GroupUser::where('partner_id',$partner_id)
            ->get()
            ->map(function ($groupuser, $key){
            return [
                'id' => $groupuser->id,
                'stt' => ($key+1),
                'name' => $groupuser->name,
                'description' => $groupuser->description,
                'status' =>  (isset($groupuser->status) && $groupuser->status==1)?"Kích hoạt":"Không kích hoat",
            ];
        }); 
        return $datatables->collection($groupuser)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'leads.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'groupuser/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-edit text-warning"></i>  </a>
                                     @endif')
            ->removeColumn('id')
            ->rawColumns(['actions'])->make();
    }

    private function generateParams()
    {
       // $userData=$this->userRepository->getUser();
       // $partner_id=$userData->partner_id;
        //$staffs = $this->userRepository->getParentStaff()->pluck('full_name', 'id')->prepend(trans(''), '');
        $statusGroup = "";//Clientstatus::where('partner_id',$partner_id)->get();
        $listRole=Rolespartner::get();
        //view()->share('staffs', $staffs);
      //  view()->share( 'statusGroup', $statusGroup );
        view()->share( 'roles', $listRole );

    }

    // 
    public function userGroup(Request $request){
        $group =$request->group_id;
        $usershow=array();
        if($group>0){
            $userData=User::where('group_id',$group)->orderBy("id", "desc")->get();
            if($userData){
                foreach($userData as $key=>$values){
                    $select="";
                    $usershow[]=array('id'=>$values["id"], 'fullname'=>$values["first_name"]." ".$values["last_name"]);
                }
            }
        }
        return $usershow;
    }
    
}
