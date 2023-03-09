<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowTitleOnPrintsColumnToCustomerBillingDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_billing_details', function (Blueprint $table) {
            $table->integer('show_title')->after('title')->default(1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_billing_details', function (Blueprint $table) {
            $table->dropColumn('show_title');
        });
    }
}
