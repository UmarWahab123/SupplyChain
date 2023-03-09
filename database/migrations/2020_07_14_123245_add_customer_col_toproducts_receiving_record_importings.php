<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerColToproductsReceivingRecordImportings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_receiving_record_importings', function (Blueprint $table) {
            $table->string('customer')->nullable()->after('description');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products_receiving_record_importings', function (Blueprint $table) {
            $table->dropColumn('customer');
        });
    }
}
