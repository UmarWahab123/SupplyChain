<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVatActualTaxColumnInPoGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_groups', function (Blueprint $table) {
            $table->float('vat_actual_tax')->after('tax')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('po_groups', function (Blueprint $table) {
            $table->dropColumn('vat_actual_tax');
        });
    }
}
