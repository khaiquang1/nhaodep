<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;

class NextTimeFollow extends Model
{
    protected $table = 'next_time_follow_status';
    public $timestamps = false;
    public static function addUpdate($lead_id, $next_time_follow, $status=0){
        if($lead_id){
            $nextTimeFollow=NextTimeFollow::where('lead_id',$lead_id)->first();
            $data=array("lead_id"=>$lead_id, "next_time_follow"=>$next_time_follow, "status"=>$status, "count_push"=>0);
            if($nextTimeFollow){
                return NextTimeFollow::where('lead_id',$lead_id)->update($data);
            }else{
                return NextTimeFollow::insert($data);
            }
        }
        return '';

    }
}