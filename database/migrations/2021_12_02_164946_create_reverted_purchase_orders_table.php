<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRevertedPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reverted_purchase_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('group_id')->nullable();
            $table->integer('po_id')->nullable();
            $table->integer('po_group_product_detail_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('supplier_id')->nullable();
            $table->double('quantity',10,4)->nullable();
            $table->double('total_received',10,4)->nullable();
            $table->integer('occurrence')->nullable();
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
        Schema::dropIfExists('reverted_purchase_orders');
    }
}
