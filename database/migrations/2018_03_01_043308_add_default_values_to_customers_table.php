<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultValuesToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('user_id')->nullable()->change();
            $table->integer('belong_user_id')->nullable()->change();
            $table->string('company_avatar')->nullable()->change();
            $table->integer('sales_team_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('user_id')->nullable(false)->change();
            $table->integer('belong_user_id')->nullable(false)->change();
            $table->string('company_avatar')->nullable(false)->change();
            $table->integer('sales_team_id')->nullable(false)->change();
        });
    }
}
