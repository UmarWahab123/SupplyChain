<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('refrence_code')->nullable();
            $table->string('short_desc')->nullable();
            $table->string('buying_unit')->nullable();
            $table->string('buying_unit_title')->nullable();
            $table->string('selling_unit')->nullable();
            $table->string('selling_unit_title')->nullable();
            $table->string('type_id')->nullable();
            $table->string('type_title')->nullable();
            $table->string('brand')->nullable();
            $table->string('product_temprature_c')->nullable();
            $table->string('total_buy_unit_cost_price')->nullable();
            $table->string('weight')->nullable();
            $table->string('unit_conversion_rate')->nullable();
            $table->string('selling_price')->nullable();
            $table->string('vat')->nullable();
            $table->string('import_tax_book')->nullable();
            $table->string('hs_code')->nullable();
            $table->string('primary_category')->nullable();
            $table->string('primary_category_title')->nullable();
            $table->string('category_id')->nullable();
            $table->string('category_title')->nullable();
            $table->string('supplier_id')->nullable();
            $table->string('supplier_description')->nullable();
            $table->string('product_supplier_reference_no')->nullable();
            $table->string('purchasing_price_eur')->nullable();

            $table->string('purchasing_price_thb')->nullable();
            $table->string('freight')->nullable();
            $table->string('landing')->nullable();
            $table->string('leading_time')->nullable();
            $table->string('default_supplier')->nullable();
            $table->string('currency_symbol')->nullable();
            $table->string('bangkok_current_qty')->nullable();
            $table->string('bangkok_reserved_qty')->nullable();
            $table->string('phuket_current_qty')->nullable();
            $table->string('phuket_reserved_qty')->nullable();
            $table->string('bcs_current_qty')->nullable();
            $table->string('bcs_reserved_qty')->nullable();
            $table->string('fixed_price')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products_records');
    }
}
