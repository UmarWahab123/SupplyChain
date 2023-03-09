<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAutoPaymentRefNoToOrdersPaymentRefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders_payment_refs', function (Blueprint $table) {
            $table->string('auto_payment_ref_no')->nullable()->after('payment_reference_no');
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
            $table->dropColumn('auto_payment_ref_no');
        });
    }
}
