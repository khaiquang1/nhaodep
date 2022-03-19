<?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    
    class UserControlPage extends Model
    {
        protected $dates = ['deleted_at'];
        protected $guarded  = array('id');
        protected $table = 'user_control_page';
        
    }
