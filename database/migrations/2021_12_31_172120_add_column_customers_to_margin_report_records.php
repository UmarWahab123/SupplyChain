<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCustomersToMarginReportRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('margin_report_records', function (Blueprint $table) {
            $table->string('customers')->nullable()->after('office');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('margin_report_records', function (Blueprint $table) {
            $table->dropColumn('customers');
        });
    }
}
