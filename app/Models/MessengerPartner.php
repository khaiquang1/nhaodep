<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessengerPartner extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'messenger_partner';

    public function sender()
    {
        return $this->belongsTo(User::class, 'from');
    }

}
