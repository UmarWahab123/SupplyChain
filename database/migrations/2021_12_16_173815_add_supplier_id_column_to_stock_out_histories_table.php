<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSupplierIdColumnToStockOutHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_out_histories', function (Blueprint $table) {
            $table->bigInteger('order_id')->after('stock_out_id')->nullable();
            $table->bigInteger('order_product_id')->after('order_id')->nullable();
            $table->bigInteger('supplier_id')->after('order_product_id')->nullable();
            $table->bigInteger('po_id')->after('supplier_id')->nullable();
            $table->bigInteger('pod_id')->after('po_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_out_histories', function (Blueprint $table) {
            $table->dropColumn('order_id');
            $table->dropColumn('order_product_id');
            $table->dropColumn('supplier_id');
            $table->dropColumn('po_id');
            $table->dropColumn('pod_id');
        });
    }
}
