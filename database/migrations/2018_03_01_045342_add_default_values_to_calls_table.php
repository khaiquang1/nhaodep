<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultValuesToCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->text('call_summary')->nullable()->change();
            $table->integer('duration')->nullable()->change();
            $table->integer('company_id')->nullable()->change();
            $table->integer('resp_staff_id')->nullable()->change();
            $table->integer('user_id')->nullable()->change();
            $table->string('company_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->text('call_summary')->nullable(false)->change();
            $table->integer('duration')->nullable(false)->change();
            $table->integer('company_id')->nullable(false)->change();
            $table->integer('resp_staff_id')->nullable(false)->change();
            $table->integer('user_id')->nullable(false)->change();
            $table->string('company_name')->nullable(false)->change();
        });
    }
}
