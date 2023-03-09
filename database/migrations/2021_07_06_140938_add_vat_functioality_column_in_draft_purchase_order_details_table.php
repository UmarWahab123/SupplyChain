<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVatFunctioalityColumnInDraftPurchaseOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_purchase_order_details', function (Blueprint $table) {
            $table->string('pod_unit_price_with_vat')->after('pod_unit_price')->nullable();
            $table->string('pod_total_unit_price_with_vat')->after('pod_total_unit_price')->nullable();
            $table->string('pod_vat_actual_price_in_thb')->after('pod_vat_actual_price')->nullable();
            $table->string('pod_vat_actual_total_price')->after('pod_vat_actual_price_in_thb')->nullable();
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
        Schema::table('draft_purchase_order_details', function (Blueprint $table) {
            $table->dropColumn('pod_unit_price_with_vat');
            $table->dropColumn('pod_total_unit_price_with_vat');
            $table->dropColumn('pod_vat_actual_total_price');
            $table->dropColumn('pod_vat_actual_price_in_thb');
            $table->dropColumn('pod_vat_actual_total_price_in_thb');
        });
    }
}
