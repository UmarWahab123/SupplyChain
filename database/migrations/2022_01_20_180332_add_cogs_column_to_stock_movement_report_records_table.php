<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCogsColumnToStockMovementReportRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_movement_report_records', function (Blueprint $table) {
            $table->decimal('cogs', 15, 4)->nullable()->after('stock_balance');
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
            $table->dropColumn('cogs');
        });
    }
}
