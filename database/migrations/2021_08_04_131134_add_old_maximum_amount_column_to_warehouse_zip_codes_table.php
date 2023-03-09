<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOldMaximumAmountColumnToWarehouseZipCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouse_zip_codes', function (Blueprint $table) {
            $table->string('old_maximum_amount')->after('maximum_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouse_zip_codes', function (Blueprint $table) {
            $table->dropColumn('old_maximum_amount');
        });
    }
}
