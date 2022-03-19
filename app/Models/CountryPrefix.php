<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;


class CountryPrefix extends Model
{
    use SoftDeletes,RevisionableTrait;

    protected $guarded = ['id'];
    protected $table = 'country_prefix';
    protected $dates = ['deleted_at'];

    public function date_format()
    {
        return config('settings.date_format');
    }

}
