<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeQtyShippedTypeInOrderProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->string('pcs_shipped')->nullable()->change();
            $table->string('qty_shipped')->nullable()->change();
        });

        Schema::table('stock_management_outs', function (Blueprint $table) {
            $table->string('quantity_out')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->integer('pcs_shipped')->nullable()->change();
            $table->integer('qty_shipped')->nullable()->change();
        });

        Schema::table('stock_management_outs', function (Blueprint $table) {
            $table->integer('quantity_out')->nullable()->change();
        });
    }
}
