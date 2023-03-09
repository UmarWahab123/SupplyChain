<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('order_invoice_id')->unsigned();
            // $table->foreign('order_invoice_id')->references('id')->on('order_invoices')->onDelete('cascade');
            $table->text('file_title');
            $table->text('file');
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
        Schema::dropIfExists('order_attachments');
    }
}
