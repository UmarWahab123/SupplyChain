<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameConfigurationTemplatesColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configuration_templates', function(Blueprint $table) {
            $table->renameColumn('user_id', 'values');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configuration_templates', function(Blueprint $table) {
            $table->renameColumn('user_id', 'values');
        });
    }
}
