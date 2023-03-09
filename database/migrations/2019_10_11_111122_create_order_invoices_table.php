<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ref_id')->nullable();
            $table->bigInteger('user_id')->unsigned();
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->bigInteger('customer_id')->unsigned()->nullable();
            // $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->float('total_amount')->nullable();  
            $table->float('vat')->nullable();  


            $table->bigInteger('created_by')->unsigned();
            // $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->boolean('status')->default(0);
            $table->boolean('invoice_status')->default(0);
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
        Schema::dropIfExists('order_invoices');
    }
}
