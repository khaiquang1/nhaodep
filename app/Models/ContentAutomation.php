<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use \Venturecraft\Revisionable\RevisionableTrait;


class ContentAutomation extends Model
{

    protected $guarded = ['id'];
    protected $table = 'content_automation';
    protected $dates = ['deleted_at'];

}
