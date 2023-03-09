<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBilledItemColumnInPurchaseOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->string('billed_desc')->after('product_id')->nullable();
            $table->string('is_billed')->after('billed_desc')->default("Product");
            $table->bigInteger('created_by')->after('is_billed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->dropColumn('billed_desc');
            $table->dropColumn('is_billed');
            $table->dropColumn('created_by');
        });
    }
}
