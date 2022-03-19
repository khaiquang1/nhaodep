<?php

namespace App\Models;

use App\Scopes\QuotationScope;
use App\Scopes\QuotationTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;

class Quotation extends Model
{
    use SoftDeletes,RevisionableTrait,QuotationTrait;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'quotations';
    protected $appends = ['expire_date','start_date'];

    public function date_format()
    {
        return ('Y-m-d H:i:s');
    }
    /*
    public function setDateAttribute($date)
    {
        $this->attributes['date'] = Carbon::createFromFormat($this->date_format(),$date)->format('Y-m-d');
    }
*/
    public function getStartDateAttribute()
    {
        if ('0000-00-00' == $this->date || '' == $this->date) {
            return '';
        } else {
            return date($this->date_format(), strtotime($this->date));
        }
    }
    /*
    public function setExpDateAttribute($exp_date)
    {
        $this->attributes['exp_date'] = Carbon::createFromFormat($this->date_format(),$exp_date)->format('Y-m-d');
    } */

    public function getExpireDateAttribute()
    {
        if ('0000-00-00' == $this->exp_date || '' == $this->exp_date) {
            return '';
        } else {
            return date($this->date_format(), strtotime($this->exp_date));
        }
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function salesPerson()
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function salesTeam()
    {
        return $this->belongsTo(Salesteam::class, 'sales_team_id');
    }

    public function quotationCustomer(){
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function quotationProducts()
    {
        return $this->belongsToMany(Product::class, 'quotations_products')->withPivot('quantity', 'price');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new QuotationScope);
    }
}
