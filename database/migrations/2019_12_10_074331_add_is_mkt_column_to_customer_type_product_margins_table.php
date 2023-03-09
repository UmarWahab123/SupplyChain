<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsMktColumnToCustomerTypeProductMarginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_type_product_margins', function (Blueprint $table) {
            $table->boolean('is_mkt')->after('default_value')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_type_product_margins', function (Blueprint $table) {
            $table->dropColumn('is_mkt');
        });
    }
}
