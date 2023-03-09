<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBillingShippingIdColumnsToDraftQuotationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_quotations', function (Blueprint $table) {
            $table->integer('billing_address_id')->after('shipping')->nullable();
            $table->integer('shipping_address_id')->after('billing_address_id')->nullable();
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
            $table->dropColumn('billing_address_id');
            $table->dropColumn('shipping_address_id');
        });
    }
}
