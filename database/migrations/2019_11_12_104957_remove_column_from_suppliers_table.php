<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveColumnFromSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // $table->dropForeign('suppliers_product_type_id_foreign');
            $table->dropColumn('product_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->bigInteger('product_type_id')->unsigned();
            // $table->foreign('product_type_id')->references('id')->on('product_types')->onDelete('cascade');
        });
    }
}
