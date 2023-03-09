<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColLastDownloadToStatusCheckForSoldProductsExports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('status_check_for_sold_products_exports', function (Blueprint $table) {
            $table->string('last_download')->after('id')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('status_check_for_sold_products_exports', function (Blueprint $table) {
             $table->dropColumn('last_download');
        });
    }
}
