<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GroupLead extends Model  {

    protected $table = 'group_client';
    protected $guarded  = ['id'];

    public function date_format()
    {
        return config('settings.date_format').' H:i';
    }

}