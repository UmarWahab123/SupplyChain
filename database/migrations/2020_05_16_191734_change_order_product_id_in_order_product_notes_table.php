<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeOrderProductIdInOrderProductNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_product_notes', function (Blueprint $table) {
            // $table->dropForeign('order_product_notes_order_product_id_foreign');
            $table->dropColumn('order_product_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_product_notes', function (Blueprint $table) {
            $table->bigInteger('order_product_id')->unsigned()->after('id');
            // $table->foreign('order_product_id')->references('id')->on('order_products')->onDelete('cascade');
        });
    }
}
