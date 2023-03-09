<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowOnInvoiceToOrderProductNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_product_notes', function (Blueprint $table) {
            $table->integer('show_on_invoice')->after('note')->default(0);
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
            $table->dropColumn('show_on_invoice');
        });
    }
}
