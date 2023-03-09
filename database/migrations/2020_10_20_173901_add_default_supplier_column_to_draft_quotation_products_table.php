<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultSupplierColumnToDraftQuotationProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_quotation_products', function (Blueprint $table) {
            $table->integer('default_supplier')->after('is_billed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('draft_quotation_products', function (Blueprint $table) {
            $table->dropColumn('default_supplier');
        });
    }
}
