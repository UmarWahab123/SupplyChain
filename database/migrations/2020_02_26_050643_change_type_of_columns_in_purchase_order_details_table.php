<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTypeOfColumnsInPurchaseOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->string('pod_import_tax_book')->nullable()->change();
            $table->string('pod_unit_price')->nullable()->change();
            $table->string('unit_price_in_thb')->nullable()->change();
            $table->string('pod_gross_weight')->nullable()->change();
            $table->string('pod_total_gross_weight')->nullable()->change();
            $table->string('pod_total_unit_price')->nullable()->change();
            $table->string('total_unit_price_in_thb')->nullable()->change();
            $table->string('pod_import_tax_book_price')->nullable()->change();
            $table->string('pod_freight')->nullable()->change();
            $table->string('pod_landing')->nullable()->change();
            $table->string('pod_total_extra_cost')->nullable()->change();
            $table->string('currency_conversion_rate')->nullable()->change();
            $table->string('pod_actual_tax_percent')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->float('pod_import_tax_book')->nullable()->change();
            $table->float('pod_unit_price')->nullable()->change();
            $table->float('unit_price_in_thb')->nullable()->change();
            $table->float('pod_gross_weight')->nullable()->change();
            $table->float('pod_total_gross_weight')->nullable()->change();
            $table->float('pod_total_unit_price')->nullable()->change();
            $table->float('total_unit_price_in_thb')->nullable()->change();
            $table->float('pod_import_tax_book_price')->nullable()->change();
            $table->float('pod_freight')->nullable()->change();
            $table->float('pod_landing')->nullable()->change();
            $table->float('pod_total_extra_cost')->nullable()->change();
            $table->decimal('currency_conversion_rate', 15, 6)->change();
            $table->float('pod_actual_tax_percent')->nullable()->change();
        });
    }
}
