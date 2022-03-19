<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpportunitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('opportunity');
            $table->string('stages');
            $table->integer('customer_id');
            $table->double('expected_revenue');
            $table->string('probability');
            $table->string('email');
            $table->integer('phone');
            $table->integer('sales_person_id');
            $table->integer('sales_team_id');
            $table->date('next_action');
            $table->string('next_action_title');
            $table->date('expected_closing');
            $table->string('priority');
            $table->string('tags');
            $table->string('lost_reason');
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
        Schema::drop('opportunities');
    }
}
