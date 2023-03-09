<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExtraTaxExtraCostColumnsToPurchaseOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->string('unit_extra_cost')->after('pod_landing')->nullable();
            $table->string('total_extra_cost')->after('unit_extra_cost')->nullable();
            $table->string('unit_extra_tax')->after('total_extra_cost')->nullable();
            $table->string('total_extra_tax')->after('unit_extra_tax')->nullable();
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
            $table->dropColumn('unit_extra_cost');
            $table->dropColumn('total_extra_cost');
            $table->dropColumn('unit_extra_tax');
            $table->dropColumn('total_extra_tax');
        });
    }
}
