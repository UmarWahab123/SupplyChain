<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnsTypeInDraftPurchaseOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_purchase_order_details', function (Blueprint $table) {
            $table->string('pod_import_tax_book')->change();
            $table->string('pod_unit_price')->change();
            $table->string('pod_gross_weight')->change();
            $table->string('pod_total_gross_weight')->change();
            $table->string('pod_total_unit_price')->change();
            $table->string('pod_import_tax_book_price')->change();
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
            $table->float('pod_import_tax_book')->change();
            $table->float('pod_unit_price')->change();
            $table->float('pod_gross_weight')->change();
            $table->float('pod_total_gross_weight')->change();
            $table->float('pod_total_unit_price')->change();
            $table->float('pod_import_tax_book_price')->change();
        });
    }
}
