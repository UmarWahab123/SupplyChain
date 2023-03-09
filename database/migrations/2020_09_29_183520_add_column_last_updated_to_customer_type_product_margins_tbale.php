<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLastUpdatedToCustomerTypeProductMarginsTbale extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_type_product_margins', function (Blueprint $table) {
            $table->timestamp('last_updated')->nullable()->after('is_mkt');
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
            $table->timestamp('last_updated');
        });
    }
}
