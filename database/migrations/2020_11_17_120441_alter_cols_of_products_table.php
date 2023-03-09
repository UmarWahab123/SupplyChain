<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColsOfProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('ecommr_conversion_rate');
            $table->float('selling_unit_conversion_rate')->after('ecommr_cogs_price')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
          Schema::table('products', function (Blueprint $table) {
          
            $table->dropColumn('selling_unit_conversion_rate');
        });
    }
}
