<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToPurchaseOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->integer('quantity_received')->after('quantity')->nullable();
            $table->string('good_condition')->after('quantity_received')->nullable();
            $table->string('result')->after('good_condition')->nullable();
            $table->string('good_type')->after('result')->nullable();
            $table->string('temperature_c')->after('good_type')->nullable();
            $table->string('checker')->after('temperature_c')->nullable();
            $table->string('problem_found')->after('checker')->nullable();
            $table->string('solution')->after('problem_found')->nullable();
            $table->string('authorized_changes')->after('solution')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->dropColumn('quantity_received');
            $table->dropColumn('good_condition');
            $table->dropColumn('result'); 
            $table->dropColumn('good_type');
            $table->dropColumn('temperature_c');
            $table->dropColumn('checker');
            $table->dropColumn('problem_found');
            $table->dropColumn('solution');
            $table->dropColumn('authorized_changes');

        });
    }
}
