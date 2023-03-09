<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUnitConversionRateForProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('unit_conversion_rate', 15, 6)->change();
            $table->decimal('total_buy_unit_cost_price', 15, 6)->nullable()->after('unit_conversion_rate');
            $table->decimal('average_unit_price', 15, 6)->change();
            $table->decimal('selling_price', 15, 6)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products',function(Blueprint $table){
            $table->float('unit_conversion_rate')->nullable(false)->change();   
            $table->dropColumn('total_buy_unit_cost_price');
            $table->integer('average_unit_price')->nullable(false)->change();            
            $table->integer('selling_price')->nullable(false)->change();                     
        });
    }
}
