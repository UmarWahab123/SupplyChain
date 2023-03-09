<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTotalExtraTaxColumnInPoGroupProductDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_group_product_details', function (Blueprint $table) {
            $table->double('total_extra_tax')->after('total_extra_cost')->nullable();
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
            $table->dropColumn('total_extra_tax');
        });
    }
}
