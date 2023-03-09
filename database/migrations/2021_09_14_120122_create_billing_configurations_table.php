<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_configurations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('official_launch_date')->nullable();
            $table->integer('total_users_allowed')->nullable();
            $table->integer('currency_id')->nullable();
            $table->float('current_annual_fee')->nullable();
            $table->float('monthly_price_per_user')->nullable();
            $table->integer('no_of_free_users')->nullable();
            $table->string('type');
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('billing_configurations');
    }
}
