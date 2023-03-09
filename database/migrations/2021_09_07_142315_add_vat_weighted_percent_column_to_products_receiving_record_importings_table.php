<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVatWeightedPercentColumnToProductsReceivingRecordImportingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_receiving_record_importings', function (Blueprint $table) {
            $table->string('pogpd_vat_actual')->after('actual_tax_percent')->nullable();
            $table->string('import_tax_book')->after('pogpd_vat_actual')->nullable();
            $table->string('book_vat_total')->after('import_tax_book')->nullable();
            $table->string('vat_weighted_percent')->after('book_vat_total')->nullable();
            $table->string('pogpd_vat_actual_price')->after('vat_weighted_percent')->nullable();
            $table->string('total_pogpd_vat_actual_price')->after('pogpd_vat_actual_price')->nullable();
            $table->string('pogpd_vat_actual_percent_val')->after('total_pogpd_vat_actual_price')->nullable();
            $table->string('total_import_tax_thb')->after('pogpd_vat_actual_percent_val')->nullable();
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
            $table->dropColumn('pogpd_vat_actual');
            $table->dropColumn('import_tax_book');
            $table->dropColumn('book_vat_total');
            $table->dropColumn('vat_weighted_percent');
            $table->dropColumn('pogpd_vat_actual_price');
            $table->dropColumn('total_pogpd_vat_actual_price');
            $table->dropColumn('pogpd_vat_actual_percent_val');
            $table->dropColumn('total_import_tax_thb');
        });
    }
}
