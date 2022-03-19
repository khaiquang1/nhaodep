<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceReceivePayment extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'invoice_receive_payments';

    public function date_format()
    {
        return "Y-m-d";
    }

    public function setPaymentDateAttribute($payment_date)
    {
        
    }

    public function getPaymentDateAttribute($payment_date)
    {
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class,'customer_id');
    }
}
