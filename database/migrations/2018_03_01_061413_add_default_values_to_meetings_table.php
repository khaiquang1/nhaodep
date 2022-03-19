<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultValuesToMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->text('meeting_subject')->nullable()->change();
            $table->string('attendees')->nullable()->change();
            $table->integer('responsible_id')->nullable()->change();
            $table->string('location')->nullable()->change();
            $table->integer('user_id')->nullable()->change();
            $table->string('company_attendees')->nullable()->change();
            $table->string('staff_attendees')->nullable()->change();
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
        Schema::table('meetings', function (Blueprint $table) {
            $table->text('meeting_subject')->nullable(false)->change();
            $table->string('attendees')->nullable(false)->change();
            $table->integer('responsible_id')->nullable(false)->change();
            $table->string('location')->nullable(false)->change();
            $table->integer('user_id')->nullable(false)->change();
            $table->string('company_attendees')->nullable(false)->change();
            $table->string('staff_attendees')->nullable(false)->change();
            $table->string('company_name')->nullable(false)->change();
        });
    }
}
