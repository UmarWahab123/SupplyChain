<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVatActualColumnsInPoGroupProductDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_group_product_details', function (Blueprint $table) {
            $table->string('pogpd_vat_actual')->nullable()->after('import_tax_book_price');
            $table->string('pogpd_vat_actual_percent')->nullable()->after('pogpd_vat_actual');
            $table->string('pogpd_vat_actual_price')->nullable()->after('actual_tax_percent');
            $table->string('pogpd_vat_actual_percent_val')->nullable()->after('pogpd_vat_actual_price');
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
            $table->dropColumn('pogpd_vat_actual');
            $table->dropColumn('pogpd_vat_actual_percent');
            $table->dropColumn('pogpd_vat_actual_price');
            $table->dropColumn('pogpd_vat_actual_percent_val');
        });
    }
}
