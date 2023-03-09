<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterQuantityColumnInStockOutHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_out_histories', function (Blueprint $table) {
            $table->string('quantity')->change();
            $table->string('total_cost')->after('sales');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_out_histories', function (Blueprint $table) {
            $table->bigInteger('quantity')->change();
            $table->dropColumn('total_cost');
        });
    }
}
