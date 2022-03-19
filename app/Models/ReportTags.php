<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportTags extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded  = array('id');
    protected $table = 'report_tags';

}
