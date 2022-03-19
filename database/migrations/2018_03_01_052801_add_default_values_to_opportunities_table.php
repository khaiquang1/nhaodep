<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultValuesToOpportunitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->string('opportunity')->nullable()->change();
            $table->string('stages')->nullable()->change();
            $table->integer('customer_id')->nullable()->change();
            $table->string('probability')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->bigInteger('phone')->nullable()->change();
            $table->integer('sales_person_id')->nullable()->change();
            $table->integer('sales_team_id')->nullable()->change();
            $table->date('next_action')->nullable()->change();
            $table->string('next_action_title')->nullable()->change();
            $table->date('expected_closing')->nullable()->change();
            $table->string('priority')->nullable()->change();
            $table->integer('user_id')->nullable()->change();
            $table->string('product_name')->nullable()->change();
            $table->string('team_name')->nullable()->change();
            $table->string('staff')->nullable()->change();
            $table->text('additional_info')->nullable()->change();
            $table->string('mobile')->nullable()->change();
            $table->string('salesteam')->nullable()->change();
            $table->string('company_name')->nullable()->change();
            $table->integer('is_archived')->default(0)->change();
            $table->integer('is_delete_list')->default(0)->change();
            $table->integer('is_converted_list')->default(0)->change();
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
            $table->string('opportunity')->nullable(false)->change();
            $table->string('stages')->nullable(false)->change();
            $table->integer('customer_id')->nullable(false)->change();
            $table->string('probability')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->bigInteger('phone')->nullable(false)->change();
            $table->integer('sales_person_id')->nullable(false)->change();
            $table->integer('sales_team_id')->nullable(false)->change();
            $table->date('next_action')->nullable(false)->change();
            $table->string('next_action_title')->nullable(false)->change();
            $table->date('expected_closing')->nullable(false)->change();
            $table->string('priority')->nullable(false)->change();
            $table->integer('user_id')->nullable(false)->change();
            $table->string('product_name')->nullable(false)->change();
            $table->string('team_name')->nullable(false)->change();
            $table->string('staff')->nullable(false)->change();
            $table->text('additional_info')->nullable(false)->change();
            $table->string('mobile')->nullable(false)->change();
            $table->string('salesteam')->nullable(false)->change();
            $table->string('company_name')->nullable(false)->change();
            $table->integer('is_archived');
            $table->integer('is_delete_list');
            $table->integer('is_converted_list');
        });
    }
}
