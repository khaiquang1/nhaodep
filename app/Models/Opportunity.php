<?php

namespace App\Models;

use App\Scopes\OpportunityArchiveScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;
use App\Scopes\OpportunityArchiveTrait;

class Opportunity extends Model
{
    use SoftDeletes, CallableTrait, MeetableTrait, RevisionableTrait, OpportunityArchiveTrait;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'opportunities';
    protected $appends = ['next_action_date','expected_closing_date'];

    public function date_format()
    {
        return ('Y-m-d H:i:s');
    }

    /*
    public function setExpectedClosingAttribute($expected_closing)
    {
        $this->attributes['expected_closing'] = Carbon::createFromFormat($this->date_format(),$expected_closing)->format('Y-m-d');
    } */

    public function getExpectedClosingDateAttribute()
    {
        if ('0000-00-00' == $this->expected_closing || '' == $this->expected_closing) {
            return '';
        } else {
            return date($this->date_format(), strtotime($this->expected_closing));
        }
    }
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
    public function salesTeam()
    {
        return $this->belongsTo(Salesteam::class, 'sales_team_id');
    }

    public function salesPerson()
    {
        return $this->belongsTo(User::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function companies(){
        return $this->belongsTo(Company::class,'company_name');
    }

    public function staffs(){
        return $this->belongsTo(User::class,'salesteam');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new OpportunityArchiveScope);
    }
}
