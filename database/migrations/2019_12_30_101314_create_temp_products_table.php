<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTempProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('refrence_code')->nullable();
            $table->text('hs_code')->nullable();
            $table->text('short_desc')->nullable();
            $table->float('weight')->nullable();
            $table->integer('primary_category')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('type_id')->nullable();
            $table->integer('product_temprature_c')->nullable();
            $table->integer('buying_unit')->nullable();
            $table->integer('selling_unit')->nullable();
            $table->float('unit_conversion_rate')->nullable();
            $table->float('import_tax_book')->nullable();
            $table->float('vat')->nullable();
            $table->integer('selling_price')->nullable();
            $table->integer('supplier_id')->default(0);
            $table->integer('status')->default(0);
            $table->integer('created_by')->nullable();
            $table->integer('hasError')->default(0);
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
        Schema::dropIfExists('temp_products');
    }
}
