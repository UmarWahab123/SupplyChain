<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToPoPaymentRefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_payment_refs', function (Blueprint $table) {
              $table->string('payment_method')->after('supplier_id')->nullable();
            $table->string('received_date')->after('payment_method')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('po_payment_refs', function (Blueprint $table) {
            $table->dropColumn('payment_method');
            $table->dropColumn('received_date');
        });
    }
}
