<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('refrence_code')->nullable();
            $table->text('hs_code')->nullable();
            $table->integer('product_type_id')->nullable();
            $table->text('name')->nullable();
            $table->text('image')->nullable();
            $table->text('long_desc')->nullable();
            $table->text('short_desc')->nullable();
            $table->float('import_tax_actual')->nullable();
            $table->float('vat')->nullable();
            $table->float('freight')->nullable();
            $table->float('landing')->nullable();
            $table->float('unit_conversion_rate')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('average_unit_price')->nullable();
            $table->float('weight')->nullable();
            $table->integer('buying_unit')->nullable();
            $table->integer('selling_unit')->nullable();
            $table->integer('selling_price')->nullable();
            $table->integer('product_category')->nullable();
            $table->integer('default_supplier')->nullable();
            $table->integer('last_supplier')->default(0);
            $table->integer('status')->default(0);
            $table->integer('created_by')->nullable();
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
        Schema::dropIfExists('products');
    }
}
