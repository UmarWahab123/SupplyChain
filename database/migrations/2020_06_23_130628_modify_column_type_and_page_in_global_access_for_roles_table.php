<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnTypeAndPageInGlobalAccessForRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_access_for_roles', function (Blueprint $table) {
            $table->renameColumn('type', 'slug');
            $table->renameColumn('page', 'title');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('global_access_for_roles', function (Blueprint $table) {
            $table->renameColumn('slug', 'type');
            $table->renameColumn('title', 'page');
        });
    }
}
