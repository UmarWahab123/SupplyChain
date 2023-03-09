<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClumnsCogsTotalCogsToProductsReceivingRecordImportingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_receiving_record_importings', function (Blueprint $table) {
            $table->string('cogs')->nullable()->after('total_import_tax_thb');
            $table->string('total_cogs')->nullable()->after('cogs');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products_receiving_record_importings', function (Blueprint $table) {
            $table->dropColumn('cogs');
            $table->dropColumn('total_cogs');
        });
    }
}
