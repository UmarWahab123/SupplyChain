<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExtraTaxColumnToProductsReceivingRecordImportingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_receiving_record_importings', function (Blueprint $table) {
            $table->string('product_note')->after('discount')->nullable();
            $table->string('gross_weight')->after('product_note')->nullable();
            $table->string('extra_cost')->after('total_gross_weight')->nullable();
            $table->string('extra_tax')->after('total_extra_cost_thb')->nullable();
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
            $table->dropColumn('product_note');
            $table->dropColumn('gross_weight');
            $table->dropColumn('extra_cost');
            $table->dropColumn('extra_tax');
        });
    }
}
