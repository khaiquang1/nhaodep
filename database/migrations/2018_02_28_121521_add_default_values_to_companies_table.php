<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultValuesToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->text('password')->nullable()->change();
            $table->string('lostpw')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('website')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('title')->nullable()->change();
            $table->string('company_attachment')->nullable()->change();
            $table->integer('main_contact_person')->nullable()->change();
            $table->integer('sales_team_id')->nullable()->change();
            $table->integer('country_id')->nullable()->change();
            $table->string('longitude')->nullable()->change();
            $table->string('latitude')->nullable()->change();
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
        Schema::table('companies', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->text('password')->nullable(false)->change();
            $table->string('lostpw')->nullable(false)->change();
            $table->string('address')->nullable(false)->change();
            $table->string('website')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->string('title')->nullable(false)->change();
            $table->string('company_attachment')->nullable(false)->change();
            $table->integer('main_contact_person')->nullable(false)->change();
            $table->integer('sales_team_id')->nullable(false)->change();
            $table->integer('country_id')->nullable(false)->change();
            $table->string('longitude')->nullable(false)->change();
            $table->string('latitude')->nullable(false)->change();
            $table->integer('user_id')->nullable(false)->change();
        });
    }
}
