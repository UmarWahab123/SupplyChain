<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToStockManagementOutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_management_outs', function (Blueprint $table) {
            $table->bigInteger('po_group_id')->after('p_o_d_id')->nullable();
            $table->string('note')->after('warehouse_id')->nullable();
            
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
            $table->dropColumn('po_group_id');
            $table->dropColumn('note');
            
        });
    }
}
