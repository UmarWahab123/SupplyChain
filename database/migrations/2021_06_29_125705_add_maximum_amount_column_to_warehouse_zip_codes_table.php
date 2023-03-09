<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaximumAmountColumnToWarehouseZipCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouse_zip_codes', function (Blueprint $table) {
            $table->boolean('free_shipment_enabled')->after('warehouse_id')->default(0);
            $table->string('maximum_amount')->after('free_shipment_enabled')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouse_zip_codes', function (Blueprint $table) {
            $table->dropColumn('free_shipment_enabled');
            $table->dropColumn('maximum_amount');
        });
    }
}
