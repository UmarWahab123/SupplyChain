<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomInvColumnsToStockManagementOutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_management_outs', function (Blueprint $table) {
            $table->string('custom_invoice_number')->after('cost_date')->nullable();
            $table->string('custom_line_number')->after('custom_invoice_number')->nullable();
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
            $table->dropColumn('custom_invoice_number');
            $table->dropColumn('custom_line_number');
        });
    }
}
