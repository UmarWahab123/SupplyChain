<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTargetShipDateToDraftQuotationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_quotations', function (Blueprint $table) {
            $table->text('target_ship_date')->after('shipping_address_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('draft_quotations', function (Blueprint $table) {
            $table->dropColumn('target_ship_date');
        });
    }
}
