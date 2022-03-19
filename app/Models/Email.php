<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Email extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $guarded  = array('id');
    protected $table = 'emails';

    public function sender()
    {
        return $this->belongsTo(User::class, 'from');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'to');
    }

    public function customer()
    {
        return $this->belongsTo(Company::class, 'assign_customer_id');
    }
}
