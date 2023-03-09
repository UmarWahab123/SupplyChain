<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoiceRefColumnsInOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('in_status_prefix')->after('ref_id')->nullable();
            $table->string('in_ref_prefix')->after('in_status_prefix')->nullable();
            $table->string('in_ref_id')->after('in_ref_prefix')->nullable();
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
            $table->dropColumn('in_status_prefix');
            $table->dropColumn('in_ref_prefix');
            $table->dropColumn('in_ref_id');
        });
    }
}
