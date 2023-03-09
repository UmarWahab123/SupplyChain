<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColsUserIdAndPaymentRefNoToPoTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order_transactions', function (Blueprint $table) {
            $table->integer('user_id')->after('po_id')->nullable();
            $table->string('payment_reference_no')->after('payment_method_id')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_order_transactions', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->dropColumn('payment_reference_no');
            
        });
    }
}
