<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMinMaxColumnsInProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('min_o_qty')->after('is_cogs_updated')->nullable();
            $table->integer('max_o_qty')->after('min_o_qty')->nullable();
            $table->integer('ecommerce_enabled')->after('max_o_qty')->default(0);
        });

        // for aftab
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('min_o_qty');
            $table->dropColumn('max_o_qty');
            $table->dropColumn('ecommerce_enabled');
        });
    }
}
