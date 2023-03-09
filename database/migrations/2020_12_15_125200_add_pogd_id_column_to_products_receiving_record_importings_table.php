<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPogdIdColumnToProductsReceivingRecordImportingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_receiving_record_importings', function (Blueprint $table) {
            $table->integer('po_id')->after('po_no')->nullable();
            $table->integer('pod_id')->after('po_id')->nullable();
            $table->integer('pogpd_id')->after('pod_id')->nullable();
            $table->string('discount')->after('qty_inv')->nullable();
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
            $table->dropColumn('po_id');
            $table->dropColumn('pod_id');
            $table->dropColumn('pogpd_id');
            $table->dropColumn('discount');
        });
    }
}
