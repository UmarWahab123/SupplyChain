<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnsToPurchaseOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->float('pod_import_tax_book')->after('product_id')->nullable();
            $table->float('pod_unit_price')->after('pod_import_tax_book')->nullable();
            $table->float('pod_gross_weight')->after('pod_unit_price')->nullable();
            $table->float('pod_total_gross_weight')->after('quantity')->nullable();
            $table->float('pod_total_unit_price')->after('pod_total_gross_weight')->nullable();
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
            $table->dropColumn('pod_import_tax_book');
            $table->dropColumn('pod_unit_price');
            $table->dropColumn('pod_gross_weight');
            $table->dropColumn('pod_total_gross_weight');
            $table->dropColumn('pod_total_unit_price');
        });
    }
}
