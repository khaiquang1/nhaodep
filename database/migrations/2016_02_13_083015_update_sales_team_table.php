<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateSalesTeamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_teams', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sales_teams` CHANGE `invoice_target` `invoice_target` DOUBLE(15,2) NOT NULL;");
            DB::statement("ALTER TABLE `sales_teams` CHANGE `invoice_forecast` `invoice_forecast` DOUBLE(15,2) NOT NULL;");
            $table->dropColumn('actual_invoice');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_teams', function (Blueprint $table) {
            DB::statement("ALTER TABLE `sales_teams` CHANGE `invoice_target` `invoice_target` INT NOT NULL;");
            DB::statement("ALTER TABLE `sales_teams` CHANGE `invoice_forecast` `invoice_forecast` INT NOT NULL;");
            $table->float('actual_invoice')->after('invoice_forecast');
        });
    }
}
