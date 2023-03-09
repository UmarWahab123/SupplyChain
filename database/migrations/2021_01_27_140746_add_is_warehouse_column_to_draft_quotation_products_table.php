<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsWarehouseColumnToDraftQuotationProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_quotation_products', function (Blueprint $table) {
            $table->integer('is_warehouse')->after('draft_quotation_id')->default(0)->nullable();
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
            $table->dropColumn('is_warehouse');
        });
    }
}
