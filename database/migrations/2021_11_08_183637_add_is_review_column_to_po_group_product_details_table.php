<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsReviewColumnToPoGroupProductDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_group_product_details', function (Blueprint $table) {
            $table->boolean('is_review')->after('is_cost_update')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('po_group_product_details', function (Blueprint $table) {
            $table->dropColumn('is_review');
        });
    }
}
