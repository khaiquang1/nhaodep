<?php

namespace App\Http\Controllers\Users;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use App\Models\HrmSetting;
use App\Models\User;
use App\Models\Attendance;
use App\Models\CheckIn;
use App\Models\CameraLogs;

use App\Repositories\UserRepository;
use Cache;
use DB;
class AttendanceController extends UserController
{
    public function __construct(
		UserRepository $userRepository
	) {
		parent::__construct();
		$this->userRepository      = $userRepository;
	}
    public function index(Request $request)
    {
        $date  = addslashes($request->starting_date);
        $warehouse_id = addslashes($request->warehouse_id);
        $daterange  = addslashes($request->daterange);

        if(isset($daterange) && $daterange!="" && $daterange!=0){
			if($daterange==1){
				$starting_date=date("Y-m-d 00:00:00");
				$ending_date=date("Y-m-d 23:59:59");
				$date_select=date("m/d/Y",strtotime($starting_date))." - ".date("m/d/Y",strtotime($ending_date));
			}else{
				$starting_date=date("Y-m-d",strtotime("-".$daterange." days"));
				$ending_date=date("Y-m-d");
				$date_select=date("m/d/Y",strtotime('today - '.$daterange.' days'))." - ".date("m/d/Y");
			}
		}else{
			if($date!=""){
				$dateArray=explode("-",trim($date));
				$starting_date=$this->convertDate(trim($dateArray[0]));
				$ending_date=$this->convertDate(trim($dateArray[1]));
				$starting_date=date("Y-m-d 00:01:00", strtotime($starting_date));
				$ending_date=date("Y-m-d 23:59:00", strtotime($ending_date));
				$date_select=$date;
			}else{
				$starting_date=date("Y-m-d",strtotime('today-30 days'));
				$ending_date=date("Y-m-d");
				$date_select=date("m/d/Y",strtotime('today-30 days'))." - ".date("m/d/Y");
			}
		}
        $dateStartSearch=$starting_date;
        $dateEndSearch=$ending_date;

       // $keyword = addslashes($request->keyword);
        $user_id = addslashes($request->user_id);
        $dataSearch=array('warehouse_id'=>$warehouse_id, 'user_id'=>$user_id);

        $lims_hrm_setting_data = HrmSetting::latest()->first();

        $userData=$this->userRepository->getUser();
        $this->partner_id=$userData->partner_id;

            $lims_lims_user_list_cache = cache('lims_user_list_cache');
            if(isset($lims_lims_user_list_cache) && $lims_lims_user_list_cache!=""){
                $lims_employee_list=$lims_lims_user_list_cache;
            }else{
                $lims_employee_list = User::where('partner_id', $this->partner_id)->get();
                Cache::put('lims_user_list_cache', $lims_employee_list, now()->addMinutes(10));
            }
            
            $lims_attendance_all = Attendance::select('attendances.*', 'users.full_name as fullname', 'users.user_avatar as user_avatar', DB::raw('count(attendances.date) as total_check_in'))
            ->join('users','users.id','=','attendances.user_id')
            ->where(function ($query)  use ($starting_date, $ending_date, $user_id){
                if($starting_date!="" && $starting_date!="0"){
                    $query->whereDate('attendances.date','>=', $starting_date);
                }
                if($ending_date!="" && $ending_date!="0"){
                    $query->whereDate('attendances.date','<=', $ending_date);
                }
                /*
                if($warehouse_id!="" && $warehouse_id!="0"){
                    $query->where('users.warehouse_id',$warehouse_id);
                } */
                if($user_id!="" && $user_id!="0"){
                    $query->where('users.id',$user_id);
                }
            }) 
            ->where('users.partner_id',$this->partner_id)
            ->distinct()
            ->groupBy('users.id')
            ->orderBy('attendances.id', 'desc')->get();
            $title="Chấm công";
        return view('user.attendance.index', compact('lims_employee_list', 'lims_hrm_setting_data', 'lims_attendance_all', 'dataSearch', 'dateStartSearch', 'dateEndSearch', 'title', 'date_select'));

        
    }

    public function detail(Request $request)
    {
        $start_date = addslashes($request->start_date);
        $end_date = addslashes($request->end_date);
        $id = addslashes($request->id);
       // $keyword = addslashes($request->keyword);
       if($id=="" || $id==0){
        return redirect()->back()->with('not_permitted', 'Attendance deleted successfully');
       }
        if($start_date=="" && $end_date==""){
            $showcustomer=0;
            $start_date = date("Y").'-'.date("m").'-'.'01';
            $end_date = date("Y-m-d");
        }
        $dateEndSearch=date("m/d/Y",strtotime($end_date));
        $dateStartSearch=date("m/d/Y",strtotime($start_date));
		$userData=$this->userRepository->getUser();
        $this->partner_id=$userData->partner_id;
       // if($role->hasPermissionTo('attendance')) {
            
            $lims_hrm_setting_data = HrmSetting::latest()->first();
            $general_setting = DB::table('general_settings')->latest()->first();

            $lims_attendance_all = Attendance::select('attendances.*', 'employees.name as fullname')
            ->join('employees','employees.id','=','attendances.employee_id')
            ->where(function ($query)  use ($start_date, $end_date){
                if($start_date!="" && $start_date!="0"){
                    $query->whereDate('attendances.date','>=', $start_date);
                }
                if($end_date!="" && $end_date!="0"){
                    $query->whereDate('attendances.date','<=', $end_date);
                }
            }) 
            ->where('employees.id',$id)
            ->distinct()
            ->orderBy('attendances.id', 'desc')->get();
            
        $dataSearch=array('start_date'=>$start_date, 'end_date'=>$end_date);

        return view('attendance.detail', compact('lims_hrm_setting_data', 'lims_attendance_all', 'dataSearch', 'dateStartSearch', 'dateEndSearch'));
    }

    
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
        $data = $request->all();
        $employee_id =  $data['employee_id'];
        $lims_hrm_setting_data = HrmSetting::latest()->first();
        $checkin = $lims_hrm_setting_data->checkin;
        foreach ($employee_id as $id) {
            $data['date'] = date('Y-m-d', strtotime(str_replace('/', '-', $data['date'])));
            $data['user_id'] = Auth::id();
            $lims_attendance_data = Attendance::whereDate('date', $data['date'])->where('employee_id', $id)->first();
            if(!$lims_attendance_data){
                $data['employee_id'] = $id;
                $diff = strtotime($checkin) - strtotime($data['checkin']);
                if($diff >= 0)
                    $data['status'] = 1;
                else
                    $data['status'] = 0;
                Attendance::create($data);
            }
        }
        return redirect()->back()->with('message', 'Attendance created successfully');
        //return date('h:i:s a', strtotime($data['from_time']));
    }

    
    public function show($id)
    {
        //
    }

    
    public function edit($id)
    {
        //
    }

    
    public function update(Request $request, $id)
    {
        //
    }

    public function deleteBySelection(Request $request)
    {
        $attendance_id = $request['attendanceIdArray'];
        foreach ($attendance_id as $id) {
            $lims_attendance_data = Attendance::find($id);
            $lims_attendance_data->delete();
        }
        return 'Attendance deleted successfully!';
    }
    
    public function destroy($id)
    {
        $lims_attendance_data = Attendance::find($id);
        $lims_attendance_data->delete();
        return redirect()->back()->with('not_permitted', 'Attendance deleted successfully');
    }

    public function updateLater(Request $request){
        //if type=1 approve, 2 reject
        $id=$request->id;
        $note=$request->note;
        $status=$request->status;

        if($id=="" || $id==0){
            return response()->json(['success' => 0, 'messenger'=>"Không tìm thấy ID"], 200);
        }
        $lims_attendance_data = Attendance::find($id);
        $message="Cập nhât thành công";

        if(isset($id) && $status!=""){

            $lims_attendance_data->status=$status;
            $lims_attendance_data->note=$note;
            $lims_attendance_data->updated_at=date("Y-m-d H:i:s");
            $lims_attendance_data->update();
            return redirect('attendance/detail?id='.$lims_attendance_data->employee_id)->with('message', $message);
        }
        return redirect('attendance')->with('message', "Có lỗi xảy ra có thể User hoặc đơn hàng không tìm thấy");
    } 

	public function customerlog(Request $request)
	{
			$title="Danh sách hình đã chụp";
			$userData=$this->userRepository->getUser();
			$this->partner_id=$userData->partner_id;
			$page = addslashes($request->page);
			$keyword = addslashes($request->keyword);
			$person_id = addslashes($request->person_id);
			$device_id = addslashes($request->device_id);
			$place_id = addslashes($request->place_id);
			
			$type = addslashes($request->type);

			$start_date = addslashes($request->start_date);
			$end_date = addslashes($request->end_date);

			if($start_date=="" && $end_date==""){
				$start_date = date("Y").'-'.date("m").'-'.'01';
				$end_date = date("Y-m-d");
			}

			$customer_group_id = addslashes($request->customer_group_id);
			$tags = addslashes($request->tags);
	   

			$dateEndSearch=date("m/d/Y",strtotime($end_date));
			$dateStartSearch=date("m/d/Y",strtotime($start_date));


			$start=1;
			if(isset($page) && $page>1){
				$start=$page;
			}
			$limit=20; 

			$customerQuery=CameraLogs::select('camera_logs.*')
			->join('camera_partner','camera_partner.deviceID','=','camera_logs.deviceID');
			if($person_id!="" && $person_id!="0"){
			$customerQuery->leftJoin('check_in', 'check_in.person_id','=','camera_logs.personID');
			}
			$customerQuery->where(function ($query)  use ($keyword, $start_date, $end_date, $person_id, $device_id, $place_id){
				if($keyword!="" && $keyword!="0"){
					$query->where(function ($query1)  use ($keyword){
							$query1->where('camera_logs.deviceName','LIKE', "%{$keyword}%");
							$query1->orWhere('camera_logs.personName','LIKE', "%{$keyword}%");
					});
				}
				if($person_id!="" && $person_id!="0"){
				   $query->where(function ($query1)  use ($person_id){
					   $query1->where('check_in.person_id', $person_id);
					   $query1->orWhere('check_in.person_id_alias', $person_id);
				   });
				}
				if($device_id!="" && $device_id!="0"){
				   $query->where('camera_logs.deviceID',$device_id);
			   }
			   if($place_id!="" && $place_id!="0"){
				   $query->where('camera_logs.placeID',$place_id);
			   }
				if($start_date!="" && $start_date!="0"){
				   $query->whereDate('camera_logs.date','>=',$start_date);
				  }
				   if($end_date!="" && $end_date!="0"){
					   $query->whereDate('camera_logs.date','<=',$end_date);
				   }
			})
			//->whereIn('personType',array(2,1))
			->where('camera_partner.partner_id',$this->partner_id)
			->distinct()
			->orderBy('camera_logs.id','desc');
			$totalCustomer=$customerQuery->count();
			$lims_customer_all=$customerQuery->paginate(20)->appends(request()->query());
		   
			$dataSearch=array('keyword'=>$keyword, 'start_date'=>$start_date, 'end_date'=>$end_date);
			$lims_customer_list=User::select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
						->where('users.partner_id','=',$this->partner_id)
						->get();
			return view('user.attendance.customerlog', compact('lims_customer_all', 'totalCustomer', 'dataSearch', 'dateEndSearch', 'dateStartSearch', 'title', 'lims_customer_list'));
	}
	public function customervistor(Request $request)
	{
			$page = addslashes($request->page);
			$keyword = addslashes($request->keyword);
			$person_id = addslashes($request->person_id);
			$device_id = addslashes($request->device_id);
			$place_id = addslashes($request->place_id);
			
			$type = addslashes($request->type);

			$start_date = addslashes($request->start_date);
			$end_date = addslashes($request->end_date);

			if($start_date=="" && $end_date==""){
				$start_date = date("Y").'-'.date("m").'-'.'01';
				$end_date = date("Y-m-d");
			}

			$customer_group_id = addslashes($request->customer_group_id);
			$tags = addslashes($request->tags);
	   

			$dateEndSearch=date("m/d/Y",strtotime($end_date));
			$dateStartSearch=date("m/d/Y",strtotime($start_date));


			$start=1;
			if(isset($page) && $page>1){
				$start=$page;
			}
			$limit=20; 
			$userData=$this->userRepository->getUser();
			$this->partner_id=$userData->partner_id;

			$customerQuery=CheckIn::select('check_in.*')
			->join('camera_partner','camera_partner.deviceID','=','check_in.deviceID')
			//->leftJoin('customers', 'customers.person_id','=','check_in.person_id')
			->where(function ($query)  use ($keyword, $start_date, $end_date, $person_id, $device_id, $place_id){
				if($keyword!="" && $keyword!="0"){
					$query->where(function ($query1)  use ($keyword){
							$query1->where('check_in.fullname','LIKE', "%{$keyword}%");
					});
				}
				if($person_id!="" && $person_id!="0"){
					$query->where('check_in.person_id',$person_id);
				}
				if($device_id!="" && $device_id!="0"){
				   $query->where('check_in.deviceID',$device_id);
			   }
			   if($place_id!="" && $place_id!="0"){
				   $query->where('check_in.placeID',$place_id);
			   }
				if($start_date!="" && $start_date!="0"){
				   $query->whereDate('check_in.check_in','>=',$start_date);
				  }
				   if($end_date!="" && $end_date!="0"){
					   $query->whereDate('check_in.check_in','<=',$end_date);
				   }
			})
			->where('check_in.person_id_alias',0)
			->whereIn('check_in.type_person',array(0,3))
			->where('camera_partner.partner_id',$this->partner_id)
			->distinct()
			->groupBy('check_in.person_id')
			->orderBy('check_in.id','desc');
			$totalCustomer=$customerQuery->count();
			$lims_customer_all=$customerQuery->paginate(20)->appends(request()->query());
		   
			$dataSearch=array('keyword'=>$keyword, 'start_date'=>$start_date, 'end_date'=>$end_date, 'device_id'=>$device_id, 'place_id'=>$place_id);
			
			$lims_customer_list=User::select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
						->where('partner_user.partner_id','=',$this->partner_id)
						->get();
		
		   $title="Danh sách hình đã chụp";

			return view('user.attendance.customercheckin', compact('lims_customer_all', 'totalCustomer', 'dataSearch', 'dateEndSearch', 'dateStartSearch', 'lims_customer_list', 'title'));
	}
	public function customerSyndata(Request $request){
	   $keysyn=$request->keysyn;
	   $synname=$request->syndata;
	   $sysn=0;
	   $delete=0;
	   $staff=0;
	   if(isset($request->syndata) && $request->syndata!=""){
		   $sysn=1;
	   }
	   if(isset($request->staffdata) && $request->staffdata!=""){
		   $staff=1;
	   }
	   if(isset($request->deletedata) && $request->deletedata!=""){
		   $delete=1;
	   }

	   if(count($keysyn)>0 && $delete==1){
		   $personalias=[];
		   for($i=0;$i<count($keysyn);$i++){
			   if($keysyn[$i]!=""){
				   $personalias[]=$keysyn[$i];
			   }
		   }
		   if(count($personalias)>0){
			   $checkIn=CheckIn::whereIn('person_id',$personalias)->update(["type_person"=>4]);
		   }
	   }

	   if(count($keysyn)>0 && $staff==1){
		   $personalias=[];
		   for($i=0;$i<count($keysyn);$i++){
			   if($keysyn[$i]!=""){
				   $personalias[]=$keysyn[$i];
			   }
		   }
		   if(count($personalias)>0){
			   $checkIn=CheckIn::whereIn('person_id',$personalias)->update(["type_person"=>1]);
		   }
	   }

	   if(count($keysyn)>1 && $sysn==1){
		   $person_id=$keysyn[0];
		   $personalias=[];
		   for($i=1;$i<count($keysyn);$i++){
			   if($keysyn[$i]!=""){
				   $personalias[]=$keysyn[$i];
			   }
		   }
		   if(count($personalias)>0 && $person_id!=0){
			   $checkIn=CheckIn::whereIn('person_id',$personalias)->update(["person_id_alias"=>$person_id]);
		   }
	   }
	   return redirect()->back()->with('not_permitted', 'Data add Successfully');
	   
	}

	public function updatePersonCustomer(Request $request){
	   $person_id=$request->person_id;
	   $person_image=$request->person_image;
	   $customer_id=$request->customer_id;
	   if($person_id!="" && $person_image!="" && $customer_id!=""){
		   $checkIn=User::where('id',$customer_id)->update(["person_id"=>$person_id, "user_avatar"=>$person_image]);
		   return redirect()->back()->with('not_permitted', 'Data add Successfully');
	   }else{
		   return redirect()->back()->with('not_permitted', 'Data add Not Successfully');
	   }
	}
}
