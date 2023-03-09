<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnTypeInVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('versions', function (Blueprint $table) {
            $table->text('version')->nullable()->change();
            $table->text('title')->nullable()->change();
            $table->text('feature')->change();
            $table->text('bugfix')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('versions', function (Blueprint $table) {
            $table->string('version')->change();
            $table->string('title')->change();
            $table->string('feature')->change();
            $table->string('bugfix')->change();
        });
    }
}
