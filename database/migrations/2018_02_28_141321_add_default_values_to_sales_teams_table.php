<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultValuesToSalesTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_teams', function (Blueprint $table) {
            $table->string('salesteam')->nullable()->change();
            $table->integer('team_leader')->nullable()->change();
            $table->integer('invoice_forecast')->nullable()->change();
            $table->string('team_members')->nullable()->change();
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
        Schema::table('sales_teams', function (Blueprint $table) {
            $table->string('salesteam')->nullable(false)->change();
            $table->integer('team_leader')->nullable(false)->change();
            $table->integer('invoice_forecast')->nullable(false)->change();
            $table->string('team_members')->nullable(false)->change();
            $table->integer('user_id')->nullable(false)->change();
        });
    }
}
