<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnFileNameToExportStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('export_statuses', function (Blueprint $table) {
            $table->string('file_name')->nullable()->after('last_downloaded');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('export_statuses', function (Blueprint $table) {
            $table->dropColumn('file_name');
        });
    }
}
