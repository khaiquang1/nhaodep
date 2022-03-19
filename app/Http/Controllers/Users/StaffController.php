<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Thumbnail;
use App\Http\Controllers\UserController;
use App\Http\Requests\InviteRequest;
use App\Http\Requests\StaffRequest;
use App\Models\Logs;
use App\Models\LeadAssignStatus;
use App\Models\User;
use App\Models\Partner;
use App\Models\PartnerUser;
use App\Models\Product;
use App\Models\GroupUserStaff;
use App\Models\GroupUser;
use App\Mail\InviteStaff;
use App\Repositories\InviteUserRepository;
use App\Repositories\UserRepository;
use Efriandika\LaravelSettings\Facades\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Yajra\Datatables\Datatables;
use App\Repositories\CallRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\ContractRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\LeadRepository;
use App\Repositories\MeetingRepository;
use App\Repositories\OpportunityRepository;
use App\Repositories\OptionRepository;
use App\Repositories\ProductRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\SalesOrderRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\TaskRepository;
use App\Models\Branch;
use App\Models\UserPermission;
use App\Models\Lead;
use App\Models\CallActionStatus;
use App\Models\ReportStaff;
use App\Models\LeadRouting;
use App\Models\ReportTags;

use Cache;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;


use Carbon\Carbon;
use Sentinel;

class StaffController extends UserController
{
    private $date_format = 'Y-m-d';
    private $emailSettings;
    private $siteNameSettings;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var InviteUserRepository
     */
    private $inviteUserRepository;
/**
     * @var LeadRepository
     */
    private $leadRepository;
    /**
     * @var OpportunityRepository
     */
    private $opportunityRepository;
    /**
     * @var CallRepository
     */
    private $callRepository;
    /**
     * @var MeetingRepository
     */
    private $meetingRepository;
    /**
     * @var QuotationRepository
     */
    private $quotationRepository;
    /**
     * @var SalesOrderRepository
     */
    private $salesOrderRepository;
    /**
     * @var ContractRepository
     */
    private $contractRepository;
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    /**
     * @var SalesTeamRepository
     */
    private $salesTeamRepository;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;
    private $taskRepository;
    /**
     * StaffController constructor.
     * @param UserRepository $userRepository
     * @param InviteUserRepository $inviteUserRepository
     */
    public function __construct(UserRepository $userRepository,
            InviteUserRepository $inviteUserRepository, LeadRepository $leadRepository,
            OpportunityRepository $opportunityRepository,
            CallRepository $callRepository,
            MeetingRepository $meetingRepository,
            QuotationRepository $quotationRepository,
            SalesOrderRepository $salesOrderRepository,
            ContractRepository $contractRepository,
            CompanyRepository $companyRepository,
            SalesTeamRepository $salesTeamRepository,
            ProductRepository $productRepository,
            InvoiceRepository $invoiceRepository,
            OptionRepository $optionRepository,
            TaskRepository $taskRepository)
    {


        $this->middleware('authorized:staff.read', ['only' => ['index', 'data']]);
        $this->middleware('authorized:staff.write', ['only' => ['create', 'store', 'update', 'edit']]);
        $this->middleware('authorized:staff.delete', ['only' => ['delete']]);

        parent::__construct();
        $this->inviteUserRepository = $inviteUserRepository;
        $this->leadRepository = $leadRepository;
        $this->opportunityRepository = $opportunityRepository;
        $this->userRepository = $userRepository;
        $this->callRepository = $callRepository;
        $this->meetingRepository = $meetingRepository;
        $this->quotationRepository = $quotationRepository;
        $this->salesOrderRepository = $salesOrderRepository;
        $this->contractRepository = $contractRepository;
        $this->companyRepository = $companyRepository;
        $this->salesTeamRepository = $salesTeamRepository;
        $this->productRepository = $productRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->optionRepository = $optionRepository;
        $this->taskRepository = $taskRepository;

        $this->date_format = config('settings.date_format');
        $this->emailSettings = Settings::get('site_email');
        $this->siteNameSettings = Settings::get('site_name');
       
        view()->share('type', 'staff');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(StaffRequest $request)
    {
        $title = trans('staff.staffs');
        $dateFormat = config('settings.date_format');
        $partner_id=0;
        if($request->partner_id){
            $partner_id=$request->partner_id;
        }
        $listUser="";
        $userData=$this->userRepository->getUser();
        $user_id=$userData->id;
        /*
        $listUserSales=$this->userRepository->getAllStaffOfUser($userData->id);
        
        if($user_id<=0 || $userData->partner_id==1){
            $listUser=$this->userRepository->getAllStaffOfUser($user_id);
        }else{
            if( $userData->partner_id>0){
                $listUserSales=$this->userRepository->getAllUserOnPartner($userData->partner_id);
                //$listUserParent=$this->userRepository->getAllStaffOfUser($user_id);
                if(isset($listUserSales) && count($listUserSales)>0){
                    //$listUser = array_unique (array_merge ($listUserParent, $listUserSales));
                    $listUser = array_unique ($listUserSales);

                }
                $partner_id=$userData->partner_id;
            }else{
                $listUser=null;
            }
        } */
        $listUser=$this->userRepository->getAllStaffOfUser($userData->id);

        $partnerList =null;
        $users=null;
        $staffData=null;
       if($listUser){
            $staffsQuery = User::select('users.*', 'partner.name as partner_name', 'partner_user.id as partner_user_id')
            ->join('partner_user','users.id','=','partner_user.user_id')
            ->join('partner','partner.id','=','partner_user.partner_id')
            ->where(function ($query)  use ($partner_id){
                if($partner_id!=0){
                    $query->where('partner_user.partner_id','=',$partner_id);
                }
            })
           ->whereIn('users.user_id',$listUser)->groupBy('users.id')
            ->orderBy('users.id', 'DESC');
            
            $totalUser=$staffsQuery->count();
            $staffData=$staffsQuery->paginate(15)->appends(request()->query());
            if($userData->user_id<=0){
                $partnerList =Partner::where('status','=',1)->orderBy("id", "asc")
                ->pluck('name', 'id')->prepend(trans('staff.select_partner'), '');
            }
            $users=$staffData->map( function ( $staff) use ($dateFormat){
                return [
                    'id'           => $staff->id,
                    'full_name'   => $staff->full_name,
                    'partner'   => $staff->partner_name,
                    'email' => $staff->email,
                    'created_at' => $staff->created_at,
                    'partner_user_id'=> $staff->partner_user_id,
                ];
            });
        }
        return view('user.staff.index', compact('title', 'users', 'staffData', 'partnerList'));


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = trans('staff.new');
        $partnerList =Partner::where('status','=',1)->orderBy("id", "asc")
        ->pluck('name', 'id')->prepend(trans('staff.select_partner'), '');
        $userData=$this->userRepository->getUser();
        $this->generateParams();
        return view('user.staff.create', compact('title', 'partnerList', 'userData'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StaffRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StaffRequest $request)
    {
        if ($request->hasFile('user_avatar_file')) {
            $file = $request->file('user_avatar_file');
            $file = $this->userRepository->uploadAvatar($file);
            $request->merge([
                'user_avatar' => $file->getFileInfo()->getFilename(),
            ]);

            $this->generateThumbnail($file);
        }

        $user = Sentinel::registerAndActivate($request->only('first_name', 'last_name', 'email', 'password'));
        $userData=$this->userRepository->getUser();

        $role = Sentinel::findRoleBySlug('staff');
        $role->users()->attach($user);

        $user = $this->userRepository->find($user->id);

        foreach ($request->get('permissions', []) as $permission) {
            $user->addPermission($permission);
        }
        $user->user_id = $this->user->id;
        $user->phone_number = $request->phone_number;
        $user->user_avatar = $request->user_avatar;
        $user->extention_code = $request->extention_code;
        $user->group_id = $request->group_id;
        $user->password_call_center = $request->password_call_center;
        $user->branch_id = $request->branch_id;
        $user->point = $request->point;

        
        
        if(isset($request->partner_id) && $request->partner_id!=""){
            $user->partner_id = $request->partner_id;
        }else{
            if($userData && $userData->partner_id!=0){
                $user->partner_id = $userData->partner_id;
            }
        }
        /*
        if(isset($request->group_user) && count($request->group_user)>0){
            $user->group_list = implode(",",$request->group_user);
        } */
        $user->save();
        $staff_id=$user->id;
        $partner_id=$userData->partner_id;
        if(isset($request->partner_id) && $request->partner_id!=""){
            $insertPartner=array("partner_id"=>$userData->partner_id, "user_id"=>$staff_id);
            PartnerUser::insert($insertPartner);
        }else{
            if($userData && $userData->partner_id!=0){
                $insertPartner=array("partner_id"=>$userData->partner_id, "user_id"=>$staff_id);
                PartnerUser::insert($insertPartner);
            }
        }

        if($request->group_id!=""){
            $listGroupPermission=GroupUser::where('id',$request->group_id)->first();
            $dataPermission=null;
            if($listGroupPermission){
                $listpermission=$listGroupPermission["permissions"];
                foreach($listpermission as $key=>$values){
                    $dataPermission=array('partner_id'=>$partner_id, 'user_id'=>$staff_id, 'permission'=>$key);
                    UserPermission::insert($dataPermission);

                } 
            }
            
           // if(isset($dataPermission) && count($dataPermission)>0){
             //   UserPermission::insert($dataPermission);

            //}
        }
        //$data=new GroupLead;
        /*
        $dataPost=$request->all();
        $dataStaffStatus =array();
        if(isset($dataPost["group_user"]) && count($dataPost["group_user"])>0){
            GroupUserStaff::where('user_id', $staff_id)->where('partner_id',$partner_id)->delete();
            $statusclient=$dataPost["group_user"];
            for($i=0;$i<count($statusclient);$i++){
                $dataStaffStatus[]= array('group_user_id'=>$statusclient[$i], 'user_id'=> $staff_id, 'partner_id'=>$partner_id);
            }
        }
        if(isset($dataStaffStatus) && count($dataStaffStatus)>0){
            GroupUserStaff::insert($dataStaffStatus); // Eloquent approach
        } */
        
        return redirect("staff");
    }


    public function edit($idstaff)
    {

        $staff = User::select('users.*', 'partner.name')
        ->leftJoin('partner_user','users.id','=','partner_user.user_id')
        ->leftJoin('partner','partner.id','=','partner_user.partner_id')
        ->where('users.id','=', $idstaff)->first();
        $title = trans('staff.edit');

        $userData=$this->userRepository->getUser();
        $this->generateParams();
        $partnerList =Partner::where('status','=',1)->orderBy("id", "asc")
        ->pluck('name', 'id')->prepend(trans('staff.select_partner'), '');

        return view('user.staff.edit', compact('title', 'staff', 'partnerList', 'userData'));
    }


    public function update(StaffRequest $request, $staff)
    {
        $userData=$this->userRepository->getUser();
        $user_id=$userData->id;
       
        $staff = $this->userRepository->find($staff);
        if ($request->password != "") {
            $staff->password = bcrypt($request->password);
        }

        if ($request->hasFile('user_avatar_file')) {
            $file = $request->file('user_avatar_file');
            $file = $this->userRepository->uploadAvatar($file);

            $request->merge([
                'user_avatar' => $file->getFileInfo()->getFilename(),
            ]);

            $this->generateThumbnail($file);

        } else {
            $request->merge([
                'user_avatar' => $staff->user_avatar,
            ]);
        }

        
        foreach ($staff->getPermissions() as $key => $item) {
            $staff->removePermission($key);
        }

        foreach ($request->get('permissions', []) as $permission) {
            $staff->addPermission($permission);
        }
        $staff->first_name = $request->first_name;
        $partner_id=0;
        if(isset($request->partner_id) && $request->partner_id!=''){
            PartnerUser::where('partner_id', $request->partner_id)->where('user_id', $staff->id)->delete();
            $insertPartner=array("partner_id"=>$request->partner_id, "user_id"=>$staff->id);
            PartnerUser::insert($insertPartner);
            $staff->partner_id = $request->partner_id;
            $partner_id=$request->partner_id;
        }else{
            $staff->partner_id = $userData->partner_id;
            $partner_id= $userData->partner_id;
        }
       
        $staff->last_name = $request->last_name;
        $staff->phone_number = $request->phone_number;
        $staff->email = $request->email;
        $staff->user_avatar = $request->user_avatar;
        $staff->user_id = $user_id;
        $staff->extention_code = $request->extention_code;
        $staff->group_id = $request->group_id;
        $staff->branch_id = $request->branch_id;
        $staff->password_call_center = $request->password_call_center;
        $staff->point = $request->point;
        $staff->save();
        //$data=new GroupLead;
        $dataPost=$request->all();
        $dataStaffStatus =array();

        if($request->group_id!=""){
            UserPermission::where(['user_id'=>$staff->id])->delete();
            $listGroupPermission=GroupUser::where('id',$request->group_id)->first();
            $dataPermission=null;
            if($listGroupPermission){
                $listpermission=$listGroupPermission["permissions"];
                foreach($listpermission as $key=>$values){
                    $dataPermission[]=array('partner_id'=>$partner_id, 'user_id'=>$staff->id, 'permission'=>$key);
                } 
            }
            UserPermission::insert($dataPermission);
        }
       
        /*
        if(isset($request->group_user) && count($request->group_user)>0){
            $staff->group_list = implode(",",$request->group_user);
        }
        if(isset($dataPost["group_user"]) && count($dataPost["group_user"])>0){
            GroupUserStaff::where('user_id', $user_id)->where('partner_id',$staff->partner_id)->delete();
            $statusclient=$dataPost["group_user"];
            for($i=0;$i<count($statusclient);$i++){
                $dataStaffStatus[]= array('group_user_id'=>$statusclient[$i], 'user_id'=> $user_id, 'partner_id'=>$staff->partner_id);
            }
        }
        if(isset($dataStaffStatus) && $dataStaffStatus!="" && count($dataStaffStatus)>0){
            GroupUserStaff::insert($dataStaffStatus); // Eloquent approach
        } */
        return redirect("staff");
    }

    public function show($staff)
    {
        $staff = $this->userRepository->find($staff);
        $title = trans('staff.show_staff');
        $action = "show";
        return view('user.staff.show', compact('title', 'staff','action'));
    }

    public function delete($staff)
    {
        $staff = $this->userRepository->find($staff);
        $title = trans('staff.delete_staff');
        return view('user.staff.delete', compact('title', 'staff'));
    }
    public function deletepartner($id)
    {
        
        $userData=$this->userRepository->getUser();
        $user_id=$userData->id;
        $partner_id=$userData->partner_id;
        $partnerData=PartnerUser::where('id', $id)->where('partner_id',$partner_id)->first();

        if($partnerData){
            if($partnerData->partner_id==$partner_id){
                PartnerUser::where('id', $id)->delete();
                Partner::where('id',$partner_id)->update(['total_user'=> DB::raw('total_user-1')]);

            }else{
                die();
            }
        }
        
        return redirect('staff');
    }

    
    public function destroy($staff)
    {
        $staff = $this->userRepository->find($staff);
        if ($staff->id != '1') {
            $staff->delete();
        }
        return redirect('staff');
    }


    public function data(Datatables $datatables)
    {
        $dateFormat = config('settings.date_format');
            $staffs = $this->userRepository->getAllNew()->with('staffSalesTeam')
            ->get()
            ->filter(function ($user) {
                return ($user->inRole('staff') && $user->id!=$this->user->id);
            });
            $totalUser=$staffs->count();
			$userData=$staffs->paginate(15)->appends(request()->query());
			$user=$leadsData->map( function ( $lead) use ($dateFormat){
                return [
                    'id'           => $lead->id,
					'created_at'   => date($dateFormat,strtotime($lead->created_at)),
                    'opportunity' => $lead->opportunity,
                ];
            });
            return view( 'user.staff.index', compact( 'title', 'leads', 'leadsData', 'salesList', 'statusList', 'sourceList', 'leadGroupSource', 'totalLead', 'date_select') );

    }

    /**
     * @param $file
     */
    private function generateThumbnail($file)
    {
        Thumbnail::generate_image_thumbnail(public_path() . '/uploads/avatar/' . $file->getFileInfo()->getFilename(),
            public_path() . '/uploads/avatar/' . 'thumb_' . $file->getFileInfo()->getFilename());
    }

    public function invite()
    {
        $title = trans('staff.invite_staffs');
        $date_format = config('settings.date_format');
        return view('user.staff.invite', compact('title','date_format'));
    }
    public function inviteSave(InviteRequest $request)
    {
        if (filter_var($this->emailSettings, FILTER_VALIDATE_EMAIL)) {
            $emails = array_filter(array_unique(explode(';', $request->emails)));
            $userData=$this->userRepository->getUser();
            $partner_id=$userData->partner_id;
           
            $dataLimit = Partner::selectRaw('number_sales as numberLimit, total_user')->where('partner.id',$partner_id)->first();
            
            $numberconlai=$dataLimit->numberLimit-$dataLimit->total_user;
            
            if($numberconlai>0){
                $i=0;
                foreach ($emails as $key => $email) {
                    $i++;
                    $checkUser=User::where('email','=',$email)->first();
                    if($checkUser){
                        $partnerData=PartnerUser::where('user_id', $checkUser->id)->where('partner_id', $partner_id)->first();
                        if(!$partnerData){
                            $insertPartner=array('user_id'=>$checkUser->id,'partner_id'=>$partner_id);
                            PartnerUser::insert($insertPartner);
                            $userUpdate = User::find($checkUser->id);
                            $userUpdate->user_id = $userData->id;
                            $userUpdate->partner_id = $partner_id;
                            $userUpdate->save();  
                            Partner::where('id',$partner_id)->update(['total_user'=> DB::raw('total_user+1')]);
                        }
                    }
                    if($i>=$numberconlai){
                        return redirect('staff?mes=1');
                    }
    
                }
                $numberconlai=$numberconlai-$i;
                return redirect('staff?mes=2&number='.$numberconlai);
            }else{
                return redirect('staff?mes=3');
            }
           
        } else {
            flash(trans('staff.invalid-email'))->error();
        }
        return redirect('staff?mes=2');
    } 
    /*
    public function inviteSave(InviteRequest $request)
    {
        if (filter_var($this->emailSettings, FILTER_VALIDATE_EMAIL)) {
            $emails = array_filter(array_unique(explode(';', $request->emails)));

            foreach ($emails as $key => $email) {
                $this->inviteUserRepository->deleteWhere([
                    'claimed_at' => null,
                    'email' => $email,
                ]);
                $user_email = $this->userRepository->usersWithTrashed($email)->count();
                if (0 == $user_email) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $invite = $this->inviteUserRepository->createInvite(['email' => trim($email)]);
                        $inviteUrl = url('invite/'.$invite->code);
                        Mail::to($email)->send(new InviteStaff([
                            'from' => $this->emailSettings,
                            'subject' => 'Invite to '.$this->siteNameSettings,
                            'inviteUrl' => $inviteUrl,
                        ]));
                    } else {
                        flash(trans('Email '.$email.' is not valid.'))->error();
                    }
                } else {
                    flash(trans('Email '.$email.' is already taken.'))->error();
                }
            }
        } else {
            flash(trans('staff.invalid-email'))->error();
        }

        return redirect('staff/invite');
    } */

    public function inviteCancel($id)
    {
        $title = trans('staff.invite_cancel');

        $invite = $this->inviteUserRepository->findWhere([
            'id' => $id,
        ])->first();

        return view('user.staff.invite-cancel', compact('title', 'invite'));
    }

    public function inviteCancelConfirm($id)
    {
        $this->inviteUserRepository->deleteWhere([
            'id' => $id,
            'claimed_at' => null,
        ]);

        return redirect('staff/invite');
    }
    public function dashboard(Request $request)
    {
        $date  = addslashes($request->starting_date);
        $sales_id=addslashes($request->sales_id);
        if($date!=""){
			$dateArray=explode("-",trim($date));
			$starting_date=$this->convertDate(trim($dateArray[0]));
			$ending_date=$this->convertDate(trim($dateArray[1]));
			$starting_date=date("Y-m-d 00:01:00", strtotime($starting_date));
			$ending_date=date("Y-m-d 23:59:00", strtotime($ending_date));
			$date_select=$date;
		}else{
            $ending_date=date("Y-m-d")." 23:59:59";
			$starting_date=date("Y-m-d");
			$date_select=date("m/d/Y",strtotime('today -1 days'))." - ".date("m/d/Y");
        }
        $daterange  = addslashes($request->daterange);
        if($daterange=='thismonth'){
            $starting_date=date("Y-m-01");
            $ending_date=date("Y-m-d 23:59:00");
            $date_select=date("m/d/Y",strtotime($starting_date))." - ".date("m/d/Y",strtotime($ending_date));
        }elseif($daterange=='lastmonth'){
            $starting_date=date("Y-m-d 00:00:00", strtotime("first day of previous month"));
            $ending_date=date("Y-m-d 23:59:59", strtotime("last day of previous month"));
            $date_select=date("m/d/Y",strtotime($starting_date))." - ".date("m/d/Y",strtotime($ending_date));
        }else{
            if(isset($daterange) && $daterange==1){
                $starting_date=date("Y-m-d 00:00:00");
                $ending_date=date("Y-m-d 23:59:59");
                $starting_date_search=date("Y/m/d");
                $ending_date_search=date("Y/m/d");
                $date_select=$starting_date_search." - ".$ending_date_search;
            }elseif(isset($daterange) && $daterange==2){
                    $starting_date=date("Y-m-d 00:00:00", strtotime("-".($daterange-1)." days"));
                    $ending_date=date("Y-m-d 23:59:59", strtotime("-".($daterange-1)." days"));
                    $starting_date_search=date("Y/m/d", strtotime("-".($daterange-1)." days"));
                    $ending_date_search=date("Ym/d", strtotime("-".($daterange-1)." days"));
                    $date_select=$starting_date_search." - ".$ending_date_search;

            }elseif(isset($daterange) && $daterange!=""){
                $starting_date=date("Y-m-d 00:00:00",strtotime("-".$daterange." days"));
                $ending_date=date("Y-m-d 23:59:59");
                $starting_date_search=date("Y/m/d",strtotime("-".$daterange." days"));
                $ending_date_search=date("Y/m/d");
                $date_select=$starting_date_search." - ".$ending_date_search;
            }
        }
        $userData=$this->userRepository->getUser();
        $this->partner_id=$userData->partner_id;
        
		$listUserAssignCache = cache('listUserAssignCache5'.$this->partner_id.$userData->id);
		$listUserCache = cache('listUserCache5'.$this->partner_id.$userData->id);
        
		if(isset($listUserAssignCache) && $listUserAssignCache!=""){
			$listUserAssign=$listUserAssignCache;
			$listUser=$listUserCache;

		}else{
			$grouppermission=GroupUser::getGroup();
			$listUser=$this->userRepository->getUserListMessenger($grouppermission, $userData);
			$listUserAssign=$this->userRepository->getAllUserOfPermissionOfStaff($userData, array("messenger.view_other", "messenger.full", "messenger.view_person"));
			Cache::put('listUserAssignCache5'.$this->partner_id.$userData->id, $listUserAssign, now()->addMinutes(10));
			Cache::put('listUserCache5'.$this->partner_id.$userData->id, $listUser, now()->addMinutes(10));
        } 
        
        //salesList
		$listUserCache="1";
		$usercache=$userData->id;
        $salesListCache = cache('salesListCache_2'.$this->partner_id.$listUserCache.$usercache);
		if(isset($salesListCache) && $salesListCache!=""){
			$salesList=$salesListCache;
		}else{

			if(isset($listUser) && $listUser!=""){
				$salesList=User::join('partner_user','partner_user.user_id','=','users.id')
							->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
							->where('partner_user.partner_id','=',$this->partner_id)
							->whereIn('users.id',$listUser)
							->get()
							->map( function ( $salesList ) {
								return [ 
									'title' => $salesList->first_name." ".$salesList->last_name,
									'value' => $salesList->id,
								];
							} )->pluck( 'title', 'value')
							->prepend(trans('lead.all'), '');
			}else{
				$salesList=User::join('partner_user','partner_user.user_id','=','users.id')
				->select('users.id', 'users.first_name', 'users.last_name', 'users.user_avatar')
				->where('partner_user.partner_id','=',$this->partner_id)
				->get()
				->map( function ( $salesList ) {
					return [ 
						'title' => $salesList->first_name." ".$salesList->last_name,
						'value' => $salesList->id,
					];
				} )->pluck( 'title', 'value')
				->prepend(trans('lead.all'), '');
			}
			Cache::put('salesListCache_2'.$this->partner_id.$listUserCache.$salesListCache, $salesList, now()->addMinutes(10));
		}
        
        if (Sentinel::check()) {
            
            $userData=$this->userRepository->getUser();
            $this->partner_id=$userData->partner_id;

            if(isset($sales_id) && $sales_id!=""){
                $id=$sales_id;
                $sales=array($sales_id);
            }else{
                $id=$userData->id;
                if($userData->user_id==1){
                    $sales=User::select('id')->where('partner_id','=',$this->partner_id)->get()->pluck( 'id')->toArray();
                }else{
                    $listSales=[];
                    if(isset($salesList) && count($salesList)>0){
                        foreach($salesList as $key=>$value){
                            if($key!=""){
                                $listSales[]=$key;
                            }
                        }
                    }
                    $sales=$listSales;
                }
            }
            if(count($sales)>0){
                $salesSearch=implode(",",$sales);
            }else{
                $salesSearch=$sales;
            }
            $cacheKey=md5($this->partner_id.$starting_date.$ending_date.$salesSearch);

            $staffDetail = $this->userRepository->find($id);
            //$customers = $this->companyRepository->getAll()->count();
            //$contracts = $this->contractRepository->getAll()->count();
            //$opportunities = $this->opportunityRepository->getAll()->count();
            //$products = Product::where('partner_id', $this->partner_id)->count();
            //Check tồng lead assign ->join('call_action_status','call_action_status.id','=')


            $totalLeadCache=cache('totalLead'.$cacheKey);
            if(isset($totalLeadCache) && $totalLeadCache!=""){
                $totalLead=$totalLeadCache;
            }else{
                $totalLead=Lead::whereIn('sales_person_id',$sales)->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->where('group_id',45)->count();
                Cache::put('totalLead'.$cacheKey, $totalLead, now()->addMinutes(10));
            } 
           

            $totalLeadNotCareCache=cache('totalLeadNotCare'.$cacheKey);
            if(isset($totalLeadNotCareCache) && $totalLeadNotCareCache!=""){
                $totalLeadNotCare=$totalLeadNotCareCache;
            }else{
                $totalLeadNotCare=Lead::where('sales_person_id',0)->where('messenger', '!=', '')->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->where('group_id',45)->count();
                Cache::put('totalLeadNotCare'.$cacheKey, $totalLeadNotCare, now()->addMinutes(10));
            } 
           
            $totalCustomerCache=cache('totalCustomerCare'.$cacheKey);
            if(isset($totalCustomerCache) && $totalCustomerCache!=""){
                $totalCustomer=$totalCustomerCache;
            }else{
                $totalCustomer=Lead::whereIn('sales_person_id',$sales)->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->where('group_id',44)->count();
                Cache::put('totalCustomerCare'.$cacheKey, $totalCustomer, now()->addMinutes(1));
            } 
           
            $LeadRoutingDataCache=cache('LeadRoutingDataCache'.$cacheKey);
            if(isset($LeadRoutingDataCache) && $LeadRoutingDataCache!=""){
                $totalAssign=$LeadRoutingDataCache;
            }else{
                $totalAssign=LeadRouting::whereIn('user_id',$sales)->where('date','>=',$starting_date)->where('date','<=',$ending_date)->sum('number'); 
                Cache::put('LeadRoutingDataCache'.$cacheKey, $totalAssign, now()->addMinutes(30));
            } 
            /*
            $totalLead=Lead::whereIn('sales_person_id',$sales)->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->where('group_id',45)->count();

            $totalLeadNotCare=Lead::where('sales_person_id',0)->where('messenger', '!=', '')->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->where('group_id',45)->count();
 

            $totalCustomer=Lead::whereIn('sales_person_id',$sales)->where('created_at','>=',$starting_date)->where('created_at','<=',$ending_date)->where('group_id',44)->count();

            $totalAssign=LeadRouting::whereIn('user_id',$sales)->where('date','>=',$starting_date)->where('date','<=',$ending_date)->sum('number');  */

            $list_lead_group = array();
            $listGroupLead=CallActionStatus::where("partner_id",$this->partner_id)->where('type',45)->get();
            foreach($listGroupLead as $listData){
                $cacheKey=md5($this->partner_id.$listData["id"].implode(",",$sales).$ending_date.$starting_date);
                $leadCache=cache('leadGroupLeadCache1'.$cacheKey);
                $leadReplyCache=cache('leadGroupLeadReplyCache'.$cacheKey);
                $leadcountReply=0;
                   // $leadcountReply=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('group_id',$listData["id"])->where('type','oldCareReply')->sum('number');

                if(isset($leadCache) && $leadCache!=""){
                    $leadcount=$leadCache;
                 //   $leadcountReply=$leadReplyCache;
                }else{

                    $leadcount=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('group_id',$listData["id"])->where('type','oldCare')->sum('number');

                 //   $leadcountReply=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('group_id',$listData["id"])->where('type','oldCareReply')->sum('number');


                    //$leadcount=Lead::select("leads.id")->join('logs','logs.lead_id','=','leads.id')->where('logs.created_at','>=',$starting_date)->where('logs.created_at','<=',$ending_date)->where('leads.created_at','>',$starting_date)->where('leads.created_at','<',$ending_date)->whereIn('leads.sales_person_id',$sales)->where('leads.partner_id', $this->partner_id)->where('leads.status', $listData["id"])->groupBy('logs.lead_id')->count();
                    Cache::put('leadGroupLeadCache1'.$cacheKey, $leadcount, now()->addMinutes(20));
                //    Cache::put('leadGroupLeadReplyCache'.$cacheKey, $leadcountReply, now()->addMinutes(20));
                } 
                $list_lead_group[] =
                    [
                        'id' =>$listData["id"],
                        'name' =>$listData["title"],
                        'color' =>$listData["color_bg"],
                        'leads'=>$leadcount,
                        'leadsReply'=>$leadcountReply
                    ];
            }
            $listLeadTitle=array();
            $groupLeadTitle=implode(",", $listLeadTitle);
            $list_customer_group = array();
            $listGroupCustomer=CallActionStatus::where("partner_id",$this->partner_id)->where('type',44)->get();
            foreach($listGroupCustomer as $listData){
                $cacheKey2=md5($this->partner_id.$listData["id"].implode(",",$sales).$ending_date.$starting_date);
                $leadGroupClientCache=cache('leadGroupClient'.$cacheKey2);
                $leadGroupClientReplyCache=cache('leadGroupClientReply'.$cacheKey2);

                if($leadGroupClientCache!="" && $leadGroupClientReplyCache!=""){
                    $lientCount=$leadGroupClientCache;
                    $clientCountReply=$leadGroupClientReplyCache;
                }else{
                   // $leadcount=Lead::select("leads.id")->join('tasks','tasks.lead_id','=','leads.id')->where('tasks.task_start','>=',$starting_date)->where('tasks.task_start','<=',$ending_date)->whereIn('leads.sales_person_id',$sales)->where('leads.partner_id', $this->partner_id)->where('leads.status', $listData["id"])->count();

                   $clientCountReply=ReportStaff::whereIn('user_id',$sales)
                   ->where('date_create','>=', $starting_date)
                   ->where('date_create','<=',$ending_date)->where('type','totalClientOldHasReply')->sum('number');
                   
                   $clientcount=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalClientOld')->sum('number');

                    Cache::put('leadGroupClient'.$cacheKey2, $clientcount, now()->addMinutes(20));
                    Cache::put('leadGroupClientReply'.$cacheKey2, $clientCountReply, now()->addMinutes(20));

                } 
                $list_customer_group[] =
                    [
                        'id' =>$listData["id"],
                        'name' =>$listData["title"],
                        'color' =>$listData["color_bg"],
                        'leads'=>$leadcount,
                        'leadsReply'=>$clientCountReply
                    ];
            }
            $listClientTitle=array();
            foreach($listGroupLead as $listData){
                $listClientTitle[]='"'.$listData["title"].'"';
            }
            $groupClientTitle=implode(",", $listClientTitle);
            $cacheCode=md5($this->partner_id.implode(",",$sales).$ending_date.$starting_date);


            //$cacheTotalOrder=md5($this->partner_id.implode(",",$sales).$ending_date.$starting_date);
            $cacheTotalLeadOld=cache('TotalLeadOld'.$cacheCode);
            if(isset($cacheTotalLeadOld) && $cacheTotalLeadOld!=""){
                $totalLeadOld=$cacheTotalLeadOld;
            }else{
                $totalLeadOld=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalLeadOld')->sum('number');
                Cache::put('TotalLeadOld'.$cacheCode, $totalLeadOld, now()->addMinutes(20));
            } 

            $cacheTotalLeadOld=cache('TotalLeadOldReply'.$cacheCode);
            if(isset($cacheTotalLeadOld) && $cacheTotalLeadOld!=""){
                $totalLeadReply=$cacheTotalLeadOld;
            }else{
                $totalLeadReply=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalLeadOldHasReply')->sum('number');
                Cache::put('TotalLeadOldReply'.$cacheCode, $totalLeadReply, now()->addMinutes(20));
            } 
            

            $cacheReportTags=cache('ReportTags2'.$cacheCode);
            if(isset($cacheReportTags) && $cacheReportTags!=""){
                $totalReportTags=$cacheReportTags;
            }else{
                $totalReportTags=ReportTags::whereIn('user_id',$sales)->where('created_at','>=', $starting_date)->where('created_at','<=',$ending_date)->count('id');
                Cache::put('ReportTags2'.$cacheCode, $totalReportTags, now()->addMinutes(20));
            }

            
            $totalLeadOldDate = cache('totalLeadReplyCache3'.$cacheCode);
            $totalLeadReplyDay = cache('totalLeadCache3'.$cacheCode);
            $start_date_list = cache('start_date2'.$cacheCode);;

            if(isset($totalLeadReply) && $totalLeadReplyDay!="" && $totalLeadOldDate!="" && $start_date_list!=""){
                $totalLeadReplyData = $totalLeadReplyDay;
                $totalLeadOldData = $totalLeadOldDate;
                $daysListData = $start_date_list;
            }else{ 
                $start = strtotime(date('Y-m-d', strtotime('-30 days', strtotime(date('Y-m-d') ))));
                $end = strtotime(date('Y-m-d', strtotime('+1 day')));
                $totalLeadReplyDataList = null;
                $totalLeadOldDataList = null;
                $daysListDataList = null;
                while($start < $end)
                {
                    $start_date=date("Y-m-d",$start);
                    $end_date=$start_date." 23:59:59";//date("Y-m-d",$end);

                    $totalLeadReplyDate=ReportStaff::whereIn('user_id',$sales)
                    ->where('date_create','>=', $start_date)
                    ->where('date_create','<=',$end_date)->where('type','totalLeadOldHasReply')->sum('number');

                    $totalLeadOldDate=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $start_date)->where('date_create','<=',$end_date)->where('type','totalLeadOld')->sum('number');

                    $totalLeadReplyDataList[] = $totalLeadReplyDate;
                    $totalLeadOldDataList[] = $totalLeadOldDate;
                    $daysListDataList[] = $start_date;
                    $start = strtotime("+1 day", $start);
                }
                $totalLeadReplyData = $totalLeadReplyDataList;
                $totalLeadOldData = $totalLeadOldDataList;
                $daysListData = $daysListDataList;

                Cache::put('totalLeadReplyCache3'.$cacheCode, $totalLeadReplyData, now()->addMinutes(300));
                Cache::put('totalLeadCache3'.$cacheCode, $totalLeadOldData, now()->addMinutes(300));
                Cache::put('start_date3'.$cacheCode, $daysListData, now()->addMinutes(300));
            }

            if(count($daysListData)>0){
                for($i=0;$i<count($daysListData);$i++){
                    $oldLeadOldDays[] = $totalLeadOldData[$i];
                    $newLeadReplyDays[] = $totalLeadReplyData[$i];
                    $daysList[] =$daysListData[$i];
                }
            }
            /*
            var_dump($newLeadReplyDays[0]);

            var_dump($daysList[0]);
            var_dump($oldLeadOldDays[0]);
            die(); */
            $cacheTotalOrder=md5($this->partner_id.implode(",",$sales).$ending_date.$starting_date);
            $cacheTotalOrderFile=cache('TotalOrder'.$cacheTotalOrder);
            if(isset($cacheTotalOrderFile) && $cacheTotalOrderFile!=""){
                $totalOrder=$cacheTotalOrderFile;
            }else{
                $totalOrder=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalInvoice')->sum('number');
                Cache::put('TotalOrder'.$cacheTotalOrder, $totalOrder, now()->addMinutes(20));
            } 


            $cacheTotalRevenue=md5($this->partner_id.implode(",",$sales).$ending_date.$starting_date);
            $cacheTotalRevenueFile=cache('TotalRevenus1'.$cacheTotalRevenue);
            if(isset($cacheTotalRevenueFile) && $cacheTotalRevenueFile!=""){
                $totalRevenus=$cacheTotalRevenueFile;
            }else{
                $totalRevenus=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalInvoice')->sum('summary_invoice');
                Cache::put('TotalRevenus1'.$cacheTotalRevenue, $totalRevenus, now()->addMinutes(20));
            } 

            $cacheTotalInvoice=md5($this->partner_id.implode(",",$sales).$ending_date.$starting_date);
            $cacheTotalInvoiceFile=cache('TotalInvoice'.$cacheTotalInvoice);
            if(isset($cacheTotalInvoiceFile) && $cacheTotalInvoiceFile!=""){
                $totalInvoice=$cacheTotalInvoiceFile;
            }else{
                $totalInvoice=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalInvoice')->sum('number');
                Cache::put('TotalInvoice'.$cacheTotalInvoice, $totalInvoice, now()->addMinutes(20));
            } 


            $list_client_group_order = array();
            foreach($listGroupCustomer as $listData){
                $cacheKey=md5($this->partner_id.$listData["id"].implode(",",$sales).$ending_date.$starting_date);
                $leadCache=cache('leadOrderCount5'.$cacheKey);
                if(isset($leadCache) && $leadCache!=""){
                    $orderCount=$leadCache;
                }else{ //select("leads.id")->
                    $orderCount=Lead::join('customer_order','customer_order.lead_id','=','leads.id')->where('customer_order.last_order','>=',$starting_date)->where('customer_order.last_order','<=',$ending_date)->where('leads.created_at','<',$starting_date)->whereIn('leads.sales_person_id',$sales)->where('leads.partner_id', $this->partner_id)->where('leads.status', $listData["id"])->count('leads.id');
                    Cache::put('leadOrderCount5'.$cacheKey, $orderCount, now()->addMinutes(20));
                } 
                $list_client_group_order[] =
                    [
                        'id' =>$listData["id"],
                        'name' =>$listData["title"],
                        'order'=>$orderCount
                    ];
            }
            /*
            $list_client_group_comnunicate = array();
            foreach($listGroupCustomer as $listData){
                $cacheKey=md5($this->partner_id.$listData["id"].implode(",",$sales).$ending_date.$starting_date);
                $leadCache=cache('leadTuongTac5'.$cacheKey);
                if(isset($leadCache) && $leadCache!=""){
                    $orderCount=$leadCache;
                }else{ //select("leads.id")->
                    $tuongtacCount=Lead::join('tasks','tasks.lead_id','=','leads.id')->where('tasks.task_start','>=',$starting_date)->where('tasks.task_start','<=',$ending_date)->where('leads.created_at','<',$starting_date)->whereIn('leads.sales_person_id',$sales)->where('leads.partner_id', $this->partner_id)->where('leads.status', $listData["id"])->count('leads.id');
                    Cache::put('leadTuongTac5'.$cacheKey, $tuongtacCount, now()->addMinutes(20));
                } 
                $list_client_group_comnunicate[] =
                    [
                        'id' =>$listData["id"],
                        'name' =>$listData["title"],
                        'totalComnuicate'=>$tuongtacCount
                    ];
            } */

            //Thoi gian xu lý lead

            $cacheTotalLeadBung=cache('TotalLeadBung'.$cacheCode);
            if(isset($cacheTotalLeadBung) && $cacheTotalLeadBung!=""){
                $totalLeadBung=$cacheTotalLeadBung;
            }else{
                $totalLeadBung=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalLeadBung')->sum('number');
                Cache::put('TotalLeadBung'.$cacheCode, $totalLeadBung, now()->addMinutes(120));
            } 

            $cacheTotalClientBung=cache('TotalClientBung'.$cacheCode);
            if(isset($cacheTotalClientBung) && $cacheTotalClientBung!=""){
                $totalClientBung=$cacheTotalClientBung;
            }else{
                $totalClientBung=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalClientBung')->sum('number');
                Cache::put('TotalClientBung'.$cacheCode, $totalClientBung, now()->addMinutes(120));
            } 

            $cacheTimeAcceptLeadB=cache('TimeAcceptLead'.$cacheCode);
            if(isset($cacheTimeAcceptLeadB) && $cacheTimeAcceptLeadB!=""){
                $timeAcceptLead=$cacheTimeAcceptLeadB;
            }else{
                $timeAcceptLead=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','timeAcceptLead')->sum('number');
                Cache::put('TimeAcceptLead'.$cacheCode, $timeAcceptLead, now()->addMinutes(120));
            }
            
            $cacheTimeProcssLead=cache('TimeProcessLead'.$cacheCode);
            if(isset($cacheTimeProcssLead) && $cacheTimeProcssLead!=""){
                $timeProcessLead=$cacheTimeProcssLead;
            }else{
                $timeProcessLead=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','timeProcessLead')->sum('number');
                Cache::put('TimeProcessLead'.$cacheCode, $timeProcessLead, now()->addMinutes(120));
            }
             /* Lead to client */
             $cacheleadToClient=cache('leadToClient1'.$cacheCode);
            if(isset($cacheleadToClient) && $cacheleadToClient!=""){
                $leadToClient=$cacheleadToClient;
            }else{
                $leadToClient = Lead::select('id')
                ->whereIn('sales_person_id',$sales)
                ->where('partner_id', $this->partner_id)
                ->whereNotNull('customer_id')
                ->where(function ($query3) use($starting_date, $ending_date) {
                        if($starting_date!=""){
                            $query3->where('created_at','>=',$starting_date);
                        }
                        if($ending_date!=""){
                            $query3->where('created_at','<=',$ending_date);
                        }
                })->count();
                Cache::put('leadToClient1'.$cacheCode, $leadToClient, now()->addMinutes(120));
            }
            /* new lead group on day */
            
            $list_lead_group_new = array();
            foreach($listGroupLead as $listData){
                $cacheKey=md5($this->partner_id.$listData["id"].implode(",",$sales).$ending_date.$starting_date);
                $leadCache=cache('leadGroupNewLeadCache1'.$cacheKey);
                if(isset($leadCache) && $leadCache!=""){
                    $totalLeadCare=$leadCache;
                }else{
                    $totalLeadCare=Lead::whereIn('sales_person_id',$sales)
                    ->where('created_at','>=',$starting_date)
                    ->where('created_at','<=',$ending_date)
                    ->where('leads.status', $listData["id"])
                    ->where('group_id',45)->count();
                    Cache::put('leadGroupNewLeadCache1'.$cacheKey, $totalLeadCare, now()->addMinutes(20));
                } 
                $list_lead_group_new[] =
                    [
                        'id' =>$listData["id"],
                        'name' =>$listData["title"],
                        'color' =>$listData["color_bg"],
                        'leads'=>$totalLeadCare
                    ];
            }
            $listLeadTitle=array();
            /*
            $cacheGroupClient=md5($this->partner_id.implode(",",$sales).$ending_date.$starting_date);
            $cacheGroupClientFile=cache('GroupClient'.$cacheGroupClient);
            if(isset($cacheGroupClientFile) && $cacheGroupClientFile!=""){
                $groupClient=$cacheGroupClientFile;
            }else{
                $totalInvoice=ReportStaff::whereIn('user_id',$sales)->where('date_create','>=', $starting_date)->where('date_create','<=',$ending_date)->where('type','totalInvoice')->sum('number');
                Cache::put('TotalInvoice'.$cacheTotalInvoice, $totalInvoice, now()->addMinutes(20));
            } */
            
            //Call
            //$callTotal= $this->callRepository->getAll()->where('user_id',$id)->count();
            //$callTotalMissing= $this->callRepository->getAll()->where('duration','<=', 3)->where('user_id',$id)->count();
            //$callTotalSuccess= $this->callRepository->getAll()->where('duration','>', 3)->where('user_id',$id)->count();
            //End call
            //$totalLead = $this->leadRepository->getAll()->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->count(); //New lead
            
            //$opportunity_new = $this->leadRepository->getAll()->where('status', 0)->where('partner_id', $this->partner_id)->where('sales_person_id',$id)->count(); //New lead
            //$opportunity_negotiation = $this->leadRepository->getAll()->where('sales_person_id',$id)->whereIn('status', array(2,3,4,5))->count();
           // $opportunity_won = $this->leadRepository->getAll()->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->whereIn('status', array(6,7))->count();
            //$opportunity_loss = $this->leadRepository->getAll()->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->whereIn('status', array(8,9,10,11))->count();
           // $opportunity_expired = $this->leadRepository->getAll()->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->where('status', 12)->count();
            // Tỷ lệ chuyển đổi
           // $ctrcall=0;
            //$ctrleadregister=0;
            //if($callTotalSuccess>0){
              //  $ctrcall=round(($opportunity_won/$callTotalSuccess)*100,2);

            //}
            //End ty lệ
            // Tỷ lệ chuyển đổi
            if($totalLead>0){
            //$ctrleadregister=round(($opportunity_won/$totalLead)*100,2);
            }
            //End ty lệ
            $staff=0;
            if($this->userRepository->getAllUserArray($id)){
         //       $staff=$this->userRepository->getAllUserArray($id)->count();

            }
            return view('user.staff.dashboard', compact('totalLead', 'totalCustomer','totalLeadOld', 'totalLeadReply','totalOrder','totalRevenus','list_lead_group','list_customer_group', 'staffDetail', 'groupLeadTitle', 'groupClientTitle', 'salesList', 'listGroupLead', 'daterange', 'totalLeadNotCare', 'totalAssign', 'salesSearch', 'list_client_group_order', 'totalLeadBung', 'totalClientBung', 'timeAcceptLead', 'timeProcessLead', 'oldLeadOldDays', 'newLeadReplyDays', 'daysList', 'leadToClient', 'date_select', 'list_lead_group_new', 'totalReportTags'));

        }
    }
    public function dashboard_bk($id)
    {
        if (Sentinel::check()) {
            $userData=$this->userRepository->getUser();
            $this->partner_id=$userData->partner_id;
            $staffDetail = $this->userRepository->find($id);
            $customers = $this->companyRepository->getAll()->count();
            $contracts = $this->contractRepository->getAll()->count();
            $opportunities = $this->opportunityRepository->getAll()->count();
          
            $products = Product::where('partner_id', $this->partner_id)->count();
            //Check tồng lead assign ->join('call_action_status','call_action_status.id','=')
            $leadAssign = LeadAssignStatus::selectRaw('DATE_FORMAT(lead_assign_status.date_create, "%Y-%m-%d") as date_assign, (select count(id) from lead_assign_status where status=1 and user_id='.$id.' and date_create>=date_assign and date_create<(date_assign+INTERVAL 1 DAY)) as leadAccept, (select count(id) from lead_assign_status where status=0 and user_id='.$id.' and date_create>=date_assign and date_create<(date_assign+INTERVAL 1 DAY)) as leadNoAccept')->join('leads','leads.id','=','lead_assign_status.lead_id')
            ->where('leads.partner_id',$this->partner_id)
            ->where('lead_assign_status.user_id','=',$id)->groupBy('date_assign')->get();
            //end
            $opportunity_leads = array();
            $stages = array();
            for($i=11;$i>=0;$i--)
            {
                $opportunity_leads[] =
                    [
                        'month' =>Carbon::now()->subMonth($i)->format('M'),
                        'year' =>Carbon::now()->subMonth($i)->format('Y'),
                        'opportunity'=>$this->leadRepository->getAll()->where('created_at','LIKE',
                                Carbon::now()->subMonth($i)->format('Y-m').'%')->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->count(),
                        'leads'=>$this->leadRepository->getAll()->where('created_at','LIKE',
                              Carbon::now()->subMonth($i)->format('Y-m').'%')->whereIn('status',array(6,7))->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->count()
                    ];
            }
            //Call
            $callTotal= $this->callRepository->getAll()->where('user_id',$id)->count();
            $callTotalMissing= $this->callRepository->getAll()->where('duration','<=', 3)->where('user_id',$id)->count();
            $callTotalSuccess= $this->callRepository->getAll()->where('duration','>', 3)->where('user_id',$id)->count();
            //End call
            $totalLead = $this->leadRepository->getAll()->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->count(); //New lead
            
            $opportunity_new = $this->leadRepository->getAll()->where('status', 0)->where('partner_id', $this->partner_id)->where('sales_person_id',$id)->count(); //New lead
            $opportunity_negotiation = $this->leadRepository->getAll()->where('sales_person_id',$id)->whereIn('status', array(2,3,4,5))->count();
            $opportunity_won = $this->leadRepository->getAll()->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->whereIn('status', array(6,7))->count();
            $opportunity_loss = $this->leadRepository->getAll()->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->whereIn('status', array(8,9,10,11))->count();
            $opportunity_expired = $this->leadRepository->getAll()->where('sales_person_id',$id)->where('partner_id', $this->partner_id)->where('status', 12)->count();
            // Tỷ lệ chuyển đổi
            $ctrcall=0;
            $ctrleadregister=0;
            if($callTotalSuccess>0){
                $ctrcall=round(($opportunity_won/$callTotalSuccess)*100,2);

            }
            //End ty lệ
            // Tỷ lệ chuyển đổi
            if($totalLead>0){
            $ctrleadregister=round(($opportunity_won/$totalLead)*100,2);
            }
            //End ty lệ
            $staff=0;
            if($this->userRepository->getAllUserArray($id)){
                $staff=$this->userRepository->getAllUserArray($id)->count();

            }

            return view('user.staff.dashboard', compact('customers', 'contracts', 'opportunities','products', 'staff','opportunity_leads','stages','opportunity_new','opportunity_negotiation','opportunity_won','opportunity_loss','opportunity_expired', 'callTotal', 'callTotalMissing', 'callTotalSuccess', 'totalLead', 'ctrcall','ctrleadregister', 'staffDetail', 'leadAssign'));
        }
    }
    public function taskuser($userid){
        $logData=Logs::where('user_id',$userid)->orderBy("id", "desc")->paginate(100)->appends(request()->query());
        $logshow=$logData->map( function ( $logs){
                return [
                    "id" => $logs->id,
                    "description" => $logs->logs,
                    "created_at" => date("d/m/Y H:i:s",strtotime($logs->created_at)),
                ];
            }
        );
        return $logshow;
    }

    private function generateParams()
    {
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;

        $userStaffGroup = Groupuser::where('partner_id',$partner_id)->get();
        $branch=Branch::where('partner_id',$partner_id)->get()->map( function ( $branch) {
            return [
                'title' => $branch->name,
                'value' => $branch->id,
            ];
        } )->pluck( 'title', 'value' )
        ->prepend(trans('branch.branch_select'), ''); 
        //view()->share('staffs', $staffs);
        view()->share( 'userStaffGroup', $userStaffGroup );
        view()->share( 'branch', $branch );

        
    }
    public function user_list(Request $request){
        $branch=$request->branch_id;
        $userData=$this->userRepository->getUser();
        $partner_id=$userData->partner_id;
        $staffsQuery=array();
        $staffs=array();
        if($branch!=""){
            $staffsQuery = User::select('users.*')
            ->where('partner_id',$partner_id)
            ->where('branch_id',$branch)
            ->groupBy('users.id')
            ->orderBy('users.id', 'DESC')->get();
            $staffs=$staffsQuery->map( function ( $staff){
                    return [
                        "id" => $staff->id,
                        "full_name" => $staff->first_name." ".$staff->last_name,
                    ];
                }
            );
        }
           
        return $staffs;
    }
}
