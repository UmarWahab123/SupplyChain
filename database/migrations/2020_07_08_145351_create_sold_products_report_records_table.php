<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSoldProductsReportRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sold_products_report_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('order_no')->nullable();
            $table->string('customer')->nullable();
            $table->string('delivery_date')->nullable();
            $table->string('created_date')->nullable();
            $table->string('supply_from')->nullable();
            $table->string('warehouse')->nullable();
            $table->string('p_ref')->nullable();
            $table->string('item_description')->nullable();
            $table->string('selling_unit')->nullable();
            $table->string('qty')->nullable();
            $table->string('unit_price')->nullable();
            $table->string('total_amount')->nullable();
            $table->string('vat')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sold_products_report_records');
    }
}
