<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoiceNumberColumnToDraftPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_purchase_orders', function (Blueprint $table) {
            $table->string('custom_invoice_number')->after('invoice_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('draft_purchase_orders', function (Blueprint $table) {
            $table->dropColumn('custom_invoice_number');
        });
    }
}
