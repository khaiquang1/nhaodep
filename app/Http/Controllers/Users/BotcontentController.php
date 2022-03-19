<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\ExcelRepository;
use App\Repositories\UserRepository;
use App\Repositories\BotcontentRepository;

use Illuminate\Http\Request;
use App\Models\Botcontent;
use App\Models\Clientstatus;
use App\Models\Branch;
use App\Models\UserControlPage;
use App\Models\Getdata;


use App\Repositories\SentinelAuthAdapter;

use Yajra\Datatables\Datatables;
use App\Helpers\ExcelfileValidator;
use Illuminate\Support\Facades\DB;


class BotcontentController extends UserController
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ExcelRepository
     */
    private $excelRepository;
    private $botcontentRepository;

    
    /**
     * @param SalesTeamRepository $salesTeamRepository
     * @param UserRepository $userRepository
     * @param ExcelRepository $excelRepository
     */
    public function __construct(UserRepository $userRepository,
                                ExcelRepository $excelRepository, botcontentRepository $botcontentRepository)
    {
        $this->middleware( 'authorized:leads.read', [ 'only' => [ 'index', 'data' ] ] );
		$this->middleware( 'authorized:leads.write', [ 'only' => [ 'create', 'store', 'update', 'edit' ] ] );
		$this->middleware( 'authorized:leads.delete', [ 'only' => [ 'delete' ] ] );

        parent::__construct();
        $this->userRepository = $userRepository;
        $this->botcontentRepository = $botcontentRepository;

        view()->share('type', 'botcontent');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('botcontent.title');
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $limit=30; 
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $i=0;
        $botcontentQuery = botcontent::where('partner_id',$partner_id)->orderBy('updated_at', 'DESC');
        $totalContent=$botcontentQuery->count();      
        $totalpage=$totalContent/$limit;
        $botcontentPage=$botcontentQuery->paginate($limit)->appends(request()->query());
        return view('user.botcontent.index', compact('title', 'partner_id',  'botcontentPage', 'totalContent', 'totalpage'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('botcontent.new');
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $pageList=Getdata::where('partner_id',$partner_id)->get();
        $this->generateParams();

        return view('user.botcontent.create', compact('title', 'pageList'));
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
        
        botcontent::create($request->all());

        return redirect("botcontent");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($groupid)
    {
        $title = trans('botcontent.edit');
        $this->generateParams();
        $botcontent =botcontent::find($groupid);
        $listUserCareData=array();
        if($botcontent){
            $page_id=$botcontent->page_id;
            $listUserCare=UserControlPage::where('page_id',$page_id)->get();
            if($listUserCare){
                foreach($listUserCare as $listData){
                    $listUserCareData[]=$listData["user_id"];
                }
            }
        }
        return view('user.botcontent.edit', compact('title', 'botcontent', 'listUserCareData'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $botcontent)
    {
        $botcontent = $this->botcontentRepository->find($botcontent);
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
        $arrayupdate["client_status_id"]=$request->client_status_id;;
        $botcontent->update($arrayupdate);
        return redirect("botcontent");
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
        
                //$botcontent->update($request->all());
        return redirect("botcontent");
    }


    
    public function show($botcontent)
    {
        $botcontent = botcontent::find($botcontent);
        $title = trans('botcontent.show');
        $action = "show";
        return view('user.botcontent.show', compact('title', 'botcontent','action'));
    }

    public function delete($botcontent)
    {
        $botcontent = botcontent::find($botcontent);
        $title = trans('botcontent.delete');
        return view('user.botcontent.delete', compact('title', 'botcontent'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($botcontent)
    {
       // $this->salesTeamRepository->deleteTeam($botcontent);
        return redirect('botcontent');
    }

    public function data(Datatables $datatables)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $i=0;
        $botcontent = botcontent::where('partner_id',$partner_id)
            ->get()
            ->map(function ($botcontent, $key){
            return [
                'id' => $botcontent->id,
                'stt' => ($key+1),
                'title' => $botcontent->title,
                'type' => $botcontent->type,
                'token_1' => $botcontent->token,
                'page_id' => $botcontent->page_id,
                'status' =>  (isset($botcontent->status) && $botcontent->status==1)?"Kích hoạt":"Không kích hoat",
            ];
        }); 
        return $datatables->collection($botcontent)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'leads.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'botcontent/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
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

    }
}
