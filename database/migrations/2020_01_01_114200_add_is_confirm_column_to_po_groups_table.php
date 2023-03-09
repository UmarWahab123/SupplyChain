<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsConfirmColumnToPoGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_groups', function (Blueprint $table) {
            $table->integer('is_confirm')->default(0)->after('target_receive_date');
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
            $table->dropColumn('is_confirm');
        });
    }
}
