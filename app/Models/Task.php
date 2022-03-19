<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Task extends Model  {

    protected $table = 'tasks';
    protected $guarded  = ['id'];

    public function date_format()
    {
        return 'Y-m-d H:i';
    }

    public function user_ids(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function task_from_users(){
        return $this->belongsTo(User::class,'task_from_user');
    }

    public function setTaskDeadlineAttribute($task_deadline)
    {
       // $this->attributes['task_deadline'] = Carbon::createFromFormat($this->date_format(),$task_deadline)->format('Y-m-d H:i:s');
    }

    public function getTaskDeadlineAttribute()
    {
       
    }
}