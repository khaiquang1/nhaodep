<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->string('meeting_description')->nullable()->change();
            $table->string('privacy')->nullable()->change();
            $table->string('show_time_as')->nullable()->change();
            $table->string('duration')->nullable()->change(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meetings', function(Blueprint $table)
        {
            $table->dropColumn(array('meeting_description','privacy','show_time_as','duration'));
        });
    }
}
