<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\ExcelRepository;
use App\Repositories\UserRepository;
use App\Repositories\GetdataRepository;

use Illuminate\Http\Request;
use App\Models\Getdata;
use App\Models\Clientstatus;
use App\Models\Branch;
use App\Models\UserControlPage;
use App\Models\GroupLead;



use App\Repositories\SentinelAuthAdapter;

use Yajra\Datatables\Datatables;
use App\Helpers\ExcelfileValidator;
use Illuminate\Support\Facades\DB;


class GetdataController extends UserController
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ExcelRepository
     */
    private $excelRepository;
    private $getdataRepository;

    
    /**
     * @param SalesTeamRepository $salesTeamRepository
     * @param UserRepository $userRepository
     * @param ExcelRepository $excelRepository
     */
    public function __construct(UserRepository $userRepository,
                                ExcelRepository $excelRepository, GetdataRepository $getdataRepository)
    {
        $this->middleware( 'authorized:leads.read', [ 'only' => [ 'index', 'data' ] ] );
		$this->middleware( 'authorized:leads.write', [ 'only' => [ 'create', 'store', 'update', 'edit' ] ] );
		$this->middleware( 'authorized:leads.delete', [ 'only' => [ 'delete' ] ] );

        parent::__construct();
        $this->userRepository = $userRepository;
        $this->getdataRepository = $getdataRepository;

        view()->share('type', 'getdata');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('getdata.title');
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        return view('user.getdata.index', compact('title', 'partner_id'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('getdata.new');

        $this->generateParams();

        return view('user.getdata.create', compact('title'));
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
        
        Getdata::create($request->all());

        return redirect("getdata");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($groupid)
    {
        $title = trans('getdata.edit');
        $this->generateParams();
        $getdata =Getdata::find($groupid);
        $listUserCareData=array();
        if($getdata){
            $page_id=$getdata->page_id;
            $listUserCare=UserControlPage::where('page_id',$page_id)->get();
            if($listUserCare){
                foreach($listUserCare as $listData){
                    $listUserCareData[]=$listData["user_id"];
                }
            }
        }
        return view('user.getdata.edit', compact('title', 'getdata', 'listUserCareData'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $Getdata)
    {
        $Getdata = $this->getdataRepository->find($Getdata);
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $page_id=$request->page_id;
        $userpage=$request->user_id;
        if(count($userpage)>0){
            UserControlPage::where('page_id',$page_id)->delete();
        }
        $listInsert=array();
        for($i=0;$i<count($userpage);$i++){
            $listInsert[]=array("user_id"=>$userpage[$i], "page_id"=>$page_id);
        }
        UserControlPage::insert($listInsert);
        $arrayupdate=array();
        $arrayupdate["partner_id"]=$partner_id;
        $arrayupdate["title"]=$request->title;
        $arrayupdate["type"]=$request->type;
        $arrayupdate["branch_id"]=$request->branch_id;
        if($request->token!=""){
            $arrayupdate["token"]=$request->token;
        }
        
        $arrayupdate["page_id"]=$request->page_id;;
        $arrayupdate["client_status_id"]=$request->client_status_id;
        $arrayupdate["group_id"]=$request->group_id;;
        $Getdata->update($arrayupdate);
        return redirect("getdata");
    }

    public function updatestatus(Request $request)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $page=$request->page;
        if(isset($page) && $page!=""){
            $pageList=explode(",",$page);
            if(isset($pageList) && count($pageList)>0){
                for($i=0;$i<count($pageList);$i++){
                    $pagedetail=explode(":",$pageList[$i]);
                    $pageid=$pagedetail[0];
                    $pagename=$pagedetail[1];
                    $token="";
                    $listInsert2[]=[$partner_id, $pagename, 'Facebook', $token, $pageid, 1];
                    $keyUpdate[]="`partner_id`='".$partner_id."', `page_id`='".$pageid."'";
                    
                    DB::insert('insert into `config_datas` (`partner_id`, `title`, `type`, `token`, `page_id`, `status`) values (?, ?, ?, ?, ?, ?) on duplicate key update '.$keyUpdate[$i],$listInsert2[$i]);


                }
            }
        }
        
                //$Getdata->update($request->all());
        return redirect("getdata");
    }


    
    public function show($Getdata)
    {
        $getdata = Getdata::find($Getdata);
        $title = trans('getdata.show');
        $action = "show";
        return view('user.getdata.show', compact('title', 'getdata','action'));
    }

    public function delete($Getdata)
    {
        $getdata = Getdata::find($Getdata);
        $title = trans('getdata.delete');
        return view('user.getdata.delete', compact('title', 'getdata'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($getdata)
    {
       // $this->salesTeamRepository->deleteTeam($Getdata);
        return redirect('getdata');
    }

    public function data(Datatables $datatables)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $i=0;
        $getdata = Getdata::where('partner_id',$partner_id)
            ->get()
            ->map(function ($getdata, $key){
            return [
                'id' => $getdata->id,
                'stt' => ($key+1),
                'title' => $getdata->title,
                'type' => $getdata->type,
                'token_1' => $getdata->token,
                'page_id' => $getdata->page_id,
                'status' =>  (isset($getdata->status) && $getdata->status==1)?"Kích hoạt":"Không kích hoat",
            ];
        }); 
        return $datatables->collection($getdata)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'leads.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'getdata/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning"></i>  </a>
                                     @endif')
            ->addColumn('token', '<span>{{$token_1}}</span>')
            ->removeColumn('token_1')
            ->removeColumn('id')
            ->rawColumns(['actions','token'])->make();
    }

    private function generateParams()
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        //$staffs = $this->userRepository->getParentStaff()->pluck('full_name', 'id')->prepend(trans(''), '');
        $statusGroup = Clientstatus::where('partner_id',$partner_id)->where('type',1)->get()
        ->map( function ( $statusgroup ) {
            return [
                'title' => $statusgroup->title,
                'value' => $statusgroup->id,
            ];
        } )->pluck( 'title', 'value' );

        $groupLead = GroupLead::select('group_client.*')
		->join('group_client_status','group_client_status.group_client_id','=','group_client.id')
		->join('call_action_status','call_action_status.id','=','group_client_status.client_status_id')
		->where("group_client.partner_id",$partner_id)
		->orderBy("group_client.position", "asc")
		->get()->map(function ($list) {
			return [
				'title' => $list->name,
				'value' =>  $list->id,
			];
		})->pluck( 'title', 'value')->prepend(trans('lead.select_group_client'), '');

        ///////
        $branch=Branch::where('partner_id',$partner_id)->get()->map( function ( $branch) {
            return [
                'title' => $branch->name,
                'value' => $branch->id,
            ];
        } )->pluck( 'title', 'value' )
        ->prepend(trans('branch.branch_select'), ''); 


       // view()->share('staffs', $staffs);
       
        view()->share('statusGroup', $statusGroup);
        view()->share('branch', $branch);
        view()->share('groupLead', $groupLead);


    }
}
