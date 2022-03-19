<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultValuesToInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('order_id')->nullable()->change();
            $table->integer('customer_id')->nullable()->change();
            $table->string('sales_person_id')->nullable()->change();
            $table->integer('sales_team_id')->nullable()->change();
            $table->string('invoice_number')->nullable()->change();
            $table->string('payment_term')->nullable()->change();
            $table->string('status')->nullable()->change();
            $table->float('total')->nullable()->change();
            $table->float('tax_amount')->nullable()->change();
            $table->float('grand_total')->nullable()->change();
            $table->float('unpaid_amount')->nullable()->change();
            $table->float('final_price')->nullable()->change();
            $table->integer('user_id')->nullable()->change();
            $table->integer('is_delete_list')->default(0)->change();
            $table->integer('quotation_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->integer('order_id')->nullable(false)->change();
            $table->integer('customer_id')->nullable(false)->change();
            $table->string('sales_person_id')->nullable(false)->change();
            $table->integer('sales_team_id')->nullable(false)->change();
            $table->string('invoice_number')->nullable(false)->change();
            $table->string('payment_term')->nullable(false)->change();
            $table->string('status')->nullable(false)->change();
            $table->float('total')->nullable(false)->change();
            $table->float('tax_amount')->nullable(false)->change();
            $table->float('grand_total')->nullable(false)->change();
            $table->float('unpaid_amount')->nullable(false)->change();
            $table->float('final_price')->nullable(false)->change();
            $table->integer('user_id')->nullable(false)->change();
            $table->integer('is_delete_list')->nullable()->change();
            $table->dropColumn('quotation_id');
        });
    }
}
