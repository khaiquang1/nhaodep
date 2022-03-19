<?php

namespace App\Models;

use App\Scopes\SalesOrderScope;
use App\Scopes\SalesOrderTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;

class SaleorderProduct extends Model
{
    use SoftDeletes,RevisionableTrait, SalesOrderTrait;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'sales_order_products';
}
