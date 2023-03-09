<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToDraftQuotationProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_quotation_products', function (Blueprint $table) {
            $table->string('exp_unit_cost')->after('quantity')->nullable();
            $table->string('margin')->after('exp_unit_cost')->nullable();
            $table->string('unit_price')->after('margin')->nullable();
            $table->string('total_price')->after('unit_price')->nullable();
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
            $table->dropColumn('exp_unit_cost');
            $table->dropColumn('margin');
            $table->dropColumn('unit_price');
            $table->dropColumn('total_price');
        });
    }
}
