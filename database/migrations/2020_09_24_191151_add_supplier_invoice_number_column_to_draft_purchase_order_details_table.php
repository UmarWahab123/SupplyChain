<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSupplierInvoiceNumberColumnToDraftPurchaseOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_purchase_order_details', function (Blueprint $table) {
            $table->string('supplier_invoice_number')->after('warehouse_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('draft_purchase_order_details', function (Blueprint $table) {
            $table->dropColumn('supplier_invoice_number');
        });
    }
}
