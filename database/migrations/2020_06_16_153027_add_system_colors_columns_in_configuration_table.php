<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSystemColorsColumnsInConfigurationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->string('bg_txt_color')->after('system_color')->nullable();
                $table->string('btn_hover_color')->after('bg_txt_color')->nullable();
                $table->string('btn_hover_txt_color')->after('btn_hover_color')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('bg_txt_color');
            $table->dropColumn('btn_hover_color');
            $table->dropColumn('btn_hover_txt_color');
        });
    }
}
