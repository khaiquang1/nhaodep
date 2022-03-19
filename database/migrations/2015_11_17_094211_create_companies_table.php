<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->text('password');
            $table->string('lostpw');
            $table->text('address');
            $table->string('website');
            $table->string('phone');
            $table->string('mobile');
            $table->string('fax');
            $table->string('title');
            $table->string('company_avatar');
            $table->string('company_attachment');
            $table->integer('main_contact_person');
            $table->integer('sales_team_id');
            $table->integer('country_id')->unsigned();
            $table->integer('state_id')->unsigned();
            $table->integer('city_id')->unsigned();
            $table->string('longitude');
            $table->string('latitude');
            $table->integer('user_id');
            $table->timestamps();
            $table->softDeletes();
            $table->engine = 'InnoDB';
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('companies');
    }
}
