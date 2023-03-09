<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVatThbColumnToSoldProductsReportRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sold_products_report_records', function (Blueprint $table) {
            $table->string('vat_thb')->after('vat')->nullable();
            $table->string('vintage')->after('vat_thb')->nullable();
            $table->string('available_stock')->after('vintage')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sold_products_report_records', function (Blueprint $table) {
            $table->dropColumn('vat_thb');
            $table->dropColumn('vintage');
            $table->dropColumn('available_stock');
        });
    }
}
