<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateQoutationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->integer('qtemplate_id')->nullable()->change();
            $table->text('terms_and_conditions')->nullable()->change();
            $table->float('discount')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotations', function(Blueprint $table)
        {
            $table->dropColumn(array('qtemplate_id','terms_and_conditions','discount'));
        });
    }
}
