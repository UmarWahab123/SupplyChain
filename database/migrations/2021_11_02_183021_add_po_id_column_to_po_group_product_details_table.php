<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPoIdColumnToPoGroupProductDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_group_product_details', function (Blueprint $table) {
            $table->integer('po_id')->after('po_group_id')->nullable();
            $table->string('pod_id')->after('po_id')->nullable();
            $table->string('order_id')->after('pod_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('po_group_product_details', function (Blueprint $table) {
            $table->dropColumn('po_id');
            $table->dropColumn('pod_id');
            $table->dropColumn('order_id');
        });
    }
}
