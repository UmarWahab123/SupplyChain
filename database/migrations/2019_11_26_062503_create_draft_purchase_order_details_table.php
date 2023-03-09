<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDraftPurchaseOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('draft_purchase_order_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('po_id')->nullable();
            $table->bigInteger('order_id')->nullable();
            $table->bigInteger('customer_id')->nullable();
            $table->bigInteger('order_product_id')->nullable();
            $table->bigInteger('supplier_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('draft_purchase_order_details');
    }
}
