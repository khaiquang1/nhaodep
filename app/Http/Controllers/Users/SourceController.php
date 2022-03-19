<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\ExcelRepository;
use App\Repositories\UserRepository;
use App\Repositories\GroupclientRepository;

use Illuminate\Http\Request;
use App\Models\Option;

use App\Repositories\SentinelAuthAdapter;

use Yajra\Datatables\Datatables;
use App\Helpers\ExcelfileValidator;

class SourceController extends UserController
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

        view()->share('type', 'source');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('source.title');
        return view('user.source.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('source.create_new');

        $this->generateParams();

        return view('user.source.create', compact('title'));
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
        $data=new Option;
        $partner_id=$userData->partner_id;
        $dataPost=$request->all();
        $data->partner_id= $partner_id;
        $data->title= $dataPost["title"];
        $data->category="function_type";
        $data->value= $dataPost["value"];
        $status=0;
        if(isset($dataPost["status"]) && $dataPost["status"]!=""){
            $status=$dataPost["status"];
        }
        $data->status=$status;
        $data->position= $dataPost["position"];
        $data->save();
        $id=$data->id;
       // $id=GroupLead::create($request->all());

        return redirect("source");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($source)
    {
        $title = trans('source.edit_new');

        $this->generateParams();
        $source =Option::find($source);
        return view('user.source.edit', compact('title', 'source'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $source)
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
       
        Option::where('id', $source)->where('partner_id',$partner_id)->update(['title'=>$dataPost["title"], 'category'=>"function_type", 'status'=>$status, 'value'=>$dataPost["value"], 'position'=>$dataPost["position"]]);
        
        return redirect("source");
    }

    public function show($source)
    {
        $source = Option::find($source);
        $title = trans('source.show');
        $action = "show";
        return view('user.source.show', compact('title', 'source','action'));
    }

    public function delete($source)
    {
        $source = Option::find($source);
        $title = trans('source.delete');
        return view('user.source.delete', compact('title', 'source'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($source)
    {
       // $this->salesTeamRepository->deleteTeam($source);
        return redirect('source');
    }

    public function data(Datatables $datatables)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $i=0;
        $source = Option::where('partner_id',$partner_id)->where('category',"function_type")
            ->get()
            ->map(function ($source, $key){
            return [
                'id' => $source->id,
                'stt' => ($key+1),
                'title' => $source->title,
                'category' => $source->category,
                'value' => $source->value,
                'position' => $source->position,
                'status' =>  (isset($source->status) && $source->status==1)?"Kích hoạt":"Không kích hoat",
            ];
        }); 
        return $datatables->collection($source)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'leads.write\']) || Sentinel::inRole(\'admin\'))
                                        <a href="{{ url(\'source/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
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
       
    }
}
