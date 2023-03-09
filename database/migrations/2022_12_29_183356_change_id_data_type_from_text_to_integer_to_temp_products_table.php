<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeIdDataTypeFromTextToIntegerToTempProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_products', function (Blueprint $table) {
            $table->integer('primary_category')->change();
            $table->integer('category_id')->change();
            $table->integer('type_id')->change();
            $table->integer('type_3_id')->change();
            $table->integer('supplier_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('temp_products', function (Blueprint $table) {
            $table->text('primary_category')->change();
            $table->text('category_id')->change();
            $table->text('type_id')->change();
            $table->string('type_3_id')->change();
            $table->text('supplier_id')->change();
        });
    }
}
