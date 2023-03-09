<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVatActualColumnsInPoGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_groups', function (Blueprint $table) {
            $table->string('po_group_vat_actual')->nullable()->after('total_import_tax_book_percent');
            $table->string('po_group_vat_actual_percent')->nullable()->after('po_group_vat_actual');
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
            $table->dropColumn('po_group_vat_actual');
            $table->dropColumn('po_group_vat_actual_percent');
        });
    }
}
