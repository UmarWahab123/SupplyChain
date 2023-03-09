<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTempSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_suppliers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('company')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('postalcode')->nullable();
            $table->string('currency_id')->nullable();
            $table->string('credit_term')->nullable();
            $table->string('tax_id')->nullable();
            $table->integer('status')->default(0);
            # The below coloumns are used for supplier contacts table
            $table->string('c_name')->nullable();
            $table->string('c_sur_name')->nullable();
            $table->string('c_email')->nullable();
            $table->string('c_telehone_number')->nullable();
            $table->string('c_position')->nullable();
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
        Schema::dropIfExists('temp_suppliers');
    }
}
