<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTypeOfColumnsInPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('total')->nullable()->change();
            $table->string('total_in_thb')->nullable()->change();
            $table->string('total_gross_weight')->nullable()->change();
            $table->string('total_import_tax_book')->nullable()->change();
            $table->string('total_import_tax_book_price')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('total', 15, 6)->change();
            $table->float('total_in_thb')->nullable()->change();
            $table->float('total_gross_weight')->nullable()->change();
            $table->float('total_import_tax_book')->nullable()->change();
            $table->float('total_import_tax_book_price')->nullable()->change();
        });
    }
}
