<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnsToPoGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_groups', function (Blueprint $table) {
            $table->float('po_group_total')->after('courier')->nullable();
            $table->float('po_group_import_tax_book')->after('total_quantity')->nullable();
            $table->float('po_group_total_gross_weight')->after('po_group_import_tax_book')->nullable();
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
            $table->dropColumn('po_group_total');
            $table->dropColumn('po_group_import_tax_book');
            $table->dropColumn('po_group_total_gross_weight');
        });
    }
}
