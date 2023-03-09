<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnBrandIdExpToTableFiltersForSoldProductReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('filters_for_sold_product_reports', function (Blueprint $table) {
            $table->string('brand_id_exp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('filters_for_sold_product_reports', function (Blueprint $table) {
            $table->dropColumn('brand_id_exp');
        });
    }
}
