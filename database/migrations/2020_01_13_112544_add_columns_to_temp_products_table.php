<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToTempProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_products', function (Blueprint $table) {
            $table->text('stock_unit')->after('selling_unit')->nullable();
            $table->float('min_stock')->after('stock_unit')->nullable();
            $table->float('m_o_q')->after('min_stock')->nullable();
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
            $table->dropColumn('stock_unit');
            $table->dropColumn('min_stock');
            $table->dropColumn('m_o_q');
        });
    }
}
