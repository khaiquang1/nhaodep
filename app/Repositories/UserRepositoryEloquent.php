<?php namespace App\Repositories;

use App\Helpers\Thumbnail;
use App\Models\User;
use App\Models\PartnerUser;
use App\Models\Salesteam;
use App\Models\SalesteamMember;
use Sentinel;
use JWTAuth;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserRepositoryEloquent extends BaseRepository implements UserRepository
{
    public function model()
    {
        return User::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function getUser()
    {
        if (Sentinel::getUser()) {
            return Sentinel::getUser();
        }else{
            $user = JWTAuth::parseToken()->authenticate();
            return $user;
        }

    }

    public function getStaff()
    {
        // $userId =Sentinel::getUser()->id
        if (Sentinel::getUser()) {
            $userId = Sentinel::getUser()->id;
        }else{
            $user = JWTAuth::parseToken()->authenticate();
            $userId = $user->id;
        }

        $user = User::find($userId);
        if (Sentinel::inRole('staff')) {
            return $user->users->filter(function ($user) {
                return $user->inRole('staff');
            })->add($user);
        } else if ($user->inRole('admin')) {
            $staffs = new Collection([]);
            $user
                ->users()
                ->with('users.users')
                ->get()
                ->each(function ($user) use ($staffs) {
                    foreach ($user->users as $u) {
                        $staffs->push($u);
                    }
                    //$staffs->push($user);
                });

            $staffs = $staffs->filter(function ($user) {
                return $user->inRole('staff');
            });
            return $staffs;
        }

    }

    public function getCustomers()
    {
        if (Sentinel::getUser()) {
            $userId = Sentinel::getUser()->id;
        }else{
            $user = JWTAuth::parseToken()->authenticate();
            $userId = $user->id;
        }
        $user = User::find($userId);
        if (Sentinel::inRole('staff')) {
            return $user->users->filter(function ($user) {
                return $user->inRole('customer');
            });
        } else if ($user->inRole('admin')) {
            $staffs = new Collection([]);
            $user
                ->users()
                ->with('users.users')
                ->get()
                ->each(function ($user) use ($staffs) {
                    foreach ($user->users as $u) {
                        $staffs->push($u);
                    }
                    //$staffs->push($user);
                });

            $staffs = $staffs->filter(function ($user) {
                return $user->inRole('customer');
            });
            return $staffs;
        }
    }
    //Get user Đệ Quy
    public function getParentStaff()
    {
        $staffs = new Collection([]);
        if (Sentinel::getUser()) {
            $userId = Sentinel::getUser()->id;
        }else{
            $user = JWTAuth::parseToken()->authenticate();
            $userId = $user->id;
        }
        
        if(Sentinel::inRole('admin')){
            $user = User::where('phone_verify','=',1)->get();
        }else{
            $listUserParent=$this->getAllStaffOfUser($userId);
             if($listUserParent){
                $user = User::whereIn('id',$listUserParent)->get();
             }
        }
       
        //$user->each(function ($user) use ($staffs) {
        if(isset($user) && $user!=""){
            foreach ($user as $u) {
                $staffs->push($u);
            }
        }
            
        //});
        /*
        $user = User::find($userId);
        if($user["user_id"]>0){
            $user->parent->users()
            ->with('users.users')
            ->get()
            ->each(function ($user) use ($staffs) {
                foreach ($user->users as $u) {
                    $staffs->push($u);
                }
            }); 
        } */
        //$staffs = $staffs->filter(function ($user) {
           // return $user->inRole('staff');
        //});
        return $staffs;
    }

    //Get staff id
    public function getAllStaffOfUser($user)
    {
        global $staff;
        if($user==0){
            if (Sentinel::getUser()) {
                $userId = Sentinel::getUser()->id;
            }else{
                $user = JWTAuth::parseToken()->authenticate();
                $userId = $user->id;
            }
            
        }else{
            $userId =$user;
        }
        $user = User::select('id','user_id')->where('user_id','=', $userId)->get();
        $staff[]=$userId;
        if(count($user)>0){
            foreach ($user as $u) {
                $staff[]=$u["id"];
                if($u["user_id"]>1 && $userId!=$u["user_id"]){
                    $this->getAllStaffOfUser($u["id"]);
                }
                //
            }
        }
        
        if(isset($staff) && count($staff)>0){
            $staff = array_unique ($staff);
        }
        return $staff;
    }
    //Get staff id
    public function getAllUserOfGroup($user)
    {
        global $staff;
        $userdata = User::select('id','user_id')->where('group_id','=', $user->group_id)->get();
        $staff[]=$user->id;
        if(count($userdata)>0){
            foreach ($userdata as $u) {
                $staff[]=$u["id"];
            }
        }
        if(isset($staff) && count($staff)>0){
            $staff = array_unique ($staff);
        }
        return $staff;
    }
    public function getAllUserOfPermission($user, $permission)
    {
        global $staff;
        $groupUser=$user->group_id;
        $brandUser=$user->branch_id;
        if($groupUser==44){
            $staff=$this->getAllUserOfTeam($user);
        }else{
            $userdata = User::select('users.id','users.user_id')
            ->join('user_permission','user_permission.user_id','=','users.id')
            ->where('users.partner_id','=', $user->partner_id)
            ->where(function ($query)  use ($groupUser, $brandUser){
                if($groupUser==42){
                    $query->where('users.branch_id',$brandUser);
                }
            })
            ->whereIn('user_permission.permission',$permission)->get();
            $staff[]=$user->id;
            if(count($userdata)>0){
                foreach ($userdata as $u) {
                    $staff[]=$u["id"];
                }
            }
            if(isset($staff) && count($staff)>0){
                $staff = array_unique ($staff);
            }
        }

       
        return $staff;
    }

    public function getAllUserOfTeam($user)
    {
        $groupUser=$user->group_id;
        $userdata = SalesteamMember::select('sales_team_members.*')->join('sales_teams','sales_teams.id','=','sales_team_members.salesteam_id')
        ->where('sales_teams.partner_id','=', $user->partner_id)
        ->where('sales_teams.team_leader','=',$user->id)
        ->get();
        $staffTeam[]=$user->id;
        if(count($userdata)>0){
            foreach ($userdata as $u) {
                $staffTeam[]=$u["user_id"];
            }
        }
       

        if(isset($staffTeam) && count($staffTeam)>0){
            $staffTeam = array_unique ($staffTeam);
        }
        return $staffTeam;
    }



    public function getAllUserOfPermissionPartner($partner_id, $permission)
    {
        global $staff;
        $userdata = User::select('users.id','users.user_id')->join('user_permission','user_permission.user_id','=','users.id')->where('users.partner_id','=', $partner_id)->whereIn('user_permission.permission',$permission)->get();
        $staff[]="";
        if(count($userdata)>0){
            foreach ($userdata as $u) {
                $staff[]=$u["id"];
            }
        }
        if(isset($staff) && count($staff)>0){
            $staff = array_unique ($staff);
        }
        return $staff;
    }


    public function getAllUserOfPermissionOfStaff($user, $permission)
    {
        $userdata = User::select('users.id','users.first_name','users.last_name')->join('user_permission','user_permission.user_id','=','users.id')->where('users.partner_id','=', $user->partner_id)->whereIn('user_permission.permission',$permission)->groupBy('users.id')->get();
        return $userdata;
    }

    public function getAllUserOfPermissionOfStaffOnline($user, $permission)
    {
        $userdata = User::select('users.id','users.first_name','users.last_name')->join('user_permission','user_permission.user_id','=','users.id')->where('users.partner_id','=', $user->partner_id)->where('users.received_lead','=',1)->whereIn('user_permission.permission',$permission)->groupBy('users.id')->get();
        return $userdata;
    }
    
    
    ////Get staff id
    public function getAllUserOnPartner($part_id)
    {
        $user = PartnerUser::where('partner_id','=', $part_id)->get();
        $staff=null;
        if(count($user)>0){
            foreach ($user as $u) {
                $staff[]=$u["user_id"];
            }
        }
        return $staff;
    }

    public function getParentCustomers()
    {
        $staffs = new Collection([]);
        /*
        $user = User::find(Sentinel::getUser()->id);
        $user
            ->parent->users()
            ->with('users.users')
            ->get()
            ->each(function ($user) use ($staffs) {
                foreach ($user->users as $u) {
                    $staffs->push($u);
                }
                // $staffs->push($user);
            });

        $staffs = $staffs->filter(function ($user) {
            return $user->inRole('customer');
        }); */
        //
        $userId=Sentinel::getUser()->id;
        if(Sentinel::inRole('admin')){
            $user = User::where('phone_verify','=',1);
        }else{
            $listUserParent=$this->getAllStaffOfUser($userId);
            $user = User::whereIn('phone_verify',$listUserParent);
        }
        $user->get()
        ->each(function ($user) use ($staffs) {
            foreach ($user->users as $u) {
                $staffs->push($u);
            }
        });
        $staffs = $staffs->filter(function ($user) {
            return $user->inRole('customer');
        });
        return $staffs;
    }

    public function getAll()
    {
        if (Sentinel::getUser()) {
            $userId =Sentinel::getUser()->id;
        }else{
            $user = JWTAuth::parseToken()->authenticate();
            $userId = $user->id;
        }
        $listUserParent=$this->getAllStaffOfUser($userId);
        $user = User::find($userId);
        $models = $this->model->whereHas('user', function ($q) use ($user, $listUserParent) {
            $q->where(function ($query) use ($user, $listUserParent) {
                $query
                    ->whereIn('user_id', $listUserParent);
            });
        });

        return $models;
    }

    public function getAllNew()
    {
        $models = $this->model;
        return $models;
    }

    public function getAllForCustomer()
    {
        $models = $this->model;

        return $models;
    }
    public function getUsersAndStaffs()
    {
        return $this->model->get()->filter(
            function ($user) {
                return ($user->inRole('admin') || $user->inRole('staff'));
            }
        );
    }

    public function uploadAvatar(UploadedFile $file)
    {
        $destinationPath = public_path() . '/uploads/avatar/';
        $extension = $file->getClientOriginalExtension() ?: 'png';
        $fileName = str_random(10) . '.' . $extension;
        return $file->move($destinationPath, $fileName);
    }
    public function generateThumbnail($file)
    {
        Thumbnail::generate_image_thumbnail(public_path().'/uploads/avatar/'.$file->getFileInfo()->getFilename(),
            public_path().'/uploads/avatar/'.'thumb_'.$file->getFileInfo()->getFilename());
    }

    public function create(array $data, $activate = false)
    {
        if (Sentinel::getUser()) {
            $userId = Sentinel::getUser()->id;
        }else{
            $user = JWTAuth::parseToken()->authenticate();
            $userId = $user->id;
        }
        $user = User::find($userId);
        $userNew = Sentinel::registerAndActivate($data, $activate);
        $user->users()->save($userNew);
        return $userNew;
    }

    public function assignRole(User $user, $roleName)
    {
        $role = Sentinel::getRoleRepository()->findByName($roleName);
        $role->users()->attach($user);
    }

    public function usersWithTrashed($email)
    {
        $users = $this->model->withTrashed()->where('email', $email)->get();

        return $users;
    }
    public function getAllUserArray($user='')
    {
        if($user==""){
            if (Sentinel::getUser()) {
                $userId =Sentinel::getUser()->id;
            }else{
                $user = JWTAuth::parseToken()->authenticate();
                $userId = $user->id;
            }
        }else{
            $userId = $user;
        }
        
        $listUserParent=$this->getAllStaffOfUser($userId);
        $user = null;
        if($listUserParent){
            $user = User::whereIn('id', $listUserParent);
        }
        return $user;
    }
    public function getUserListSearch($grouppermission, $userData){
        if($grouppermission!="" && $grouppermission!=null){
            switch ($grouppermission) {
                case $grouppermission->hasAccess(['leads.full']):
                    $listUser="";//$this->getAllUserOfGroup($userData);
                    break;
                case $grouppermission->hasAccess(['leads.view_other']):
                    $listUser=$this->getAllUserOfGroup($userData);
                    break;
                case $grouppermission->hasAccess(['leads.view_person']):
                    $listUser=array($userData->id);
                    break;
                default:
                    $listUser=array($userData->id);
                    break;
            }
        }else{
            $listUser=array($userData->id);
        }
        return $listUser;
    }

    public function getUserListMessenger($grouppermission, $userData){
        if($grouppermission!="" && $grouppermission!=null){
            switch ($grouppermission) {
                case $grouppermission->hasAccess(['messenger.full']):
                    $listUser=$this->getAllUserOfPermission($userData, array('messenger.view_other', 'messenger.view_person'));
                    break;
                case $grouppermission->hasAccess(['messenger.view_other']):
                    $listUser=$this->getAllUserOfPermission($userData, array('messenger.view_other', 'messenger.view_person'));
                    break;
                case $grouppermission->hasAccess(['messenger.view_person']):
                    $listUser=array($userData->id);
                    break;
                default:
                    $listUser=array($userData->id);
                    break;
            }
        }else{
            $listUser=array($userData->id);
        }
        return $listUser;
    }
    /*
    public function getUserListSearch($partner_id){
        $user_id=0;
        if($partner_id!="" && $partner_id!=null){
            //->orderByRaw('RAND()')
            $listUser=$this->getAllUserOfPermissionPartner($partner_id, array('messenger.view_other', 'messenger.view_person'));
            if($listUser){
                $user_id = $listUser[mt_rand(0, count($listUser) - 1)];
            }
        }
        return $user_id;
    } */
}