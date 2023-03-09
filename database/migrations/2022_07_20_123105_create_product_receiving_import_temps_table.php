<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductReceivingImportTempsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_receiving_import_temps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_id')->nullable();
            $table->string('group_id')->nullable();
            $table->string('po_id')->nullable();
            $table->string('pod_id')->nullable();
            $table->string('p_c_id')->nullable();
            $table->string('prod_ref_no')->nullable();
            $table->string('purchasing_price_euro_old')->nullable();
            $table->string('purchasing_price_euro')->nullable();
            $table->boolean('purchasing_price_euro_updated')->default(0);
            $table->string('discount_old')->nullable();
            $table->string('discount')->nullable();
            $table->boolean('discount_updated')->default(0);
            $table->string('qty_inv_old')->nullable();
            $table->string('qty_inv')->nullable();
            $table->boolean('qty_inv_updated')->default(0);
            $table->string('total_gross_weight_old')->nullable();
            $table->string('total_gross_weight')->nullable();
            $table->boolean('total_gross_weight_updated')->default(0);
            $table->string('total_extra_cost_old')->nullable();
            $table->string('total_extra_cost')->nullable();
            $table->boolean('total_extra_cost_updated')->default(0);
            $table->string('total_extra_tax_oldd')->nullable();
            $table->string('total_extra_tax')->nullable();
            $table->boolean('total_extra_tax_updated')->default(0);
            $table->string('currency_conversion_rate_old')->nullable();
            $table->string('currency_conversion_rate')->nullable();
            $table->boolean('currency_conversion_rate_updated')->default(0);
            $table->string('gross_weight_old')->nullable();
            $table->string('gross_weight')->nullable();
            $table->boolean('gross_weight_updated')->default(0);
            $table->string('extra_cost_old')->nullable();
            $table->string('extra_cost')->nullable();
            $table->boolean('extra_cost_updated')->default(0);
            $table->string('extra_tax_old')->nullable();
            $table->string('extra_tax')->nullable();
            $table->boolean('extra_tax_updated')->default(0);
            $table->boolean('row_updated')->default(0);
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
        Schema::dropIfExists('product_receiving_import_temps');
    }
}
