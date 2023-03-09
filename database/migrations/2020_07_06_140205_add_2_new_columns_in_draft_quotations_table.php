<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Add2NewColumnsInDraftQuotationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('draft_quotations', function (Blueprint $table) {
            $table->text('manual_ref_no')->nullable()->after('id');
            $table->integer('is_vat')->default(0)->after('memo')->comment('0=Vat,1=Non-Vat');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('draft_quotations', function (Blueprint $table) {
            $table->dropColumn('manual_ref_no');
            $table->dropColumn('is_vat');
        });
    }
}
