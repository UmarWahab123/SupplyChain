<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomLineNumberColumnToDraftPurchaseOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_purchase_order_details', function (Blueprint $table) {
            $table->string('custom_line_number')->after('warehouse_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('draft_purchase_order_details', function (Blueprint $table) {
            $table->dropColumn('custom_line_number');
        });
    }
}
