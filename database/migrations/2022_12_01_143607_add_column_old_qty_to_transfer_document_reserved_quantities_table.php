<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnOldQtyToTransferDocumentReservedQuantitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transfer_document_reserved_quantities', function (Blueprint $table) {
            $table->string('old_qty')->nullable()->after('reserved_quantity');
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
            $table->dropColumn('old_qty');
        });
    }
}
