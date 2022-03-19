<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;


class Call extends Model
{
    use SoftDeletes,RevisionableTrait;

    protected $guarded = ['id'];
    protected $table = 'calls';
    protected $dates = ['deleted_at'];

    public function date_format()
    {
        return "Y-m-d H:i:s";
        
    }
    /*
    public function setDateAttribute($date)
    {
        $this->attributes['date'] = Carbon::createFromFormat($this->date_format(),$date)->format('Y-m-d');
    }
    */
    public function getDateAttribute($date)
    {
        if ($date == "0000-00-00" || $date == "") {
            return "";
        } else {
            return date($this->date_format(), strtotime($date));
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function resp_staff()
    {
        return $this->belongsTo(User::class, 'resp_staff_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'resp_staff_id');
    }

    public function opportunity()
    {
        return $this->morphedByMany(Opportunity::class, 'callables');
    }

    public function lead()
    {
        return $this->morphedByMany(Lead::class, 'callables');
    }

}
