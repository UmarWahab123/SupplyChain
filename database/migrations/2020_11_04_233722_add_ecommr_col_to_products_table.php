<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEcommrColToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('discount')->after('refrence_code')->nullable();
                    $table->string('discount_expiry_date')->after('discount')->nullable();
                    $table->float('ecommr_conversion_rate')->after('discount_expiry_date')->nullable();
                    $table->integer('ecommr_cogs_price')->after('discount_expiry_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
                    $table->dropColumn('discount');
                    $table->dropColumn('discount_expiry_date');
                    $table->dropColumn('ecommr_conversion_rate');
                    $table->dropColumn('ecommr_cogs_price');
        });
    }
}
