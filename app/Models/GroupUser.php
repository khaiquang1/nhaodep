<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sentinel;
use App\Models\User;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Cartalyst\Sentinel\Users\EloquentUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class GroupUser extends EloquentUser implements AuthenticatableContract,
AuthorizableContract,
CanResetPasswordContract{
    use Authenticatable, Authorizable, CanResetPassword,
        Billable, SoftDeletes, Notifiable;
        
    protected $table = 'group_user';
    protected $guarded  = ['id'];

    public function date_format()
    {
        return config('settings.date_format').' H:i';
    }
    public static function getGroup(){
        $group_data = GroupUser::select('group_user.permissions')->join('users','users.group_id', '=', 'group_user.id')->where('users.id',Sentinel::getUser()->id)->groupBy('users.id')->first();
        return  $group_data;
    }

    public static function getGroupPartner(){
        $group_data = GroupUser::select('group_user.permissions')->join('users','users.group_id', '=', 'group_user.id')->where('users.id',Sentinel::getUser()->id)->groupBy('users.id')->first();
        return  $group_data;
    }
    
}