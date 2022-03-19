<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultValuesToLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('opportunity')->nullable()->change();
            $table->integer('sales_team_id')->nullable()->change();
            $table->string('contact_name')->nullable()->change();
            $table->integer('country_id')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('function')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->integer('user_id')->nullable()->change();
            $table->string('product_name')->nullable()->change();
            $table->string('client_name')->nullable()->change();
            $table->text('additionl_info')->nullable()->change();
            $table->string('company_site')->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('opportunity')->nullable(false)->change();
            $table->integer('sales_team_id')->nullable(false)->change();
            $table->string('contact_name')->nullable(false)->change();
            $table->integer('country_id')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->string('function')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->integer('user_id')->nullable(false)->change();
            $table->string('product_name')->nullable(false)->change();
            $table->string('client_name')->nullable(false)->change();
            $table->text('additionl_info')->nullable(false)->change();
            $table->string('company_site')->nullable(false)->change();
        });
    }
}
