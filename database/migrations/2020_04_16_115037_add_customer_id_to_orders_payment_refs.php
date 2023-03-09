<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerIdToOrdersPaymentRefs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders_payment_refs', function (Blueprint $table) {
            $table->string('customer_id')->after('payment_reference_no')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders_payment_refs', function (Blueprint $table) {
            $table->dropColumn('customer_id');
            
        });
    }
}
