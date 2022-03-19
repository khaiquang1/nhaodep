<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomFields extends Model
{
    protected $guarded  = array('id');
    protected $table = 'custom_fields';

}
