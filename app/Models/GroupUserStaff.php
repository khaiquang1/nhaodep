<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GroupUserStaff extends Model  {

    protected $table = 'group_user_staff';
    protected $guarded  = ['id'];

    public function date_format()
    {
    }

}
