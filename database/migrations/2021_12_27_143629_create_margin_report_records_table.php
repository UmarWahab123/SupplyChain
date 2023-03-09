<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarginReportRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('margin_report_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('product_id');
            $table->string('office');
            $table->decimal('vat_out', 15, 2);
            $table->decimal('sales', 15, 2);
            $table->decimal('percent_sales', 15, 2);
            $table->decimal('vat_in', 15, 2);
            $table->decimal('cogs', 15, 2);
            $table->decimal('gp', 15, 2);
            $table->decimal('percent_gp', 15, 2);
            $table->decimal('margins', 15, 2);
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
        Schema::dropIfExists('margin_report_records');
    }
}
