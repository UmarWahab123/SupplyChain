<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeColumnToStockMovementReportRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_movement_report_records', function (Blueprint $table) {
            $table->string('type')->after('brand')->nullable();
            $table->string('type_2')->after('type')->nullable();
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
            $table->dropColumn('type');
            $table->dropColumn('type_2');
        });
    }
}
