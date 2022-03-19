<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQtemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qtemplates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('quotation_template');
            $table->integer('quotation_duration');
            $table->boolean('immediate_payment')->default(1);
            $table->text('terms_and_conditions');
            $table->float('total');
            $table->float('tax_amount');
            $table->float('grand_total');
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
        Schema::drop('qtemplates');
    }
}
