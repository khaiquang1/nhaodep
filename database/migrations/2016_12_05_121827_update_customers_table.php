<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->text('address')->nullable()->change();
            $table->string('website')->nullable()->change();
            $table->string('job_position')->nullable()->change();
            $table->string('mobile')->nullable()->change();
            $table->string('fax')->nullable()->change();
            $table->string('title')->nullable()->change();
            $table->integer('company_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function(Blueprint $table)
        {
            $table->dropColumn(array('address','website','job_position','title','mobile','fax','company_id'));
        });
    }
}
