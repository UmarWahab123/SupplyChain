<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnShippedQtyToTransferDocumentReservedQuantitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transfer_document_reserved_quantities', function (Blueprint $table) {
            $table->string('qty_shipped')->nullable()->after('old_qty');
            $table->string('old_qty_shipped')->nullable()->after('qty_shipped');
            $table->string('qty_received')->nullable()->after('old_qty_shipped');
            $table->string('spoilage')->nullable()->after('qty_received');
            $table->string('spoilage_type')->nullable()->after('spoilage');
            $table->string('type')->nullable()->after('spoilage_type');
            $table->string('inbound_pod_id')->nullable()->after('type');
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
            $table->dropColumn('qty_shipped');
            $table->dropColumn('old_qty_shipped');
            $table->dropColumn('qty_received');
            $table->dropColumn('spoilage');
            $table->dropColumn('spoilage_type');
            $table->dropColumn('type');
            $table->dropColumn('inbound_pod_id');
        });
    }
}
