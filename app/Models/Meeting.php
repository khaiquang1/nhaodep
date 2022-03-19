<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;

class Meeting extends Model
{
    use SoftDeletes,RevisionableTrait;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'meetings';
    protected $appends = ['meeting_starting_date','meeting_ending_date'];

    public function date_time_format()
    {
        return config('settings.date_format').' H:i';
    }

    public function setStartingDateAttribute($starting_date)
    {
        if ($starting_date) {
            $this->attributes['starting_date'] =  Carbon::createFromFormat($this->date_time_format(),$starting_date)->format('Y-m-d H:i');
        } else {
            $this->attributes['starting_date'] = '';
        }
    }

    public function getMeetingStartingDateAttribute()
    {
        if ('0000-00-00 00:00' == $this->starting_date || '' == $this->starting_date) {
            return '';
        } else {
            return date($this->date_time_format(), strtotime($this->starting_date));
        }
    }

    public function setEndingDateAttribute($ending_date)
    {
        if ($ending_date) {
            $this->attributes['ending_date'] = Carbon::createFromFormat($this->date_time_format(),$ending_date)->format('Y-m-d H:i');
        } else {
            $this->attributes['ending_date'] = '';
        }
    }

    public function getMeetingEndingDateAttribute()
    {
        if ('0000-00-00 00:00' == $this->ending_date || '' == $this->ending_date) {
            return '';
        } else {
            return date($this->date_time_format(), strtotime($this->ending_date));
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }
}
