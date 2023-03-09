<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerQtyColumnToProductsReceivingRecordImportingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_receiving_record_importings', function (Blueprint $table) {
            $table->double('customer_qty',15,4)->after('qty_ordered')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products_receiving_record_importings', function (Blueprint $table) {
            $table->dropColumn('customer_qty');
        });
    }
}
