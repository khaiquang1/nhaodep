<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\UserController;
use App\Repositories\ExcelRepository;
use App\Repositories\UserRepository;
use App\Repositories\GroupclientRepository;

use Illuminate\Http\Request;
use App\Models\ContentAutomation;

use App\Repositories\SentinelAuthAdapter;
use Illuminate\Pagination\Paginator;

use Yajra\Datatables\Datatables;
use App\Helpers\ExcelfileValidator;

class ContentAutomationController extends UserController
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

        view()->share('type', 'contentautomation');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = trans('contentautomation.title');

        $keyword = addslashes($request->keyword);
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $i=0;
        $ContentAutomation = ContentAutomation::where('partner_id',$partner_id)
        ->where('parent_id',0)
        ->where(function ($query)  use ($keyword){
            if($keyword!=""){
                $query->where(function ($query1)  use ($keyword){
                    $query1->where('title', 'LIKE', "%{$keyword}%");
                    $query1->orWhere('type',$keyword);
                    $query1->orWhere('content', 'LIKE', "%{$keyword}%");
                    $query1->orWhere('keyword', 'LIKE', "%{$keyword}%");

                });
            } 
        })
        ->distinct()
        ->orderBy('id', 'asc');
        $totalcontent=$ContentAutomation->count();
        
        $contentPage=$ContentAutomation->paginate(20)->appends(request()->query());

        return view('user.contentautomation.index', compact('title', 'totalcontent', 'contentPage'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('contentautomation.new');

        $this->generateParams();
        $contentParent=$this->getContentMultilevel(0, "", 0);

        return view('user.contentautomation.create', compact('title', 'contentParent'));
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
        $data=new ContentAutomation;
        $partner_id=$userData->partner_id;
        $dataPost=$request->all();
        $data->partner_id= $partner_id;
        $data->title= $dataPost["title"];
        $data->reply= $dataPost["reply"];
        $data->status= $dataPost["status"];
        $data->type= $dataPost["type"];
        $data->keyword= $dataPost["keyword"];
        $data->type_content= $dataPost["type_content"];
        $data->promotion_code= $dataPost["promotion_code"];

        if($dataPost["keyword"]!=""){
            $data->keyword_button= $this->convert_vi_to_en($dataPost["keyword"]);

        }

        $data->parent_id= $dataPost["parent_id"];
        $data->save();
        $id=$data->id;
        $dataClientStatus =null;
       // $id=GroupLead::create($request->all());

        return redirect("contentautomation");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($groupid)
    {
        $title = trans('contentautomation.edit');

        $this->generateParams();
        $contentautomation =ContentAutomation::find($groupid);
        $contentselect=0;
        if($contentautomation!=""){
            $contentselect=$contentautomation["parent_id"];
        }
        $contentParent=$this->getContentMultilevel(0, "", $contentselect);
        return view('user.contentautomation.edit', compact('title', 'contentautomation', 'contentParent'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $ContentAutomation)
    {
        $grouplead = $this->groupclientRepository->find($ContentAutomation);
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
		$request->merge([
			'partner_id' => $partner_id,
        ]);
      //  $data->keyword= $dataPost["keyword"];

        //$data=new GroupLead;
        $dataPost=$request->all();
        $keyword_button="";
        if($dataPost["keyword"]!=""){
            $keyword_button= $this->convert_vi_to_en($dataPost["keyword"]);
        }
        $status=0;
        if(isset($dataPost["status"]) && $dataPost["status"]!=""){
            $status=$dataPost["status"];
        }

        ContentAutomation::where('id', $ContentAutomation)->update(['title'=>$dataPost["title"], 'reply'=>$dataPost["reply"], 'status'=>$status, 'type'=> $dataPost["type"], 'keyword'=> $dataPost["keyword"], 'keyword_button'=>$keyword_button, 'parent_id'=> $dataPost["parent_id"], 'type_content'=> $dataPost["type_content"], 'promotion_code'=> $dataPost["promotion_code"]]);
        $dataClientStatus =null;
        return redirect("contentautomation");
    }

    public function show($ContentAutomation)
    {
        $ContentAutomation = ContentAutomation::find($ContentAutomation);
        $title = trans('contentautomation.show');
        $action = "show";
        return view('user.contentautomation.show', compact('title', 'ContentAutomation','action'));
    }

    public function delete($id)
    {
        $ContentAutomationDetail = ContentAutomation::find($id);
        $title = trans('ContentAutomation.delete');
        if($ContentAutomationDetail){
            $ContentAutomationDetail->delete();

        }
        return redirect("contentautomation");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($ContentAutomation)
    {
       // $this->salesTeamRepository->deleteTeam($ContentAutomation);
        return redirect('ContentAutomation');
    }
    public function getcontentchirldren(Request $request)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $parent=$request->id;
        $i=0;
        if($parent!=""){
             
            $contentchirldrentdata = ContentAutomation::where('partner_id',$partner_id)->where('parent_id',$parent)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($content, $key){
            return [
                'id' => $content->id,
                'title' => $content->title,
                'type' => $content->type,
                'reply' => $content->reply,
                'status' =>  (isset($content->status) && $content->status==1)?"Kích hoạt":"Không kích hoat",
            ];
            });
        }else{
            $contentchirldrentdata=array();
        }
        return response()->json( ["datachirl"=> $contentchirldrentdata], 200 );
    }

    public function getContentMultilevel($patent_id=0, $char="", $parentselect=0)
    {
        global $datahtml;
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $i=0;
        $data= ContentAutomation::where('partner_id',$partner_id)->where('parent_id',$patent_id)
        ->get();
        if($data && $data!=""){
            $selected="";
            foreach($data as $listData){
                if(isset($parentselect) && $parentselect==$listData["id"]){
                    $selected="selected";
                }
                $datahtml.="<option value='".$listData["id"]."' $selected>".$char.$listData["title"]."</option>";
                $this->getContentMultilevel($listData["id"], $char."--", $parentselect);
            }
        }
        return $datahtml;
    }

    public function data(Datatables $datatables)
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $i=0;
        $ContentAutomation = ContentAutomation::where('partner_id',$partner_id)
            ->get()
            ->map(function ($ContentAutomation, $key){
            return [
                'id' => $ContentAutomation->id,
                'stt' => ($key+1),
                'title' => $ContentAutomation->title,
                'type' => $ContentAutomation->type,
                
                'content' => $ContentAutomation->content,
                'status' =>  (isset($ContentAutomation->status) && $ContentAutomation->status==1)?"Kích hoạt":"Không kích hoat",
            ];
        }); 
        
        return $datatables->collection($ContentAutomation) 
            ->addColumn('actions', '@if(Sentinel::getUser()->user_id==1)
            <a href="{{ url(\'ContentAutomation/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                <i class="fa fa-fw fa-pencil text-warning"></i>  </a>
            @endif
            @if(Sentinel::getUser()->user_id==1)
            <a href="{{ url(\'ContentAutomation/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                <i class="fa fa-fw fa-trash text-danger"></i> </a>
            @endif'
            )
            ->removeColumn('id')
            ->rawColumns(['actions'])->make();
    }

    private function generateParams($paramater=array())
    {

        //$staffs = $this->userRepository->getParentStaff()->pluck('full_name', 'id')->prepend(trans(''), '');

        //view()->share('clientGroupList', $groupClient);
    }

    private function convert_vi_to_en($str) {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
        $str = preg_replace("/(đ)/", 'd', $str);
        $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
        $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
        $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
        $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
        $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
        $str = preg_replace("/(K)/", 'k', $str);
        $str = preg_replace("/(T)/", 't', $str);
        $str = preg_replace("/(Q)/", 'q', $str);
        $str = preg_replace("/(V)/", 'v', $str);
        $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
        $str = preg_replace("/(Đ)/", 'D', $str);
        $str = preg_replace("/( )/", '-', $str);
    
        return $str;
    }
}
