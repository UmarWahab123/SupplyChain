<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePoVatConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('po_vat_configurations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('purchasing_vat')->default(0);
            $table->boolean('unit_price_plus_vat')->default(0);
            $table->boolean('total_amount_without_vat')->default(0);
            $table->boolean('total_amount_inc_vat')->default(0);
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
        Schema::dropIfExists('po_vat_configurations');
    }
}
