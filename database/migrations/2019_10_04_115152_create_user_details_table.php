<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->string('company_name')->nullable();
            $table->string('sales_person')->nullable();
            $table->string('contact_name')->nullable();
            $table->text('classification')->nullable();
            $table->text('since')->nullable();
            $table->integer('open_orders')->nullable();
            $table->integer('total_orders')->nullable();
            $table->date('last_order_date')->nullable();

            $table->bigInteger('country_id')->unsigned();
            $table->bigInteger('state_id')->unsigned();
            
            $table->string('city_name')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('primary_contact')->nullable();
            $table->string('phone_no')->nullable();
            $table->text('category_type')->nullable(); // this and below this 
            $table->text('product_type')->nullable(); // both have some confusion
            $table->text('image')->nullable();
            $table->text('credit_terms')->nullable();

            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            // $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
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
        Schema::dropIfExists('user_details');
    }
}
