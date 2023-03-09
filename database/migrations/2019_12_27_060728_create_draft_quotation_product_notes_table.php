<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDraftQuotationProductNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('draft_quotation_product_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('draft_quotation_product_id')->unsigned();
            // $table->foreign('draft_quotation_product_id')->references('id')->on('draft_quotation_products')->onDelete('cascade');
            $table->text('note');
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
        Schema::dropIfExists('draft_quotation_product_notes');
    }
}
