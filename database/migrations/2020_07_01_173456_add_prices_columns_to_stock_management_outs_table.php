<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPricesColumnsToStockManagementOutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_management_outs', function (Blueprint $table) {
            $table->string('cost')->after('note')->nullable();
            $table->string('cost_date')->after('cost')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_management_outs', function (Blueprint $table) {
            $table->dropColumn('cost');
            $table->dropColumn('cost_date');
        });
    }
}
