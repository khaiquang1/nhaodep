<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;

class Contract extends Model
{
    use SoftDeletes,RevisionableTrait;

    protected $dates = ['deleted_at'];
    protected $guarded  = array('id');
    protected $table = 'contracts';

    public function date_format()
    {
        return config('settings.date_format');
    }

    public function setStartDateAttribute($start_date)
    {
       $this->attributes['start_date'] = Carbon::createFromFormat($this->date_format(),$start_date)->format('Y-m-d');
    }

    public function getStartDateAttribute($start_date)
    {
        if ($start_date == "0000-00-00" || $start_date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($start_date));
        }
    }

    public function setEndDateAttribute($end_date)
    {
        $this->attributes['end_date'] = Carbon::createFromFormat($this->date_format(),$end_date)->format('Y-m-d');
    }

    public function getEndDateAttribute($end_date)
    {
        if ($end_date == "0000-00-00" || $end_date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($end_date));
        }
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function company() {
        return $this->belongsTo(Company::class);
    }
    public function responsible()
    {
        return $this->belongsTo(User::class, 'resp_staff_id');
    }
}
