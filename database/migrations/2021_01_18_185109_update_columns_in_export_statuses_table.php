<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnsInExportStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('export_statuses', function (Blueprint $table) {
            $table->text('exception')->nullable()->change();
            $table->string('error_msgs')->nullable()->after('exception');
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
            $table->string('exception')->nullable()->change();
            $table->dropColumn('error_msgs');
        });
    }
}
