<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFreightLandingToTempProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_products', function (Blueprint $table) {
            $table->float('extra_cost')->after('buying_price')->nullable();            
            $table->float('freight')->after('extra_cost')->nullable();            
            $table->float('landing')->after('freight')->nullable();            
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
            $table->dropColumn('extra_cost');            
            $table->dropColumn('freight');            
            $table->dropColumn('landing');            
        });
    }
}
