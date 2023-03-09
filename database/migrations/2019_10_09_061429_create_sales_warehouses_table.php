<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_warehouses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('warehouse_id')->unsigned()->nullable(); //FK
            $table->integer('sales_id')->unsigned()->nullable(); //FK
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
        Schema::dropIfExists('sales_warehouses');
    }
}
