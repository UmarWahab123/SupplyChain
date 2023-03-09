<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMinStockColumnToStockMovementReportRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_movement_report_records', function (Blueprint $table) {
            $table->string('min_stock')->after('brand')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_movement_report_records', function (Blueprint $table) {
            $table->dropColumn('min_stock');
        });
    }
}
