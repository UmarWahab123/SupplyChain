<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPoGroupProductDetailIdToProductReceivingHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_receiving_histories', function (Blueprint $table) {
            $table->bigInteger('p_g_p_d_id')->after('pod_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_receiving_histories', function (Blueprint $table) {
            $table->dropColumn('p_g_p_d_id');
        });
    }
}
