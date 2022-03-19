<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{ 
    protected $table = 'attendances';
    protected $fillable =[
        "date", "employee_id", "user_id", "person_id",
        "checkin", "checkout", "status", "note"
    ];
}
