<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Settings;

class InviteUser extends Model {

	protected $table = 'invite_user';
	protected $fillable = ['code', 'email','user_id'];

	public function parent()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function date_format()
	{
        return Settings::get('date_format').' '.Settings::get('time_format');
	}
	public function setClaimedAtAttribute($claimed_at)
	{
		$this->attributes['claimed_at'] = Carbon::createFromFormat($this->date_format(),$claimed_at)->format('Y-m-d H:s');
	}

	public function getClaimedAtAttribute($claimed_at)
	{
		if ($claimed_at == "0000-00-00" || $claimed_at == "") {
			return "";
		} else {
			return date($this->date_format(), strtotime($claimed_at));
		}
	}
}