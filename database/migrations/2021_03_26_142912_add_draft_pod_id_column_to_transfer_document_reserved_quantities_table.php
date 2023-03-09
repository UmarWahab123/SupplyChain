<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDraftPodIdColumnToTransferDocumentReservedQuantitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transfer_document_reserved_quantities', function (Blueprint $table) {
            $table->string('draft_po_id')->after('id')->nullable();
            $table->string('draft_pod_id')->after('draft_po_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transfer_document_reserved_quantities', function (Blueprint $table) {
            $table->dropColumn('draft_po_id');
            $table->dropColumn('draft_pod_id');
        });
    }
}
