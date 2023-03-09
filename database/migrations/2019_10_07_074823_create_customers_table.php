<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reference_number')->nullable();
            $table->binary('company')->nullable();
            $table->string('reference_name')->nullable();
            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->binary('credit_term')->nullable();
            $table->bigInteger('user_id')->unsigned();
            $table->binary('first_name')->nullable();
            $table->binary('last_name')->nullable();
            $table->binary('email')->nullable();
            $table->binary('phone')->nullable();
            $table->binary('address_line_1')->nullable();
            $table->binary('address_line_2')->nullable();
            $table->unsignedInteger('country')->nullable();
            $table->unsignedInteger('state')->nullable();
            $table->binary('city')->nullable();
            $table->binary('postalcode')->nullable();
            $table->boolean('status')->default(1);
            $table->string('logo')->nullable();
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
        Schema::dropIfExists('customers');
    }
}
