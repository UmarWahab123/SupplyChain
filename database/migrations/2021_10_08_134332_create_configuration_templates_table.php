<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigurationTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configuration_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('notification_configuration_id')->nullable();
            $table->string('notification_type')->nullable();
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('user_id')->nullable()->comment='get user email for send emails';
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
        Schema::dropIfExists('configuration_templates');
    }
}
