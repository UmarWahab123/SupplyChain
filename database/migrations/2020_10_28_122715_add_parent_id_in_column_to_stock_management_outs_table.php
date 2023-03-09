<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentIdInColumnToStockManagementOutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_management_outs', function (Blueprint $table) {
            $table->string('parent_id_in')->after('warehouse_id')->nullable();
            $table->string('available_stock')->after('parent_id_in')->nullable();
            $table->string('expiry_id')->after('available_stock')->nullable();
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
            $table->dropColumn('parent_id_in');
            $table->dropColumn('available_stock');
            $table->dropColumn('expiry_id');
        });
    }
}
