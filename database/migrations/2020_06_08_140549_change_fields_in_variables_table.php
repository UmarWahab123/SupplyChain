<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFieldsInVariablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('variables', function (Blueprint $table) {
            $table->renameColumn('old_value', 'slug');
            $table->renameColumn('new_value', 'terminology');
            $table->dropColumn('system_id');
            $table->string('page')->nullable();


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('variables', function (Blueprint $table) {
            $table->renameColumn('slug', 'old_value');
            $table->renameColumn('terminology', 'new_value');
            $table->integer('system_id');
            $table->dropColumn('page');
        });
    }
}

