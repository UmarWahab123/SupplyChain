<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('company_name')->nullable();
            $table->string('billing_email');
            $table->string('billing_phone')->nullable();
            $table->string('billing_fax')->nullable();
            $table->string('billing_address')->nullable();
            $table->unsignedInteger('billing_country')->nullable();
            $table->unsignedInteger('billing_state')->nullable();
            $table->string('billing_city')->nullable();
            $table->integer('billing_zip')->nullable();
            $table->bigInteger('created_by')->nullable();
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
        Schema::dropIfExists('invoice_settings');
    }
}
