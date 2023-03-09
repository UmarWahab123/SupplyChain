<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockOutHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_out_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('stock_out_from_id')->nullable();
            $table->bigInteger('stock_out_id')->nullable();
            $table->bigInteger('quantity')->nullable();
            $table->string('sales')->nullable();
            $table->string('vat_out')->nullable();
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
        Schema::dropIfExists('stock_out_histories');
    }
}
