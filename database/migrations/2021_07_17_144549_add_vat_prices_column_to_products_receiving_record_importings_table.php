<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVatPricesColumnToProductsReceivingRecordImportingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products_receiving_record_importings', function (Blueprint $table) {
            $table->string('purchasing_price_eur_with_vat')->after('purchasing_price_eur')->nullable();
            $table->string('total_purchasing_price_with_vat')->after('total_purchasing_price')->nullable();
            $table->string('purchasing_price_thb_with_vat')->after('purchasing_price_thb')->nullable();
            $table->string('total_purchasing_price_thb_with_vat')->after('total_purchasing_price_thb')->nullable();
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
            $table->dropColumn('purchasing_price_eur_with_vat');
            $table->dropColumn('total_purchasing_price_with_vat');
            $table->dropColumn('purchasing_price_thb_with_vat');
            $table->dropColumn('total_purchasing_price_thb_with_vat');
        });
    }
}
