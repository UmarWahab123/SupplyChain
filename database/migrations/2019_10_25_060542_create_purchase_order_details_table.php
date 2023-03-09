<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('po_id')->nullable();
            $table->bigInteger('order_id')->nullable();
            $table->bigInteger('customer_id')->nullable();
            $table->bigInteger('order_product_id')->nullable();
            $table->bigInteger('supplier_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->text('remarks')->nullable();
            $table->bigInteger('created_by')->nullable();

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
        Schema::dropIfExists('purchase_order_details');
    }
}
