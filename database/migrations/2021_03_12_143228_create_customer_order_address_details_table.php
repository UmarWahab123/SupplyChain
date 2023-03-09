<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerOrderAddressDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_order_address_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->bigInteger('order_id')->nullable();
            $table->bigInteger('customer_id')->nullable();
            $table->bigInteger('customer_billing_id')->nullable();

            $table->string('customer_name')->nullable();
            $table->string('street_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('is_tax_order')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('tax_name')->nullable();
            $table->string('tax_address')->nullable();
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
        Schema::dropIfExists('customer_order_address_details');
    }
}
