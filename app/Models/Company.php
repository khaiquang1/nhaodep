<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;

class Company extends Model
{
    use SoftDeletes,RevisionableTrait;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'companies';

	protected $appends = ['avatar'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contactPerson()
    {
        return $this->belongsTo(User::class,'main_contact_person');
    }

    public function salesTeam()
    {
        return $this->belongsTo(Salesteam::class, 'sales_team_id');
    }

    public function cities()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
    public function customerCompany()
    {
        return $this->hasMany(Customer::class, 'company_id');
    }
    public function opportunityCompany()
    {
        return $this->hasMany(Opportunity::class, 'company_name');
    }
	public function getAvatarAttribute() {
		$val = $this->attributes['company_avatar'];
		if (strlen($val) > 1) {
			$val = asset( 'uploads/company' ) . '/' . $val;
		}
		return $val;
	}


}
