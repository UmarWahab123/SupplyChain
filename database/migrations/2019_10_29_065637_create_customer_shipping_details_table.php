<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerShippingDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_shipping_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customer_id')->nullable();
            $table->string('title');
            $table->string('shipping_contact_name');
            $table->string('shipping_email');
            $table->string('company_name');
            $table->string('shipping_phone');
            $table->string('shipping_fax');
            $table->string('shipping_address');
            $table->unsignedInteger('shipping_country')->nullable();
            $table->unsignedInteger('shipping_state')->nullable();
            $table->string('shipping_city')->nullable();
            $table->integer('shipping_zip');
            $table->boolean('status')->default(0)->comment = '0 = incomplete, 1 = completed';
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
        Schema::dropIfExists('customer_shipping_details');
    }
}
