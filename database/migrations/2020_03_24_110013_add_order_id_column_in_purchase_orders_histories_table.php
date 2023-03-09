<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderIdColumnInPurchaseOrdersHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_orders_histories', function (Blueprint $table) {
            $table->integer('order_id')->after('user_id')->nullable();
            $table->integer('order_product_id')->after('order_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_orders_histories', function (Blueprint $table) {
            $table->dropColumn('order_id');
            $table->dropColumn('order_product_id');
        });
    }
}
