<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultValuesToEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->integer('assign_customer_id')->nullable()->change();
            $table->string('to')->nullable()->change();
            $table->string('from')->nullable()->change();
            $table->string('subject')->nullable()->change();
            $table->text('message')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->integer('assign_customer_id')->nullable(false)->change();
            $table->string('to')->nullable(false)->change();
            $table->string('from')->nullable(false)->change();
            $table->string('subject')->nullable(false)->change();
            $table->text('message')->nullable(false)->change();
        });
    }
}
