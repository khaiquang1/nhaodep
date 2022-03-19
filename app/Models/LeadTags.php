<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadTags extends Model
{

    protected $dates = ['deleted_at'];
    protected $guarded  = array('id');
    protected $table = 'lead_tags';

}
