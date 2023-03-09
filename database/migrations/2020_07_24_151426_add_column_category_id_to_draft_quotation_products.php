<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCategoryIdToDraftQuotationProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_quotation_products', function (Blueprint $table) {
            $table->integer('category_id')->nullable()->after('brand');
            $table->text('hs_code')->nullable()->after('category_id');
            $table->string('product_temprature_c')->nullable()->after('hs_code');
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
            $table->dropColumn('category_id');
            $table->dropColumn('hs_code');
            $table->dropColumn('product_temprature_c');
        });
    }
}
