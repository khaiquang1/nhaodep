<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TaskReport extends Model  {

    protected $table = 'task_reports';
    protected $guarded  = ['id'];

    public function date_format()
    {
        return config('settings.date_format').' H:i';
    }

}