<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewThbColsInPoGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_groups', function (Blueprint $table) {
            $table->string('total_buying_price_in_thb_with_vat')->after('total_buying_price_in_thb')->nullable();
            $table->string('po_group_import_tax_book_with_vat')->after('po_group_import_tax_book')->nullable();
            $table->string('po_group_vat_actual_with_vat')->after('po_group_vat_actual')->nullable();
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
            $table->dropColumn('total_buying_price_in_thb_with_vat');
            $table->dropColumn('po_group_import_tax_book_with_vat');
            $table->dropColumn('po_group_vat_actual_with_vat');
        });
    }
}
