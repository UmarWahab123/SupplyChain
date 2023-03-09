<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->char('decimal_point',3)->after('currency_symbol')->nullable();
            $table->char('thousands_point',3)->after('decimal_point')->nullable();
            $table->char('decimal_places',3)->after('thousands_point')->nullable();
            $table->decimal('conversion_rate', 15, 6)->after('decimal_places')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn('decimal_point');
            $table->dropColumn('thousands_point');
            $table->dropColumn('decimal_places');
            $table->dropColumn('conversion_rate');
        });
    }
}
