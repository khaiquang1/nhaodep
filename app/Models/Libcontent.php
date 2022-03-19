<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use \Venturecraft\Revisionable\RevisionableTrait;


class Libcontent extends Model
{

    protected $guarded = ['id'];
    protected $table = 'lib_content';
    protected $dates = ['deleted_at'];

}
