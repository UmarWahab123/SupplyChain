<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVatThbColsInPurchaseOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->string('pod_vat_actual_price_in_thb')->after('pod_vat_actual_price')->nullable();
            $table->string('pod_vat_actual_total_price_in_thb')->after('pod_vat_actual_total_price')->nullable();
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
            $table->dropColumn('pod_vat_actual_price_in_thb');
            $table->dropColumn('pod_vat_actual_total_price_in_thb');
        });
    }
}
