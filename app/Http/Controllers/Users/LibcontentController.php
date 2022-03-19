<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\ExcelRepository;
use App\Repositories\UserRepository;
use App\Repositories\GroupclientRepository;

use Illuminate\Http\Request;
use App\Models\Libcontent;

use App\Repositories\SentinelAuthAdapter;
use Illuminate\Pagination\Paginator;

use Yajra\Datatables\Datatables;
use App\Helpers\ExcelfileValidator;

class LibcontentController extends UserController
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
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->groupclientRepository = $groupclientRepository;

        view()->share('type', 'libcontent');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = trans('libcontent.title');

        $keyword = addslashes($request->keyword);
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $i=0;
        $libcontent = Libcontent::where('partner_id',$partner_id)
        ->where(function ($query)  use ($keyword){
            if($keyword!=""){
                $query->where(function ($query1)  use ($keyword){
                    $query1->where('title', 'LIKE', "%{$keyword}%");
                    $query1->orWhere('type',$keyword);
                    $query1->orWhere('content', 'LIKE', "%{$keyword}%");
                });
            } 
        })
        ->distinct()
        ->orderBy('id', 'DESC');
        $totalcontent=$libcontent->count();
        $contentPage=$libcontent->paginate(20)->appends(request()->query());

        /*
            ->get()
            ->map(function ($libcontent, $key){
            return [
                'id' => $libcontent->id,
                'stt' => ($key+1),
                'title' => $libcontent->title,
                'type' => $libcontent->type,
                'content' => $libcontent->content,
                'status' =>  (isset($libcontent->status) && $libcontent->status==1)?"Kích hoạt":"Không kích hoat",
            ];
        }); 
        
        return $datatables->collection($libcontent) 
            ->addColumn('actions', '@if(Sentinel::getUser()->user_id==1)
            <a href="{{ url(\'libcontent/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                <i class="fa fa-fw fa-pencil text-warning"></i>  </a>
            @endif
            @if(Sentinel::getUser()->user_id==1)
            <a href="{{ url(\'libcontent/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                <i class="fa fa-fw fa-trash text-danger"></i> </a>
            @endif'
            )
            ->removeColumn('id')
            ->rawColumns(['actions'])->make();
 */

        return view('user.libcontent.index', compact('title', 'totalcontent', 'contentPage'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('libcontent.new');

        $this->generateParams();

        return view('user.libcontent.create', compact('title'));
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
        $data=new Libcontent;
        $partner_id=$userData->partner_id;
        $dataPost=$request->all();
        $data->partner_id= $partner_id;
        $data->title= $dataPost["title"];
        $data->content= $dataPost["content"];
        if(isset( $dataPost["status"]) &&  $dataPost["status"]!=""){
            $data->status= $dataPost["status"];
        }
        $data->status= 1;
        $data->type= $dataPost["type"];

        
        $data->save();
        $id=$data->id;
        $dataClientStatus =null;
       // $id=GroupLead::create($request->all());

        return redirect("libcontent");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($groupid)
    {
        $title = trans('libcontent.edit');

        $this->generateParams();
        $libcontent =Libcontent::find($groupid);

        return view('user.libcontent.edit', compact('title', 'libcontent'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $libcontent)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
		$request->merge([
			'partner_id' => $partner_id,
        ]);
        //$data=new GroupLead;
        
        $dataPost=$request->all();
        $status=0;
        if(isset($dataPost["status"])){
            $status=$dataPost["status"];
        }
        Libcontent::where('id', $libcontent)->update(['title'=>$dataPost["title"], 'content'=>$dataPost["content"], 'status'=>$status, 'type'=> $dataPost["type"]]);
        $dataClientStatus =null;
        
        return redirect("libcontent");
    }

    public function show($libcontent)
    {
        $libcontent = Libcontent::find($libcontent);
        $title = trans('libcontent.show');
        $action = "show";
        return view('user.libcontent.show', compact('title', 'libcontent','action'));
    }

    public function delete($libcontent)
    {
        $libcontentDetail = Libcontent::find($libcontent);
        $title = trans('libcontent.delete');
        if($libcontentDetail){
            $libcontentDetail->delete();

        }
        return redirect("libcontent");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($libcontent)
    {
       // $this->salesTeamRepository->deleteTeam($libcontent);
        return redirect('libcontent');
    }

    public function data(Datatables $datatables)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $i=0;
        $libcontent = Libcontent::where('partner_id',$partner_id)
            ->get()
            ->map(function ($libcontent, $key){
            return [
                'id' => $libcontent->id,
                'stt' => ($key+1),
                'title' => $libcontent->title,
                'type' => $libcontent->type,
                
                'content' => $libcontent->content,
                'status' =>  (isset($libcontent->status) && $libcontent->status==1)?"Kích hoạt":"Không kích hoat",
            ];
        }); 
        
        return $datatables->collection($libcontent) 
            ->addColumn('actions', '@if(Sentinel::getUser()->user_id==1)
            <a href="{{ url(\'libcontent/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                <i class="fa fa-fw fa-pencil text-warning"></i>  </a>
            @endif
            @if(Sentinel::getUser()->user_id==1)
            <a href="{{ url(\'libcontent/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                <i class="fa fa-fw fa-trash text-danger"></i> </a>
            @endif'
            )
            ->removeColumn('id')
            ->rawColumns(['actions'])->make();
    }
    public function searchcontent(Request $request){
        $keyword=$request->keyword;
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $libcontent = Libcontent::where('partner_id',$partner_id)
        ->where(function ($query)  use ($keyword){
            if($keyword!=""){
                $query->where(function ($query1)  use ($keyword){
                    $query1->where('title', 'LIKE', "%{$keyword}%");
                    $query1->orWhere('type',$keyword);
                    $query1->orWhere('content', 'LIKE', "%{$keyword}%");
                });
            } 
        })
        ->orderBy('id', 'DESC')->limit(20)->get()
        ->map(function ($content){
            return [
                'id' => $content->id,
                'title' => $content->title,
                'type' => $content->type,
                'content' => $content->content,
            ];
        });
        return response()->json( ["data"=> $libcontent], 200 );
    }
    private function generateParams()
    {

        //$staffs = $this->userRepository->getParentStaff()->pluck('full_name', 'id')->prepend(trans(''), '');

        //view()->share('clientGroupList', $groupClient);
    }
}
