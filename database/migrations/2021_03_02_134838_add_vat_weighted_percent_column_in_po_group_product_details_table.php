<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVatWeightedPercentColumnInPoGroupProductDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_group_product_details', function (Blueprint $table) {
            $table->string('vat_weighted_percent')->after('weighted_percent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('po_group_product_details', function (Blueprint $table) {
            $table->dropColumn('vat_weighted_percent');
        });
    }
}
