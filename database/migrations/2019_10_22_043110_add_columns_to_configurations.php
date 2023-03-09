<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToConfigurations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->string('quotation_prefix')->after('currency_symbol');
            $table->string('draft_invoice_prefix')->after('quotation_prefix');
            $table->string('invoice_prefix')->after('draft_invoice_prefix');
            $table->string('system_email')->after('invoice_prefix');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('quotation_prefix');
            $table->dropColumn('draft_invoice_prefix');
            $table->dropColumn('invoice_prefix');
            $table->dropColumn('system_email');
        });
    }
}
