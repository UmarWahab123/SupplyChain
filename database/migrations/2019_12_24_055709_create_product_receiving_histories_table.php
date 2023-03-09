<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductReceivingHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_receiving_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('po_group_id')->nullable();
            $table->bigInteger('pod_id')->nullable();
            $table->string('term_key')->nullable();
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->string('ip_address', 100)->nullable();
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
        Schema::dropIfExists('product_receiving_histories');
    }
}
