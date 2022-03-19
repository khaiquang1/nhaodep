<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;


class EmailCheck extends Model
{
    use RevisionableTrait;

    protected $guarded = ['id'];
    protected $table = 'email_check';
    protected $dates = ['deleted_at'];

    public function date_format()
    {
        return "Y-m-d H:i:s";
        
    }
}
