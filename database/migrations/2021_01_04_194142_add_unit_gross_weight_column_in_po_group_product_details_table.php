<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUnitGrossWeightColumnInPoGroupProductDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_group_product_details', function (Blueprint $table) {
            $table->string('unit_gross_weight')->nullable()->after('import_tax_book_price');
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
            $table->dropColumn('unit_gross_weight');
        });
    }
}
