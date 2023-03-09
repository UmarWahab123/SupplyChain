<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('payment_reference')->nullable();
            $table->string('received_date')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('reference_name')->nullable();
            $table->string('billing_name')->nullable();
            $table->string('delivery_date')->nullable();
            $table->string('invoice_total')->nullable();
            $table->string('total_paid_vat')->nullable();
            $table->string('total_paid_non_vat')->nullable();
            $table->string('total_paid')->nullable();
            $table->string('difference')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('sale_person')->nullable();
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
        Schema::dropIfExists('transaction_records');
    }
}
