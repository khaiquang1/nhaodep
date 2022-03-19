<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('company_name')->nullable()->change();
            $table->integer('customer_id')->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->integer('state_id')->nullable()->change();
            $table->integer('city_id')->nullable()->change();
            $table->integer('sales_person_id')->nullable()->change();
            $table->string('title')->nullable()->change();
            $table->string('mobile')->nullable()->change();
            $table->string('fax')->nullable()->change();
            $table->string('tags')->nullable()->change();
            $table->string('priority')->nullable()->change();
            $table->text('internal_notes')->nullable()->change();
            $table->integer('assigned_partner_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function(Blueprint $table)
        {
            $table->dropColumn(array('company_name','customer_id','address','state_id','city_id','sales_person_id','title','mobile','fax','tags','priority','internal_notes','assigned_partner_id'));
        });
    }
}
