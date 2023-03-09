<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterFiltersForCompleteProductsChangeProdCategoryExp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('filters_for_complete_products', function (Blueprint $table) {
            $table->string('prod_category_exp')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('filters_for_complete_products', function (Blueprint $table) {
            $table->integer('prod_category_exp')->change();
            
        });
    }
}
