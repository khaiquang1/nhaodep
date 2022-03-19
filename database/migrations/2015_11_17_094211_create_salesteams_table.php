<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesteamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_teams', function (Blueprint $table) {
            $table->increments('id');
            $table->string('salesteam');
            $table->integer('team_leader');
            $table->integer('invoice_target');
            $table->integer('invoice_forecast');
            $table->float('actual_invoice');
            $table->string('team_members');
            $table->boolean('leads')->default(0);
            $table->boolean('quotations')->default(0);
            $table->boolean('opportunities')->default(0);
            $table->text('notes');
            $table->integer('user_id');
            $table->timestamps();
            $table->softDeletes();
            $table->engine = 'InnoDB';
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sales_teams');
    }
}
