<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCoumnsOnAirplaneOnDomesticToProductsRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_records', function (Blueprint $table) {
            $table->string('on_airplane')->nullable()->after('on_water');
            $table->string('on_domestic')->nullable()->after('on_airplane');
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
            $table->dropColumn('on_airplane');
            $table->dropColumn('on_domestic');
        });
    }
}
