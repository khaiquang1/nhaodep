<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQoutationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('quotations_number');
            $table->integer('customer_id');
            $table->integer('qtemplate_id');
            $table->date('date');
            $table->date('exp_date');
            $table->string('payment_term');
            $table->integer('sales_person_id');
            $table->integer('sales_team_id');
            $table->text('terms_and_conditions');
            $table->string('status');
            $table->float('total');
            $table->float('tax_amount');
            $table->float('grand_total');
            $table->float('discount');
            $table->float('final_price');
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
        Schema::drop('quotations');
    }
}
