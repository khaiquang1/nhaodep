<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class GroupClientStatus extends Model  {

    protected $table = 'group_client_status';
    protected $guarded  = ['id'];

    public function date_format()
    {
    }

}
