<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMultipleColumnsToDraftPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_purchase_orders', function (Blueprint $table) {
            $table->float('total_quantity')->after('total')->nullable();
            $table->float('total_gross_weight')->after('total_quantity')->nullable();
            $table->float('total_import_tax_book')->after('total_gross_weight')->nullable();
            $table->float('total_import_tax_book_price')->after('total_import_tax_book')->nullable();
            $table->date('payment_due_date')->after('supplier_id')->nullable();
            $table->date('target_receive_date')->after('payment_due_date')->nullable();
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
            $table->dropColumn('total_quantity');
            $table->dropColumn('total_gross_weight');
            $table->dropColumn('total_import_tax_book');
            $table->dropColumn('total_import_tax_book_price');
            $table->dropColumn('payment_due_date');
            $table->dropColumn('target_receive_date');
        });
    }
}
