<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Projects extends Model  {

    protected $table = 'projects';
    protected $guarded  = ['id'];

    public function date_format()
    {
        return config('settings.date_format');
    }

    public function user_ids(){
        return $this->belongsTo(User::class,'user_id');
    }
}
