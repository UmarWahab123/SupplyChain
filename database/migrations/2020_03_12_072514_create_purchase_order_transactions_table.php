<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrderTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_order_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('po_id')->nullable();
            $table->integer('payment_method_id')->nullable();
            $table->timestamp('received_date')->nullable();
            $table->string('total_received')->nullable();
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
        Schema::dropIfExists('purchase_order_transactions');
    }
}
