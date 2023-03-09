<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFreeShipmentEnabledColumnToWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->boolean('free_shipment_enabled')->after('associated_zip_codes')->default(0);
            $table->string('free_shipment_enabled_value')->after('free_shipment_enabled')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('free_shipment_enabled');
            $table->dropColumn('free_shipment_enabled_value');
        });
    }
}
