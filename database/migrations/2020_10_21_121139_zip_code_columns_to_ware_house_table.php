<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ZipCodeColumnsToWareHouseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('default_zipcode')->after('status')->nullable();
            $table->string('default_shipping')->after('default_zipcode')->nullable();
            $table->text('associated_zip_codes')->after('default_shipping')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ware_house', function (Blueprint $table) {
            //
        });
    }
}
