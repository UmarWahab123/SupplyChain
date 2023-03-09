<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFixedPricesColumnsToTempProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_products', function (Blueprint $table) {
            $table->float('restaurant_fixed_price')->after('leading_time')->nullable();
            $table->float('hotel_fixed_price')->after('restaurant_fixed_price')->nullable();
            $table->float('retail_fixed_price')->after('hotel_fixed_price')->nullable();
            $table->float('private_fixed_price')->after('retail_fixed_price')->nullable();
            $table->float('catering_fixed_price')->after('private_fixed_price')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('temp_products', function (Blueprint $table) {
            $table->dropColumn('restaurant_fixed_price');
            $table->dropColumn('hotel_fixed_price');
            $table->dropColumn('retail_fixed_price');
            $table->dropColumn('private_fixed_price');
            $table->dropColumn('catering_fixed_price');
            
        });
    }
}
