<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFiltersForCompleteProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('filters_for_complete_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('default_supplier_exp')->nullable();
            $table->integer('prod_type_exp')->nullable();
            $table->integer('prod_category_exp')->nullable();
            $table->integer('prod_category_primary_exp')->nullable();
            $table->integer('filter_exp')->nullable();
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
        Schema::dropIfExists('filters_for_complete_products');
    }
}
