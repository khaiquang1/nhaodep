<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;
use App\Models\SalesteamMember;


class Salesteam extends Model
{
    use SoftDeletes,RevisionableTrait;

    protected $guarded = ['id'];
    protected $table = 'sales_teams';

    protected $casts = [
        'leads' => 'boolean',
        'quotations' => 'boolean',
        'opportunities' => 'boolean',
        'team_members' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function members(){
        return $this->belongsToMany(User::class,'sales_team_members');
    }

    public function teamLeader()
    {
        return $this->belongsTo(User::class, 'team_leader');
    }
    public function actualInvoice()
    {
        return $this->hasMany(Invoice::class,'sales_team_id')->where('invoice_date','LIKE', date('Y-m').'%');
    }
    public function agentSalesteam()
    {
        return $this->hasMany(Customer::class, 'sales_team_id');
    }

    public function opportunitySalesteam()
    {
        return $this->hasMany(Opportunity::class,'sales_team_id');
    }

    public function quotationSalesteam()
    {
        return $this->hasMany(Quotation::class,'sales_team_id');
    }

    public function salesorderSalesteam()
    {
        return $this->hasMany(Saleorder::class,'sales_team_id');
    }

}
