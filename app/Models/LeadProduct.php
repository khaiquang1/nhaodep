<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;


class LeadProduct extends Model
{
    protected $guarded = ['product_id','lead_id'];
    protected $table = 'lead_products';
    protected $dates = ['deleted_at'];

    public function date_format()
    {
        return "Y-m-d H:i:s";
        
    }
}
