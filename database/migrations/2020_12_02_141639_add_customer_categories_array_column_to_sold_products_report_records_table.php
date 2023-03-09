<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerCategoriesArrayColumnToSoldProductsReportRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sold_products_report_records', function (Blueprint $table) {
            $table->text('customer_categories_array')->after('available_stock')->nullable();
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
            $table->dropColumn('customer_categories_array');
        });
    }
}
