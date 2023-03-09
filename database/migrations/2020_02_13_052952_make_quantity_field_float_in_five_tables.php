<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeQuantityFieldFloatInFiveTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_purchase_orders', function (Blueprint $table) {
            $table->string('total_quantity')->change();
        });

        Schema::table('draft_purchase_order_details', function (Blueprint $table) {
            $table->string('quantity')->change();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('total_quantity')->change();
        });

        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->string('quantity')->change();
        });

        Schema::table('po_groups', function (Blueprint $table) {
            $table->string('total_quantity')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('draft_purchase_orders', function (Blueprint $table) {
            $table->integer('total_quantity')->change();
        });

        Schema::table('draft_purchase_order_details', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->integer('total_quantity')->change();
        });

        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        Schema::table('po_groups', function (Blueprint $table) {
            $table->integer('total_quantity')->change();
        });
    }
}
