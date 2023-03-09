<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatesInProductCustomerFixedPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('product_customer_fixed_prices', function (Blueprint $table) {
         if(Schema::hasColumn('product_customer_fixed_prices','customer_type_id'))
         {
            $table->dropColumn('customer_type_id');
         }
         if(!Schema::hasColumn('product_customer_fixed_prices','customer_id'))
         {
            $table->bigInteger('customer_id')->unsigned()->nullable();
         }
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('product_customer_fixed_prices', function (Blueprint $table) {
          if(!Schema::hasColumn('product_customer_fixed_prices','customer_type_id'))
         {
            $table->bigInteger('customer_type_id')->unsigned()->nullable();
         }
         if(Schema::hasColumn('product_customer_fixed_prices','customer_id'))
         {
            $table->dropColumn('customer_id');
         }
      });
    }
}
