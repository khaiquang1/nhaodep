<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQtemplateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qtemplate_products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('qtemplate_id');
            $table->integer('product_id');
            $table->string('product_name');
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->float('price');
            $table->float('sub_total');
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
        Schema::drop('qtemplate_products');
    }
}
