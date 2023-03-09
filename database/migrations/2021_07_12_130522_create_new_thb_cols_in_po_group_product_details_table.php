<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewThbColsInPoGroupProductDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_group_product_details', function (Blueprint $table) {
            $table->string('total_unit_price_in_thb_with_vat')->after('total_unit_price_in_thb')->nullable();
            $table->string('unit_price_with_vat')->after('unit_price')->nullable();
            $table->string('total_unit_price_with_vat')->after('total_unit_price')->nullable();
            $table->string('unit_price_in_thb_with_vat')->after('unit_price_in_thb')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('po_group_product_details', function (Blueprint $table) {
            $table->dropColumn('total_unit_price_in_thb_with_vat');
            $table->dropColumn('unit_price_with_vat');
            $table->dropColumn('total_unit_price_with_vat');
            $table->dropColumn('unit_price_in_thb_with_vat');
        });
    }
}
