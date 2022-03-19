<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->increments('id');
            $table->text('meeting_subject');
            $table->string('attendees');
            $table->integer('responsible_id');
            $table->dateTime('starting_date');
            $table->dateTime('ending_date');
            $table->boolean('all_day')->default(0);
            $table->string('location');
            $table->string('meeting_description');
            $table->string('privacy');
            $table->string('show_time_as');
            $table->string('duration');
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
        Schema::drop('meetings');
    }
}
