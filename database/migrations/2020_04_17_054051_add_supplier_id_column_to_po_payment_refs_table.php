<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSupplierIdColumnToPoPaymentRefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_payment_refs', function (Blueprint $table) {
             $table->string('supplier_id')->after('payment_reference_no')->nullable();
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
            $table->dropColumn('supplier_id');
        });
    }
}
