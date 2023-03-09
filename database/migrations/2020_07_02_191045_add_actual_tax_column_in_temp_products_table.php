<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActualTaxColumnInTempProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_products', function (Blueprint $table) {
            $table->float('import_tax_actual')->after('import_tax_book')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('temp_products', function (Blueprint $table) {
            $table->dropColumn('import_tax_actual');
        });
    }
}
