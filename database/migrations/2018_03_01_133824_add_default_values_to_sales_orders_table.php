<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultValuesToSalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('sale_number')->nullable()->change();
            $table->integer('customer_id')->nullable()->change();
            $table->string('payment_term')->nullable()->change();
            $table->integer('sales_person_id')->nullable()->change();
            $table->integer('sales_team_id')->nullable()->change();
            $table->string('status')->nullable()->change();
            $table->float('total')->nullable()->change();
            $table->float('tax_amount')->nullable()->change();
            $table->float('grand_total')->nullable()->change();
            $table->float('discount')->nullable()->change();
            $table->float('final_price')->nullable()->change();
            $table->integer('user_id')->nullable()->change();
            $table->integer('quotation_id')->default(0)->change();
            $table->integer('is_delete_list')->default(0)->change();
            $table->integer('is_invoice_list')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('sale_number')->nullable(false)->change();
            $table->integer('customer_id')->nullable(false)->change();
            $table->string('payment_term')->nullable(false)->change();
            $table->integer('sales_person_id')->nullable(false)->change();
            $table->integer('sales_team_id')->nullable(false)->change();
            $table->string('status')->nullable(false)->change();
            $table->float('total')->nullable(false)->change();
            $table->float('tax_amount')->nullable(false)->change();
            $table->float('grand_total')->nullable(false)->change();
            $table->float('discount')->nullable(false)->change();
            $table->float('final_price')->nullable(false)->change();
            $table->integer('user_id')->nullable(false)->change();
            $table->integer('quotation_id')->nullable()->change();
            $table->integer('is_delete_list')->nullable()->change();
            $table->integer('is_invoice_list')->nullable()->change();
        });
    }
}
