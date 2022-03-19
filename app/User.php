<?php

namespace App;

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

class User extends EloquentUser implements AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract,
    JWTSubject
{
    use Authenticatable, Authorizable, CanResetPassword,
	    Billable, SoftDeletes, Notifiable;

    protected $dates = ['deleted_at'];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes to be fillable from the model.
     *
     * A dirty hack to allow fields to be fillable by calling empty fillable array
     *
     * @var array
     */
    protected $fillable = [];

    protected $guarded = ['id'];

    protected $hidden = ['password'];

    protected $appends = ['full_name', 'avatar'];

    public function customer()
    {
        return $this->hasOne('App\Models\Customer');
    }

    public function staff()
    {
        return $this->hasOne('App\Models\Staff');
    }

    public function salesTeams()
    {
        return $this->hasMany(Models\Salesteam::class);
    }

    public function opportunities()
    {
        return $this->hasMany(Models\Opportunity::class);
    }

    public function meetings()
    {
        return $this->hasMany(Models\Meeting::class);
    }

    public function calls()
    {
        return $this->hasMany(Models\Call::class);
    }

    public function qtemplates()
    {
        return $this->hasMany(Models\Qtemplate::class);
    }

    public function contracts()
    {
        return $this->hasMany(Models\Contract::class);
    }

    public function leads()
    {
        return $this->hasMany(Models\Lead::class);
    }

	public function authorized($permission = null)
	{
		return array_key_exists($permission, $this->permissions);
	}

    public function getFullNameAttribute()
    {
        return str_limit($this->first_name . ' ' . $this->last_name, 30);
    }

    public function user()
    {
        return $this->belongsTo(Models\User::class);
    }

    public function users()
    {
        return $this->hasMany(Models\User::class);
    }


    public function parent()
    {
        return $this->belongsTo(Models\User::class, 'user_id');
    }

    public function companies()
    {
        return $this->hasMany(Models\Company::class);
    }

    public function allChildCustomers()
    {
        return $this->hasManyThrough(Customer::class, User::class, 'user_id', 'belong_user_id');
    }

    public function products()
    {
        return $this->hasMany(Models\Product::class);
    }

    public function categories()
    {
        return $this->hasMany(Models\Category::class);
    }

    public function invoiceReceivePayments()
    {
        return $this->hasMany(Models\InvoiceReceivePayment::class);
    }

    public function getAvatarAttribute()
    {
        $val = $this->attributes['user_avatar'];
        if (empty($val))
            return asset('uploads/avatar') . '/user.png';

        $val = asset('uploads/avatar') . '/' . $val;

        return $val;
    }

    public function emailTemplates()
    {
        return $this->hasMany(Models\EmailTemplate::class);
    }

    public function notifications()
    {
        return $this->hasMany(Models\Notification::class);
    }

    public function emails()
    {
        return $this->hasMany(Models\Email::class, 'to');
    }

    public function logins()
    {
        return $this->hasMany(Models\UserLogin::class);
    }

    public function invite()
    {
        return $this->hasMany(Models\InviteUser::class);
    }
    public function staffSalesTeam()
    {
        return $this->hasMany(Models\Salesteam::class,'team_leader');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getUserId();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

}
