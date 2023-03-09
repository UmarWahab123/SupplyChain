<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDraftQuotationProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_quotation_products', function (Blueprint $table) {
            $table->unsignedInteger('product_id')->nullable()->change();
            $table->string('name')->nullable()->after('product_id');
            $table->string('short_desc')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('draft_quotation_products', function (Blueprint $table) {
            $table->unsignedInteger('product_id')->nullable(false)->change();
            $table->dropColumn('name');
            $table->dropColumn('short_desc');
         });
    }
}
