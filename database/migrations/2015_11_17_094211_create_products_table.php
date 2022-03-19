<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('product_name');
            $table->string('product_image');
            $table->integer('category_id');
            $table->string('product_type');
            $table->string('status');
            $table->integer('quantity_on_hand');
            $table->integer('quantity_available');
            $table->float('sale_price');
            $table->text('description');
            $table->text('description_for_quotations');
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
        Schema::drop('products');
    }
}
