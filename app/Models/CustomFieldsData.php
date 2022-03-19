<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomFieldsData extends Model
{
    protected $guarded  = array('id');
    protected $table = 'customer_field_data';

}
