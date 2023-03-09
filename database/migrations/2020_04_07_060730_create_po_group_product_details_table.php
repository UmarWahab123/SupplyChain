<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePoGroupProductDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('po_group_product_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('po_group_id')->nullable();
            $table->bigInteger('supplier_id')->nullable();
            $table->bigInteger('from_warehouse_id')->nullable();
            $table->bigInteger('to_warehouse_id')->nullable();
            $table->bigInteger('product_id')->nullable();
            $table->string('quantity_ordered')->nullable();
            $table->string('quantity_inv')->nullable();
            $table->string('quantity_received_1')->nullable();
            $table->string('expiration_date_1')->nullable();
            $table->string('quantity_received_2')->nullable();
            $table->string('expiration_date_2')->nullable();
            $table->string('import_tax_book')->nullable();
            $table->string('import_tax_book_price')->nullable();
            $table->string('total_gross_weight')->nullable();
            $table->string('unit_price')->nullable();
            $table->string('currency_conversion_rate')->nullable();
            $table->string('unit_price_in_thb')->nullable();
            $table->string('total_unit_price_in_thb')->nullable();
            $table->string('freight')->nullable();
            $table->string('landing')->nullable();
            $table->string('total_extra_cost')->nullable();
            $table->string('actual_tax_percent')->nullable();
            $table->string('good_condition')->nullable();
            $table->string('result')->nullable();
            $table->string('good_type')->nullable();
            $table->string('temperature_c')->nullable();
            $table->string('checker')->nullable();
            $table->string('problem_found')->nullable();
            $table->string('solution')->nullable();
            $table->string('authorized_changes')->nullable();
            $table->string('trasnfer_num_of_pieces')->nullable();
            $table->string('trasnfer_pcs_shipped')->nullable();
            $table->string('trasnfer_qty_shipped')->nullable();
            $table->string('trasnfer_expiration_date')->nullable();
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
        Schema::dropIfExists('po_group_product_details');
    }
}
