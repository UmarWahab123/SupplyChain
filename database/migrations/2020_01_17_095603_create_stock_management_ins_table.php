<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockManagementInsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_management_ins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('po_id')->unsigned()->nullable();
            $table->bigInteger('p_o_d_id')->unsigned()->nullable();
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->integer('quantity_in')->nullable();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->bigInteger('warehouse_id')->unsigned()->nullable();
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
        Schema::dropIfExists('stock_management_ins');
    }
}
