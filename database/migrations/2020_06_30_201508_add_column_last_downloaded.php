<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLastDownloaded extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('status_check_for_complete_products_exports', function (Blueprint $table) {
            $table->date('last_downloaded')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('status_check_for_complete_products_exports', function (Blueprint $table) {
            $table->dropColumn('last_downloaded');
        });
    }
}
