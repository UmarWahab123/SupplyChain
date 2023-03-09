<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockMovementReportRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_movement_report_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reference_code')->nullable();
            $table->string('short_desc')->nullable();
            $table->string('brand')->nullable();
            $table->string('selling_unit')->nullable();
            $table->string('start_count')->nullable();
            $table->string('stock_in')->nullable();
            $table->string('stock_out')->nullable();
            $table->string('stock_balance')->nullable();
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
        Schema::dropIfExists('stock_movement_report_records');
    }
}
