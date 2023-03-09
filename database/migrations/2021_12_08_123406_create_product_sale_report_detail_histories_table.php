<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductSaleReportDetailHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_sale_report_detail_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('order_product_id');
            $table->string('column');
            $table->decimal('old_value', 15, 2);
            $table->decimal('new_value', 15, 2);
            $table->integer('updated_by');
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
        Schema::dropIfExists('product_sale_report_detail_histories');
    }
}
