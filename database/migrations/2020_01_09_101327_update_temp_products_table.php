<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTempProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_products', function (Blueprint $table) {
            $table->text('brand_id')->after('type_id')->nullable();
            $table->text('p_s_r')->after('supplier_id')->nullable();
            $table->text('buying_price')->after('p_s_r')->nullable();
            $table->text('leading_time')->after('buying_price')->nullable();
            $table->text('primary_category')->nullable()->change();
            $table->text('category_id')->nullable()->change();
            $table->text('type_id')->nullable()->change();
            $table->text('buying_unit')->nullable()->change();
            $table->text('selling_unit')->nullable()->change();
            $table->text('supplier_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('temp_products', function (Blueprint $table) {
            $table->dropColumn('brand_id');
            $table->dropColumn('p_s_r');
            $table->dropColumn('buying_price');
            $table->dropColumn('leading_time');
            $table->Integer('primary_category')->nullable(false)->change();
            $table->Integer('category_id')->nullable(false)->change();
            $table->Integer('type_id')->nullable(false)->change();
            $table->Integer('buying_unit')->nullable(false)->change();
            $table->Integer('selling_unit')->nullable(false)->change();
            $table->Integer('supplier_id')->nullable(false)->change();
        });
    }
}
