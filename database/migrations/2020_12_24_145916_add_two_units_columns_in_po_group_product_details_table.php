<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTwoUnitsColumnsInPoGroupProductDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_group_product_details', function (Blueprint $table) {
            $table->string('total_extra_tax')->change();
            $table->string('unit_extra_cost')->after('landing')->nullable();
            $table->string('unit_extra_tax')->after('total_extra_cost')->nullable();
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
            $table->double('total_extra_tax')->change();
            $table->dropColumn('unit_extra_cost');
            $table->dropColumn('unit_extra_tax');
        });
    }
}
