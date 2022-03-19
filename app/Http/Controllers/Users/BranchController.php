<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\ExcelRepository;
use App\Repositories\UserRepository;

use Illuminate\Http\Request;
use App\Models\Branch;

use App\Repositories\SentinelAuthAdapter;

use Yajra\Datatables\Datatables;
use App\Helpers\ExcelfileValidator;

class BranchController extends UserController
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
        view()->share('type', 'branch');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('branch.title');
        return view('user.branch.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('branch.new');

        $this->generateParams();

        return view('user.branch.create', compact('title'));
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
        $data=new Branch;
        $partner_id=$userData->partner_id;
        $dataPost=$request->all();

        $data->partner_id= $partner_id;
        $data->name= $dataPost["name"];
        $data->description= $dataPost["description"];
        $data->status= $dataPost["status"];
        $data->address= $dataPost["address"];
        $data->phone= $dataPost["phone"];
        $data->email= $dataPost["email"];
        $data->city_id= $dataPost["city_id"];
        $data->district_id= $dataPost["district_id"];
        $data->ward_id= $dataPost["ward_id"];
        if(isset($dataPost["is_default"]) && $dataPost["is_default"]!=""){
            $data->is_default= $dataPost["is_default"];

        }else{
            $data->is_default= 0;
        }
       
        if(isset($dataPost["type"]) && $dataPost["type"]!=""){
            $data->type= $dataPost["type"]; //1: Chi nhánh chính,

        }else{
            $data->type= 1;
        }
        $data->save();
        $id=$data->id;
       // $id=Branch::create($request->all());
        return redirect("branch");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($groupid)
    {
        $title = trans('branch.edit');

        $this->generateParams();
        $branch =Branch::find($groupid);
        return view('user.branch.edit', compact('title', 'branch'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $branch)
    {
        $brand=Branch::find($branch);
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
		$request->merge([
			'partner_id' => $partner_id,
        ]);
        //$data=new Branch;
        $dataPost=$request->all();
        $brand->partner_id= $partner_id;
        $brand->name= $dataPost["name"];
        $brand->description= $dataPost["description"];
        $brand->status= $dataPost["status"];
        $brand->address= $dataPost["address"];
        $brand->phone= $dataPost["phone"];
        $brand->email= $dataPost["email"];
        $brand->city_id= $dataPost["city_id"];
        $brand->district_id= $dataPost["district_id"];
        $brand->ward_id= $dataPost["ward_id"];
        if(isset($dataPost["is_default"]) && $dataPost["is_default"]!=""){
            $brand->is_default= $dataPost["is_default"];

        }else{
            $brand->is_default= 0;
        }
        if(isset($dataPost["type"]) && $dataPost["type"]!=""){
            $brand->type= $dataPost["type"]; //1: Chi nhánh chính,

        }else{
            $brand->type= 1;
        }
        $brand->update();
      //  Branch::where('id', $branch)->update(['name'=>$dataPost["name"], 'description'=>$dataPost["description"], 'status'=>$dataPost["status"], 'client_status'=>implode(",",$dataPost["client_status"])]);
      

        return redirect("branch");
    }

    public function show($branch)
    {
        $branch = Branch::find($branch);
        $title = trans('branch.show');
        $action = "show";
        return view('user.branch.show', compact('title', 'branch','action'));
    }

    public function delete($branch)
    {
        $branch = Branch::find($branch);
        $title = trans('branch.delete');
        return view('user.branch.delete', compact('title', 'branch'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($branch)
    {
       // $this->salesTeamRepository->deleteTeam($branch);
        return redirect('branch');
    }

    public function data(Datatables $datatables)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $i=0;
        $branch = Branch::select('branches.*', 'cities.name as city', 'districts.name as district', 'wards.name as ward')->leftJoin('cities','cities.id','=','branches.city_id')->leftJoin('districts','districts.id','=','branches.district_id')->leftJoin('wards','wards.id','=','branches.ward_id')->where('branches.partner_id',$partner_id)
            ->get() 
            ->map(function ($branch, $key){
            return [
                'id' => $branch->id,
                'stt' => ($key+1),
                'name' => $branch->name,
                'description' => $branch->description,
                'address' => $branch->address.", ".$branch->city.", ".$branch->district.", ".$branch->ward." ",
                'status' =>  (isset($branch->status) && $branch->status==1)?"Kích hoạt":"Không kích hoat",
            ];
        }); 
        return $datatables->collection($branch)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'leads.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'branch/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
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
       // $statusGroup = Clientstatus::where('partner_id',$partner_id)->get();

        //view()->share('staffs', $staffs);
      //  view()->share( 'statusGroup', $statusGroup );
    }
}
