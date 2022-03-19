<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cookie extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'cookie';

    public function sender()
    {
        return $this->belongsTo(User::class, 'from');
    }

}
