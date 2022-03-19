<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;

class Product extends Model
{
    use SoftDeletes,RevisionableTrait;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'products';

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function invoiceProducts()
    {
        return $this->belongsToMany(Product::class, 'invoices_products')->withPivot('quantity', 'price');
    }

    public function quotationProducts()
    {
        return $this->belongsToMany(Product::class, 'quotations_products')->withPivot('quantity', 'price');
    }
    public function qTemplateProducts()
    {
        return $this->belongsToMany(Product::class, 'qtemplate_products')->withPivot('quantity','price');
    }

    public function salesOrderProducts()
    {
        return $this->belongsToMany(Product::class, 'sales_order_products')->withPivot('quantity', 'price');
    }

}
