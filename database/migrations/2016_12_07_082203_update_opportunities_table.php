<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOpportunitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->string('tags')->nullable()->change();
            $table->string('lost_reason')->nullable()->change(); 
            $table->text('internal_notes')->nullable()->change();
            $table->integer('assigned_partner_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('opportunities', function(Blueprint $table)
        {
            $table->dropColumn(array('tags','lost_reason','internal_notes','assigned_partner_id'));
        });
    }
}
