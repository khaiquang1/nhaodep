<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultValuesToInvoicesProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices_products', function (Blueprint $table) {
            $table->integer('invoice_id')->nullable()->change();
            $table->integer('product_id')->nullable()->change();
            $table->string('product_name')->nullable()->change();
            $table->integer('quantity')->nullable()->change();
            $table->float('price')->nullable()->change();
            $table->float('sub_total')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices_products', function (Blueprint $table) {
            $table->integer('invoice_id')->nullable(false)->change();
            $table->integer('product_id')->nullable(false)->change();
            $table->string('product_name')->nullable(false)->change();
            $table->integer('quantity')->nullable(false)->change();
            $table->float('price')->nullable(false)->change();
            $table->float('sub_total')->nullable(false)->change();
        });
    }
}
