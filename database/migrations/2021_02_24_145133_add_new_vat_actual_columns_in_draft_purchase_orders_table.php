<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewVatActualColumnsInDraftPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_purchase_orders', function (Blueprint $table) {
            $table->string('total_vat_actual')->nullable()->after('total_import_tax_book_price');
            $table->string('total_vat_actual_price')->nullable()->after('total_vat_actual');
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
            $table->dropColumn('total_vat_actual');
            $table->dropColumn('total_vat_actual_price');
        });
    }
}
