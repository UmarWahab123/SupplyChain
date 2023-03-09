<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsReceivingRecordImportingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_receiving_record_importings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('po_no')->nullable();
            $table->string('order_warehouse')->nullable();
            $table->string('order_no')->nullable();
            $table->string('sup_ref_no')->nullable();
            $table->string('supplier')->nullable();
            $table->string('pf_no')->nullable();
            $table->string('description')->nullable();
            $table->string('buying_unit')->nullable();
            $table->string('qty_ordered')->nullable();
            $table->string('qty_inv')->nullable();
            $table->string('total_gross_weight')->nullable();
            $table->string('total_extra_cost_thb')->nullable();
            $table->string('purchasing_price_eur')->nullable();
            $table->string('total_purchasing_price')->nullable();
            $table->string('currency_conversion_rate')->nullable();
            $table->string('purchasing_price_thb')->nullable();
            $table->string('total_purchasing_price_thb')->nullable();
            $table->string('import_tax_book_percent')->nullable();
            $table->string('freight_thb')->nullable();
            $table->string('landing_thb')->nullable();
            $table->string('book_percent_tax')->nullable();
            $table->string('weighted_percent')->nullable();
            $table->string('actual_tax')->nullable();
            $table->string('actual_tax_percent')->nullable();
            $table->string('sub_row')->nullable();

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
        Schema::dropIfExists('products_receiving_record_importings');
    }
}
