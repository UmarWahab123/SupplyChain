<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsBuyingPriceBeforeDiscountToSupplierProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supplier_products', function (Blueprint $table) {
            $table->double('buying_price_before_discount', 15, 4)->after('supplier_description')->nullable();
            $table->double('discount', 15, 4)->after('buying_price_before_discount')->nullable();
            $table->double('currency_conversion_rate', 15, 4)->after('discount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supplier_products', function (Blueprint $table) {
            $table->dropColumn('buying_price_before_discount');
            $table->dropColumn('discount');
            $table->dropColumn('currency_conversion_rate');
        });
    }
}
