<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotationConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_configs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('section');
            $table->string('display_prefrences')->nullable();
            $table->string('show_columns')->nullable();
            $table->string('print_prefrences')->nullable();
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
        Schema::dropIfExists('quotation_configs');
    }
}
