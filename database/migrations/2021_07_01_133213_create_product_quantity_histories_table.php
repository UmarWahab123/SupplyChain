<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductQuantityHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_quantity_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('product_id')->nullable();
            $table->string('order_product_id')->nullable();
            $table->string('order_id')->nullable();
            $table->string('quantity')->nullable();
            $table->string('type')->nullable();
            $table->string('order_type')->nullable();
            $table->string('user_id')->nullable();
            $table->string('warehouse')->nullable();
            $table->string('current_quantity')->nullable();
            $table->string('reserved_quantity')->nullable();
            $table->string('available_quantity')->nullable();
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
        Schema::dropIfExists('product_quantity_histories');
    }
}
