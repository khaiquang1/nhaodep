<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    protected $table = 'check_in';
    protected $fillable =[
        "user_id", 'person_id', 'deviceID', 'placeID', 'fullname', "check_in", "check_out", "images_link", "date_check", "updated_at", "created_at", "type_person", "type_working"
    ];
}

