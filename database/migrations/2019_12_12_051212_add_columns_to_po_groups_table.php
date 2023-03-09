<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToPoGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_groups', function (Blueprint $table) {
            $table->float('freight')->after('courier')->nullable();
            $table->float('landing')->after('freight')->nullable();
            $table->float('tax')->after('landing')->nullable();
            $table->integer('warehouse_id')->after('tax')->nullable();
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
            $table->dropColumn('freight');
            $table->dropColumn('landing');
            $table->dropColumn('tax');
            $table->dropColumn('warehouse_id');
        });
    }
}
