<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOpportunitiesTableProductName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opportunities', function (Blueprint $table) {
          $table->string('product_name');
            $table->string('team_name');
            $table->string('staff');
            $table->text('additional_info');
            $table->string('mobile');
            $table->string('salesteam');
            $table->string('company_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropColumn(['product_name','team_name','additional_info','staff','mobile','salesteam','company_name']);
        });
    }
}
