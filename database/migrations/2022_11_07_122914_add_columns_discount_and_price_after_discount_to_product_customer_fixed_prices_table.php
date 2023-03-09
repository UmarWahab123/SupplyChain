<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsDiscountAndPriceAfterDiscountToProductCustomerFixedPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_customer_fixed_prices', function (Blueprint $table) {
            $table->decimal('discount', 18, 2)->nullable()->after('fixed_price');
            $table->decimal('price_after_discount', 18, 2)->nullable()->after('discount');
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
            $table->dropColumn(['discount', 'price_after_discount']);
            // $table->dropColumn('price_after_discount');
        });
    }
}
