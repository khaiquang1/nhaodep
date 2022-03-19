<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultValuesToQtemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qtemplates', function (Blueprint $table) {
            $table->string('quotation_template')->nullable()->change();
            $table->float('total')->nullable()->change();
            $table->float('tax_amount')->nullable()->change();
            $table->float('grand_total')->nullable()->change();
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
        Schema::table('qtemplates', function (Blueprint $table) {
            $table->string('quotation_template')->nullable(false)->change();
            $table->float('total')->nullable(false)->change();
            $table->float('tax_amount')->nullable(false)->change();
            $table->float('grand_total')->nullable(false)->change();
            $table->integer('user_id')->nullable(false)->change();
        });
    }
}
