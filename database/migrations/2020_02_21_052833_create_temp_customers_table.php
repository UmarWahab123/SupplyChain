<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTempCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reference_number')->nullable();
            $table->string('reference_name')->nullable();
            $table->string('sales_person')->nullable();
            $table->string('company_name')->nullable();
            $table->string('classification')->nullable();
            $table->string('credit_term')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('address_reference_name')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('cell_no')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('email')->nullable();
            $table->string('fax')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_sur_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_tel')->nullable();
            $table->string('contact_position')->nullable();
            $table->integer('status')->default(0);

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
        Schema::dropIfExists('temp_customers');
    }
}
