<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultValuesToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_name')->nullable()->change();
            $table->integer('category_id')->nullable()->change();
            $table->string('product_type')->nullable()->change();
            $table->integer('quantity_on_hand')->nullable()->change();
            $table->integer('quantity_available')->nullable()->change();
            $table->text('description_for_quotations')->nullable()->change();
            $table->integer('user_id')->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_name')->nullable(false)->change();
            $table->integer('category_id')->nullable(false)->change();
            $table->string('product_type')->nullable(false)->change();
            $table->integer('quantity_on_hand')->nullable(false)->change();
            $table->integer('quantity_available')->nullable(false)->change();
            $table->text('description_for_quotations')->nullable(false)->change();
            $table->integer('user_id')->nullable(false)->change();
        });
    }
}
