<?php

namespace App\Http\Controllers\Users;

use App\Http\Requests\TaskRequest;
use App\Repositories\TaskRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskReport;
use App\Models\Notification;
use App\Models\CallActionStatus;
use App\Models\Partner;


use App\Repositories\OptionRepository;
use App\Http\Controllers\UserController;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Datatables;

class TaskController extends UserController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    private $taskRepository;
	/**
	 * @var OptionRepository
	 */
	private $optionRepository;

    /**
     * TaskController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(
        UserRepository $userRepository,
        TaskRepository $taskRepository,
        OptionRepository $optionRepository
    )
    {
        parent::__construct();

        $this->userRepository = $userRepository;
        $this->taskRepository = $taskRepository;
        $this->optionRepository    = $optionRepository;
        view()->share('type', 'task');
    }
    public function index(Request $request)
    {
        $title = trans('task.tasks');
		$dateFormat = config('settings.date_format');
		$date  = addslashes($request->starting_date);
		$sales_id = addslashes($request->sales_id) ;
		$status  = addslashes($request->status) ;
        $keyword = addslashes($request->keyword);
        $linkfull=urlencode($request->fullUrl());
        if($date!=""){
			$dateArray=explode("-",trim($date));
			$starting_date=$this->convertDate(trim($dateArray[0]));
			$ending_date=$this->convertDate(trim($dateArray[1]));
			$starting_date=date("Y-m-d", strtotime($starting_date." -1 days"));
			$ending_date=date("Y-m-d", strtotime($ending_date." +1 days"));
			$date_select=$date;
		}else{
			$starting_date=date("Y-m-d",strtotime('today - 1 days'));
			$ending_date=date("Y-m-d",strtotime('today +1 days'));
			$date_select=date("m/d/Y",strtotime('today - 1 days'))." - ".date("m/d/Y");
		}
        $usersList = "";
        $user = $this->userRepository->getUser(); 
        //Status list
        //Source
		$statusList = $this->optionRepository->getAll()->where('partner_id','=',$user->partner_id)->where( 'category', 'status_report' )->get()
        ->map( function ( $title ) {
            return [
                'title' => $title->title,
                'value' => $title->value,
            ];
        } )->pluck( 'title', 'value')
        ->prepend(trans('lead.select_function'), '');
        /*
        $statusList = $this->optionRepository->getAll()->where( 'partner_id', $user->partner_id)->where( 'category', 'status_report' )->get()
        ->map( function ( $title ) {
            return [
                'title' => $title->title,
                'value' => $title->value,
            ];
        } )->pluck( 'title', 'value' )
        ->prepend(trans('lead.select_function'), '');
        */
        //salesList

        $listUserSales=$this->userRepository->getAllStaffOfUser($user->id);
        $salesList="";
        if($listUserSales){
            $salesList=User::join('partner_user','partner_user.user_id','=','users.id')
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
            //->where('partner_user.partner_id','=',$user->partner_id)
            ->whereIn('users.id',$listUserSales)
            ->get()
            ->map( function ( $salesList ) {
                return [ 
                    'title' => $salesList->first_name." ".$salesList->last_name,
                    'value' => $salesList->id,
                ];
            } )->pluck( 'title', 'value')
            ->prepend(trans('lead.all'), '');
        }else{
            $salesList=array(''=>trans('lead.all'));
        }
		

        $dateFormat = config('settings.date_format');
        $date  = addslashes($request->starting_date);

        $taskQuery=Task::select('tasks.*', 'users.first_name as first_name', 'users.last_name as last_name', 'leads.contact_name as lead_name', 'leads.id as lead_id')
        ->leftJoin('users','users.id','=','tasks.user_id')
        ->leftJoin('leads','leads.id','=','tasks.lead_id')
        ->where(function ($query)  use ($starting_date, $ending_date, $status, $keyword){
            if($keyword!=""){
                $query->where(function ($query1)  use ($keyword){
                    $query1->where('tasks.task_title', 'LIKE', "%{$keyword}%");
                    $query1->orWhere('tasks.task_description','LIKE', "%{$keyword}%");
                });
            }
            if($starting_date!=""){
                $query->where('tasks.task_end','>=',$starting_date);
            }
            if($ending_date!=""){
                $query->where('tasks.task_end','<=',$ending_date);
            } 
            if($status!=""){
                $query->where('tasks.work_status',$status);
            }
        })->where(function ($query)  use ($sales_id, $listUserSales, $user){
            if($sales_id!="" && $sales_id!="0"){
                $query->where('tasks.user_id','=',$sales_id);
                $query->whereOr('tasks.task_from_user','=',$sales_id);
            }else{
                if($listUserSales){
                    array_push($listUserSales,$user->id);
                }
                if($listUserSales!="" && count($listUserSales)>0){
                    $query->whereIn('tasks.user_id',$listUserSales);
                    $query->orWhereIn('tasks.task_from_user',$listUserSales);
                }else{
                    $query->where('tasks.user_id',$user->id);
                    $query->orWhere('tasks.task_from_user',$user->id);
                }
               
            }
        })->distinct()
        ->orderBy("tasks.id", "desc");
        //$totalLead=$leadsQuery->count();
        $totalTask=count($taskQuery->get());

        $taskPage=$taskQuery->paginate(20)->appends(request()->query());
            $taskList=$taskPage->map( function ( $task) use ($dateFormat){
                return [
                    'id'           => $task->id,
                    'task_deadline'   => $task->task_end,
                    'task_start'   => $task->task_start,
                    'task_description' => $task->description,
                    'task_title' => $task->task_title,
                    'task_note'  => $task->task_note,
                    'user_id'  => $task->user_id,
                    'status_title'  => $task->status_title,
                    'full_name'  => $task->first_name." ".$task->last_name,
                    'lead_name'  => $task->lead_name,
                    'lead_id'  => $task->lead_id,
                    'type_task'  => $task->type_task,
                    'report_status'  => $task->report_status,
                ];
            }
        );
        return view('user.task.index', compact('linkfull', 'title','salesList', 'taskPage', 'statusList', 'totalTask', 'taskList', 'date_select'));
    }

    public function store(TaskRequest $request)
    {
        $task = $this->taskRepository->create($request->except('_token','full_name'));
        $user = $this->userRepository->getUser(); 
        $fullname=trim($user->first_name." ".$user->last_name);
        $notification = array(
            'partner_id'=>$user->partner_id,
            'user_id' => $user->id,
            'url'=> "",
            'title'=>"@".$fullname." tạo Task: ".$task->task_title,
            'desc'=>"@".$fullname." đã báo cáo task ".$task->id.". ".$task->task_description,
            'status'=>0, 
            'type'=>"task",
            'item_id'=>$task->id,
            'created_at'=> date("Y-m-d H:i:s"),
            'date_notification'=>time()
        );
        Notification::insert($notification);
        return $task->id;
    }


    public function update($task, Request $request)
    {
        $task = $this->taskRepository->find($task);
        $task->update($request->except('_method', '_token'));
    }

    public function delete($task)
    {
        $task = $this->taskRepository->find($task);
        $task->delete();

    }

    /**
     * Ajax Data
     */
    public function data()
    {
        $user = $this->userRepository->getUser();
        return $this->taskRepository->orderBy("finished", "ASC")
            ->orderBy("task_end", "DESC")->all()->where('user_id', $user->id)
            ->map(function ($task) {
                return [
                    'task_from' => $task->task_from_users->full_name,
                    'id' => $task->id,
                    'finished' => $task->finished,
                    'task_deadline' => $task->task_end,
                    "task_description" => $task->task_description,
                ];
            });

    }
    public function editag(Request $request)
    {
        $user = $this->userRepository->getUser();
        $title = trans('task.tasks');
        $task=null;
        $id=$request->id;
        //Status list
		$statusList=CallActionStatus::where('partner_id','=',$user->partner_id)->orderBy('position', 'asc')->get()
		->map( function ( $statusList ) {
			return [
				'title' => $statusList->title,
				'value' => $statusList->id,
			];
		} )->pluck( 'title', 'value')
        ->prepend(trans('lead.all'), '');
        
        if($id){
            $task = Task::find($id);
        }
        $listUserSales=$this->userRepository->getAllStaffOfUser($user->id);
        $staffs="";
        if($listUserSales){
            $staffs=User::join('partner_user','partner_user.user_id','=','users.id')
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
            ->whereIn('users.id',$listUserSales)
            ->get()
            ->map( function ( $salesList ) {
                return [ 
                    'title' => $salesList->first_name." ".$salesList->last_name,
                    'value' => $salesList->id,
                ];
            } )->pluck( 'title', 'value')
            ->prepend(trans('dashboard.select_staff'), '');
		}else{
			$staffs=array(''=>trans('dashboard.select_staff'));
		}
        return view('user.task.edittag', compact('title','task', 'statusList', 'staffs'));
    }


   
    
    public function reportTask(Request $request)
    {
        $user = $this->userRepository->getUser();
        $title = trans('task.tasks');
        $task=null;
        $id="";
        $taskreport=null;
        $task_id=$request->task_id;
        $urlredirect=$request->redirect;

        $id=$request->id;
        $statusListFirst = $this->optionRepository->getAll()->where( 'partner_id', $user->partner_id)->where( 'category', 'status_report' )->get();
        if(!isset($statusListFirst) || count($statusListFirst)<=0){
            $statusListFirst = $this->optionRepository->getAll()->where( 'partner_id', 0)->where( 'category', 'status_report' )->get();
        }
        $statusList=$statusListFirst->map( function ( $title ) {
            return [
                'title' => $title->title,
                'value' => $title->id,
            ];
        } )->pluck( 'title', 'value' )
        ->prepend(trans('lead.status_report'), '');
        
        if($id){
            $taskreport = TaskReport::find($id);
        }
        return view('user.task.reporttask', compact('title', 'urlredirect', 'task_id','taskreport', 'statusList'));
    }
    /**/ 
    public function addtasktolead(Request $request){
        $user = $this->userRepository->getUser();
        $task_title  = addslashes($request->task_title);
		$task_description = addslashes($request->task_description) ;
		$task_note = addslashes($request->task_note);
        $lead_id  = addslashes($request->lead_id) ;
        $user_id  = addslashes($request->user_id) ;
        $partner_id=$user->partner_id;
        if($lead_id==""){
            $lead_id=0;
        }

        $task_deadline  =date("Y-m-d H:i:s",strtotime(addslashes(trim($request->task_deadline))));
        $task_start  =date("Y-m-d H:i:s",strtotime(addslashes(trim($request->task_start))));

        $finish  = addslashes($request->finished);
        $id  = addslashes($request->task_id);
        if($task_title!="" && $user){
            $user_id_phutrach=$user_id;
            if($user_id=="" or $user_id==0){
                $user_id_phutrach=$user->id;
            }
            $partner_detail=Partner::where('id',$partner_id)->first();
            $domain=$partner_detail["domain"];
            if($domain=="" || $domain==null){
                $domain="https://fastercrm.com";
            }
            if($id!="" && $id>0){
                $taskupdate=array("task_title"=>$task_title, "task_description"=>$task_description, "task_note"=>$task_note, "lead_id"=>$lead_id, "task_end"=>$task_deadline,  "task_start"=>$task_start, "task_from_user"=>$user->id, "user_id"=>$user_id_phutrach, "finished"=>$finish, "partner_id"=>$partner_id);
                Task::where('id', $id)->update($taskupdate);
                // end add
                
                $url=$domain."/task?id=".$id;
                $notification = array(
                    'partner_id'=>$partner_id,
                    'user_id' => $user_id_phutrach,
                    'url'=> $url,
                    'type'=> "task",
                    'item_id'=> $id,
                    'title'=>"Lịch hẹn làm việc - ".$task_title,
                    'desc'=>$task_description,
                    'status'=>0, 
                    'date_notification'=>strtotime($task_deadline)
                );
                Notification::where('type_id', $id)->update($notification);
            }else{
                $task = new Task;
                $task->task_title=$task_title;
                $task->task_description=$task_description;
                $task->lead_id=$lead_id;
                $task->task_start=$task_start;
                $task->task_end=$task_deadline;
                $task->full_name=$user->first_name." ".$user->last_name;
                $task->finished=$finish;
                $task->user_id=$user_id_phutrach;
                $task->partner_id=$partner_id;
                $task->save();
                $id=$task->id;
                
                $taskupdate=array("task_title"=>$task_title, "task_description"=>$task_description, "task_note"=>$task_note, "lead_id"=>$lead_id, "task_end"=>$task_deadline, "task_start"=>$task_start, "full_name"=>$user->first_name." ".$user->last_name, "finished"=>$finish, "user_id"=>$user_id_phutrach, "partner_id"=>$partner_id);
                // end add
                $url=$domain."/task?id=".$id;
                $notification = array(
                    'partner_id'=>$partner_id,
                    'user_id' => $user_id_phutrach,
                    'url'=> $url,
                    'type'=> "task",
                    'item_id'=> $id,
                    'title'=>"Lịch hẹn làm việc - ".$task_title,
                    'desc'=>$task_description,
                    'status'=>0, 
                    'created_at'=> date("Y-m-d H:i:s"),
                    'date_notification'=>strtotime($task_deadline),
                    'type_id'=>$id
                );
                Notification::insert($notification);
                //Task::insert($taskupdate); 
            }
            
            return response()->json( ["Result"=>$taskupdate], 200 );
        }
        return response()->json( [], 200 );
    }  
    /**/ 
    public function addReportTask(Request $request){
        $user = $this->userRepository->getUser();
		$task_description = addslashes($request->task_report_description) ;
		$task_note = addslashes($request->task_note);
        $finish  = addslashes($request->finished);
        $status  = addslashes($request->status);
        $task_id = addslashes($request->task_id);
        $file = $request->file('file_report');
        $redirect = addslashes($request->redirect);
        $filereport="";
        if( $redirect!=""){
            $redirect=urldecode($redirect);
        }else{
            $redirect="task";
        }
       // $filereport = $request->file('file_report')->getRealPath();
        if($file){
            $filereport=$file->move('upload', $file->getClientOriginalName());
        } 
        $id  = addslashes($request->id);
        if($task_description!="" && $task_id>0){
            $statusTypeUpdate=2;
            if($status!=""){
                $titleStatus = $this->optionRepository->getAll()->where( 'id', $status)->first();
                $statusTypeUpdate=$titleStatus["value"];
            }
            if($id!="" && $id>0){
                $taskreport=array("task_report_description"=>$task_description, "date_report"=>date("Y-m-d H:i:s"), "user_id"=>$user->id, "task_id"=>$task_id, "status"=>$status, "type_task"=>$statusTypeUpdate);
                TaskReport::where('id', $id)->update($taskreport);
            }else{
                $taskreport=array("task_report_description"=>$task_description, "date_report"=>date("Y-m-d H:i:s"), "user_id"=>$user->id, "task_id"=>$task_id, "status"=>$status, "file_report"=>$filereport, "type_task"=>$statusTypeUpdate);
                TaskReport::insert($taskreport);
            }
            $taskDetail=Task::where('id', $task_id)->first(); 
            Task::where('id', $task_id)->update(["report_status"=>1, "work_status"=>$statusTypeUpdate]);
            $fullname=trim($user->first_name." ".$user->last_name);

            $notification = array(
                'partner_id'=>$user->partner_id,
                'user_id' => $user->id,
                'url'=> "",
                'title'=>"@".$fullname." báo cáo Task: ".$taskDetail->task_title,
                'desc'=>"@".$fullname." đã báo cáo task ".$task_id.". ".$task_description,
                'status'=>0, 
                'type'=>"task",
                'item_id'=>$task_id,
                'created_at'=> date("Y-m-d H:i:s"),
                'date_notification'=>time()
            );
            Notification::insert($notification);



            return redirect( $redirect );
        }
        return redirect( $redirect );
    }    
    /* */
    public function history(Request $request){
		$lead_id  = $request->lead_id;

		$logData=Task::select('tasks.*', 'call_action_status.type as type_task', 'call_action_status.title as title_status')->leftJoin('call_action_status', 'call_action_status.id','=','tasks.finished')->where('tasks.lead_id',$lead_id)->orderBy("tasks.id", "desc")->paginate(50)->appends(request()->query());
        $logshow=$logData->map( function ( $logs){
                return [
					'id' => $logs->id,
					'task_deadline' => $logs->task_end,
                    'task_description' => $logs->task_description,
                    'task_note' => $logs->task_note,
                    'task_title' => $logs->task_title,
                    'status' => $logs->finished,
                    'title_status'=>$logs->title_status,
                ];
            }
        );
        return $logshow;
    }
    public function historyReport(Request $request){
		$task_id  = $request->task_id;
        if($task_id){
            $logData=TaskReport::select('task_reports.*', 'options.value as type_task', 'options.title as title_status', 'users.first_name as first_name', 'users.last_name as last_name')
            ->join('tasks', 'tasks.id','=','task_reports.task_id')
            ->join('users', 'users.id','=','task_reports.user_id')
            ->leftJoin('options', 'options.id','=','task_reports.status')
            ->where('task_reports.task_id',$task_id)->orderBy("task_reports.id", "desc")->paginate(50)->appends(request()->query());
            $logshow=$logData->map( function ( $logs){
                    return [
                        'id' => $logs->id,
                        'task_report_description' => $logs->task_report_description,
                        'date_report' => $logs->date_report,
                        'title_status' => $logs->title_status,
                        'type_task' => $logs->type_task,
                        'file_report' => $logs->file_report,
                        'full_name' => $logs->first_name." ".$logs->last_name,
                    ];
                }
            );
            return $logshow;
        }
		return "";
    }
}