<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPermission extends Model
{
    protected $dates = ['deleted_at'];
    protected $guarded  = array('id');
    protected $table = 'user_permission';

}
