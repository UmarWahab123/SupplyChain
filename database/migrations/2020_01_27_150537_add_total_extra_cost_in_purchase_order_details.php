<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotalExtraCostInPurchaseOrderDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->float('pod_total_extra_cost')->after('pod_landing')->nullable();
            $table->decimal('currency_conversion_rate',15,6)->after('pod_total_extra_cost')->nullable();
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
            $table->dropColumn('pod_total_extra_cost');
            $table->dropColumn('currency_conversion_rate');
        });
    }
}
