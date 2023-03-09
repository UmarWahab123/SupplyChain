<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Add2NewColumnsInOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('manual_ref_no')->nullable()->after('in_ref_id');
            $table->integer('is_vat')->default(0)->after('created_by')->comment('0=Vat,1=Non-Vat');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('manual_ref_no');
            $table->dropColumn('is_vat');
        });
    }
}
