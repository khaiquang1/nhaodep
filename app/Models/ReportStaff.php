<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;


class ReportStaff extends Model
{
    protected $guarded = ['id'];
    protected $table = 'report_staff';

}
