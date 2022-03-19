<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('mobile')->nullable()->change();
            $table->string('fax')->nullable()->change();
            $table->string('company_avatar')->nullable()->change();
            $table->integer('state_id')->unsigned()->nullable()->change();
            $table->integer('city_id')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function(Blueprint $table)
        {
            $table->dropColumn(array('mobile','fax','company_avatar','state_id','city_id'));
        });
    }
}
