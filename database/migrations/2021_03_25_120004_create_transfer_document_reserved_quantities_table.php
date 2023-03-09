<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransferDocumentReservedQuantitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_document_reserved_quantities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('po_id')->nullable();
            $table->string('pod_id')->nullable();
            $table->string('stock_id')->nullable();
            $table->string('reserved_quantity')->nullable();
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
        Schema::dropIfExists('transfer_document_reserved_quantities');
    }
}
