<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;


class LeadTemp extends Model
{

    protected $guarded = ['id'];
    protected $table = 'lead_import';
}
