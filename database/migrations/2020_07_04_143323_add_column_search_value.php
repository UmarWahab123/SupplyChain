<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSearchValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('filters_for_complete_products', function (Blueprint $table) {
            $table->string('search_value')->nullable()->after('filter_exp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('filters_for_complete_products', function (Blueprint $table) {
            $table->dropColumn('search_value');
            
        });
    }
}
