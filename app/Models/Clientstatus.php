<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Clientstatus extends Model  {

    protected $table = 'call_action_status';
    protected $guarded  = ['id'];

    public function date_format()
    {
        return config('settings.date_format').' H:i';
    }

}