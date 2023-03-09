<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reference_number');
            $table->binary('company')->nullable();
            $table->bigInteger('category_id')->unsigned();
            // $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('cascade');
            $table->bigInteger('product_type_id')->unsigned();
            // $table->foreign('product_type_id')->references('id')->on('product_types')->onDelete('cascade');
            $table->binary('credit_term');
            $table->binary('first_name');
            $table->binary('last_name');
            $table->binary('email');
            $table->binary('phone')->nullable();
            $table->binary('secondary_phone')->nullable();
            $table->binary('address_line_1')->nullable();
            $table->binary('address_line_2')->nullable();
            $table->unsignedInteger('country')->nullable();
            $table->unsignedInteger('state')->nullable();
            $table->binary('city')->nullable();
            $table->binary('postalcode')->nullable();
            $table->boolean('status')->default(1);
            $table->bigInteger('user_id')->unsigned()->nullable();
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
        Schema::dropIfExists('suppliers');
    }
}
