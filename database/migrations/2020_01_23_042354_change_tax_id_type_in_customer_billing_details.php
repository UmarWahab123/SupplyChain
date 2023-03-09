<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTaxIdTypeInCustomerBillingDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_billing_details', function (Blueprint $table) {
            $table->string('tax_id',255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_billing_details', function (Blueprint $table) {
            $table->integer('tax_id')->nullable()->change();
        });
    }
}
