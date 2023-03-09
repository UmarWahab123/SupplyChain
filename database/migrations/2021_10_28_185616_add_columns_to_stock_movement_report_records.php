<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToStockMovementReportRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_movement_report_records', function (Blueprint $table) {
            $table->double('in_from_purchase',15,4)->nullable()->after('start_count');
            $table->double('in_order_update',15,4)->nullable()->after('in_from_purchase');
            $table->double('in_transfer_document',15,4)->nullable()->after('in_order_update');
            $table->double('in_manual_adjustment',15,4)->nullable()->after('in_transfer_document');

            $table->double('out_order',15,4)->nullable()->after('stock_in');
            $table->double('out_transfer_document',15,4)->nullable()->after('out_order');
            $table->double('out_manual_adjustment',15,4)->nullable()->after('out_transfer_document');
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
            $table->dropColumn('in_from_purchase');
            $table->dropColumn('in_manual_adjustment');
            $table->dropColumn('in_transfer_document');
            $table->dropColumn('in_order_update');
            $table->dropColumn('out_order');
            $table->dropColumn('out_manual_adjustment');
            $table->dropColumn('out_transfer_document');
        });
    }
}
