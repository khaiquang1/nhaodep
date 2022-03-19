<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrmSetting extends Model
{
    protected $table = 'hrm_settings';
    protected $fillable =[
        "checkin", "checkout"
    ];
}
