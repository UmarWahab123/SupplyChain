<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_configurations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('notification_name')->nullable();
            $table->text('notification_discription')->nullable();
            $table->string('notification_type')->nullable();
            $table->boolean('notification_status')->default('0')->comment='0=disable,1=enable';
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
        Schema::dropIfExists('notification_configurations');
    }
}
