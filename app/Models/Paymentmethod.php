<?php
    
    namespace App\Models;
    
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;
    
    class Paymentmethod extends Model
    {
        use SoftDeletes;
        
        protected $dates = ['deleted_at'];
        protected $guarded  = array('id');
        protected $table = 'payment_method';
        
    }
