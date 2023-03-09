<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSmiIdColumnToStockManagementOutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_management_outs', function (Blueprint $table) {
            $table->bigInteger('smi_id')->after('id')->nullable();
            $table->bigInteger('p_o_d_id')->after('order_product_id')->nullable();
            $table->string('quantity_in')->after('product_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_management_outs', function (Blueprint $table) {
            $table->dropColumn('smi_id');
            $table->dropColumn('p_o_d_id');
            $table->dropColumn('quantity_in');
        });
    }
}
