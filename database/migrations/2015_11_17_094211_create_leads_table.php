<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('opportunity');
            $table->string('company_name');
            $table->integer('customer_id');
            $table->text('address');
            $table->integer('country_id');
            $table->integer('state_id');
            $table->integer('city_id');
            $table->integer('sales_person_id');
            $table->integer('sales_team_id');
            $table->string('contact_name');
            $table->string('title');
            $table->string('email');
            $table->string('function');
            $table->string('phone');
            $table->string('mobile');
            $table->string('fax');
            $table->string('tags');
            $table->string('priority');
            $table->text('internal_notes');
            $table->integer('assigned_partner_id');
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
        Schema::drop('leads');
    }
}
