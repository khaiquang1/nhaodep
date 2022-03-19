<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateQtemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qtemplates', function (Blueprint $table) {
            $table->integer('quotation_duration')->nullable()->change();
            $table->text('terms_and_conditions')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qtemplates', function(Blueprint $table)
        {
            $table->dropColumn(array('quotation_duration','terms_and_conditions'));
        });
    }
}
