<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceReceivePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_receive_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('invoice_id');
            $table->dateTime('payment_date');
            $table->string('payment_method');
            $table->float('payment_received');
            $table->string('payment_number');
            $table->string('paykey')->nullable();
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
        Schema::drop('invoice_receive_payments');
    }
}
