<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsDefaultShippingColumnToCustomerBillingDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_billing_details', function (Blueprint $table) {
            $table->boolean('is_default_shipping')->after('is_default')->default(0);
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
            $table->dropColumn('is_default_shipping');
        });
    }
}
