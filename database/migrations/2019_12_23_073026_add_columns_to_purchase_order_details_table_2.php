<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToPurchaseOrderDetailsTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->float('pod_freight')->after('pod_import_tax_book_price')->nullable();
            $table->float('pod_landing')->after('pod_freight')->nullable();
            $table->float('pod_actual_tax_percent')->after('pod_landing')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->dropColumn('pod_freight');
            $table->dropColumn('pod_landing');
            $table->dropColumn('pod_actual_tax_percent');
        });
    }
}
