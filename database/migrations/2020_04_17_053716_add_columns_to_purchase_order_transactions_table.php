<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToPurchaseOrderTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order_transactions', function (Blueprint $table) {
            $table->string('supplier_id')->after('po_id')->nullable();
            $table->string('po_order_ref_no')->after('supplier_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_order_transactions', function (Blueprint $table) {
            $table->dropColumn('supplier_id');
            $table->dropColumn('po_order_ref_no');
        });
    }
}
