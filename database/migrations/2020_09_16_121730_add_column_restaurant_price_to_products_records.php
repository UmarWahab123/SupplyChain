<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnRestaurantPriceToProductsRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_records', function (Blueprint $table) {
            $table->string('restaurant_price')->nullable()->after('total_buy_unit_cost_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products_records', function (Blueprint $table) {
            $table->dropColumn('restaurant_price');
        });
    }
}
