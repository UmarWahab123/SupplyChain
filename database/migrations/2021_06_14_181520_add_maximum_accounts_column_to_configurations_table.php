<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaximumAccountsColumnToConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->integer('maximum_admin_accounts')->after('btn_hover_txt_color')->nullable();
            $table->integer('maximum_staff_accounts')->after('maximum_admin_accounts')->nullable();
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
            $table->dropColumn('maximum_admin_accounts');
            $table->dropColumn('maximum_staff_accounts');
        });
    }
}
