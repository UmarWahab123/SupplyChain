<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePosIntegrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_integrations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('device_name')->nullable();
            $table->integer('warehouse_id')->nullable();
            $table->string('warehouse_name')->nullable();
            $table->string('token')->unique();
            $table->integer('status')->nullable();
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
        Schema::dropIfExists('pos_integrations');
    }
}
