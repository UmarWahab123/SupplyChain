<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotalExtraTaxThbInProductsReceivingRecordImportingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_receiving_record_importings', function (Blueprint $table) {
            $table->double('total_extra_tax_thb')->after('total_extra_cost_thb')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products_receiving_record_importings', function (Blueprint $table) {
            $table->dropColumn('total_extra_tax_thb');
        });
    }
}
