<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnHsDescriptionToProudctsRecoreds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_records', function (Blueprint $table) {
            $table->string('hs_description')->nullable()->after('hs_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products_records', function (Blueprint $table) {
            $table->dropColumn('hs_description');
        });
    }
}
