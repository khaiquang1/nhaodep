<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\ExcelRepository;
use App\Repositories\UserRepository;
use App\Repositories\GroupclientRepository;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\GroupLead;



use App\Repositories\SentinelAuthAdapter;

use Yajra\Datatables\Datatables;
use App\Helpers\ExcelfileValidator;

class TagsController extends UserController
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

        view()->share('type', 'tags');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('tags.title');
        return view('user.tags.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('tags.create_new');

        $this->generateParams();

        return view('user.tags.create', compact('title'));
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
        $data=new Tag;
        $partner_id=$userData->partner_id;
        $dataPost=$request->all();
        $data->partner_id= $partner_id;
        $data->title= $dataPost["title"];
        $data->color_text= $dataPost["color_text"];
        $data->color_bg= $dataPost["color_bg"];
        $data->position= $dataPost["position"];
        $data->keyword= $dataPost["keyword"];

        
        $status=0;
        if(isset($dataPost["status"]) && $dataPost["status"]!=""){
            $status=$dataPost["status"];
        }
        $data->status= $status;
        $data->group_client_id= $dataPost["group_client_id"];
        $data->save();
        $id=$data->id;
        $dataClientStatus =null;
       // $id=GroupLead::create($request->all());

        return redirect("tags");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($groupid)
    {
        $title = trans('tags.edit_new');

        $this->generateParams();
        $tags =Tag::find($groupid);

        return view('user.tags.edit', compact('title', 'tags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $tags)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
		$request->merge([
			'partner_id' => $partner_id,
        ]);
        //$data=new GroupLead;
        
        $dataPost=$request->all();
        $status=0;
        if(isset($dataPost["status"]) && $dataPost["status"]!=""){
            $status=$dataPost["status"];
        }

        Tag::where('id', $tags)->update(['title'=>$dataPost["title"], 'group_client_id'=>$dataPost["group_client_id"], 'color_text'=>$dataPost["color_text"], 'color_bg'=>$dataPost["color_bg"], 'status'=>$status, 'position'=>$dataPost["position"], 'keyword'=>$dataPost["keyword"]]);
        $dataClientStatus =null;
        
        return redirect("tags");
    }

    public function show($tags)
    {
        $tags = Tag::find($tags);
        $title = trans('tags.show');
        $action = "show";
        return view('user.tags.show', compact('title', 'tags','action'));
    }

    public function delete($tags)
    {
        $tagsDetail = Tag::find($tags);
        $title = trans('tags.delete');
        if($tagsDetail){
            $tagsDetail->delete();

        }
        return redirect("tags");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($tags)
    {
       // $this->salesTeamRepository->deleteTeam($tags);
        return redirect('tags');
    }

    public function data(Datatables $datatables)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $i=0;
        $tags = Tag::select('tags.*', 'group_client.name as groupName')->leftJoin('group_client','group_client.id','=','tags.group_client_id')->where('tags.partner_id',$partner_id)
            ->get()
            ->map(function ($tags, $key){
            return [
                'id' => $tags->id,
                'stt' => ($key+1),
                'title' => $tags->title,
                'color_text' => $tags->color_text,
                'color_bg' => $tags->color_bg,
                'group_client_id' => $tags->group_client_id,
                'groupName' => $tags->groupName,
                'position' => $tags->position,
                'status' =>  (isset($tags->status) && $tags->status==1)?"Kích hoạt":"Không kích hoat",
            ];
        }); 
        
        return $datatables->collection($tags) 
            ->addColumn('actions', '@if(Sentinel::getUser()->user_id==1)
            <a href="{{ url(\'tags/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                <i class="fa fa-fw fa-edit text-warning"></i></a>
            @endif
            @if(Sentinel::getUser()->user_id==1)
            <a href="{{ url(\'tags/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                <i class="fa fa-fw fa-trash text-danger"></i> </a>
            @endif'
            )
            ->removeColumn('id')
            ->rawColumns(['actions'])->make();
    }

    private function generateParams()
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        //$typeList = $this->optionRepository->getAll()->where( 'category', 'call_status_type' )->get()
        $groupClient = GroupLead::where('partner_id', $partner_id)->get()->map( function ( $title ) {
            return [
                'title' => $title->name,
                'value' => $title->id,
            ];
        } )->pluck( 'title', 'value' )
        ->prepend(trans('lead.select_function'), '');

        //$staffs = $this->userRepository->getParentStaff()->pluck('full_name', 'id')->prepend(trans(''), '');

        view()->share('clientGroupList', $groupClient);
    }
}
