<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransferPickingColumnsInPurchaseOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->string('trasnfer_num_of_pieces')->after('expiration_date_2')->nullable();
            $table->string('trasnfer_pcs_shipped')->after('trasnfer_num_of_pieces')->nullable();
            $table->string('trasnfer_qty_shipped')->after('trasnfer_pcs_shipped')->nullable();
            $table->date('trasnfer_expiration_date')->after('trasnfer_qty_shipped')->nullable();
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
            $table->dropColumn('trasnfer_num_of_pieces');
            $table->dropColumn('trasnfer_pcs_shipped');
            $table->dropColumn('trasnfer_qty_shipped');
            $table->dropColumn('trasnfer_expiration_date');
        });
    }
}
