<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeFieldsNullableInPoGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_groups', function (Blueprint $table) {
            $table->string('courier')->nullable()->change();
            $table->string('target_receive_date')->nullable()->change();
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
            $table->string('courier')->nullable(false)->change();
            $table->string('target_receive_date')->nullable(false)->change();
        });
    }
}
