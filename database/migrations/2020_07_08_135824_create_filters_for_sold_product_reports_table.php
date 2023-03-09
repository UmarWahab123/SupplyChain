<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFiltersForSoldProductReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('filters_for_sold_product_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('warehouse_id_exp')->nullable();
            $table->string('customer_id_exp')->nullable();
            $table->string('order_type_exp')->nullable();
            $table->string('product_id_exp')->nullable();
            $table->string('prod_category_exp')->nullable();
            $table->string('filter_exp')->nullable();
            $table->string('from_date_exp')->nullable();
            $table->string('to_date_exp')->nullable();
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
        Schema::dropIfExists('filters_for_sold_product_reports');
    }
}
