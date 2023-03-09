<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFixedPriceUpdateDataColumnInProductCustomerFixedPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_customer_fixed_prices', function (Blueprint $table) {
            $table->timestamp('fixed_price_update_date')->after('expiration_date')->nullable();
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
            $table->dropColumn('fixed_price_update_date');
        });
    }
}
