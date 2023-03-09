<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsBuildColumnInOrderProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->string('name')->after('product_id')->nullable();
            $table->string('short_desc')->after('name')->nullable();
            $table->string('is_billed')->after('status')->default("Product");
            $table->bigInteger('created_by')->after('is_billed')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropColumn('is_billed');
            $table->dropColumn('created_by');
            $table->dropColumn('name');
            $table->dropColumn('short_desc');
        });
    }
}
