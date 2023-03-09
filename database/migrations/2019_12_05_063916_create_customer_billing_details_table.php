<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerBillingDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_billing_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('customer_id')->nullable();
            $table->string('title')->nullable();
            $table->string('billing_contact_name')->nullable();;
            $table->string('billing_email')->nullable();;
            $table->string('company_name')->nullable();
            $table->string('billing_phone')->nullable();
            $table->string('cell_number')->nullable();
            $table->string('billing_fax')->nullable();
            $table->string('billing_address')->nullable();
            $table->unsignedInteger('billing_country')->nullable();
            $table->unsignedInteger('billing_state')->nullable();
            $table->string('billing_city')->nullable();
            $table->integer('billing_zip')->nullable();
            $table->integer('tax_id')->nullable();
            $table->integer('is_default')->nullable();  
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
        Schema::dropIfExists('customer_billing_details');
    }
}
