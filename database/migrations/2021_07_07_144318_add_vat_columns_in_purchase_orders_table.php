<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVatColumnsInPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('total_with_vat')->after('total_in_thb')->nullable();
            $table->string('total_with_vat_in_thb')->after('total_with_vat')->nullable();
            $table->string('vat_amount_total')->after('total_with_vat_in_thb')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('total_with_vat');
            $table->dropColumn('total_with_vat_in_thb');
            $table->dropColumn('vat_amount_total');
        });
    }
}
