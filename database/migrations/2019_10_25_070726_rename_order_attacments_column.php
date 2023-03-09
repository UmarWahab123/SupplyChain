<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameOrderAttacmentsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_attachments', function(Blueprint $table) {
            $table->renameColumn('order_invoice_id', 'order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_attachments', function(Blueprint $table) {
            $table->renameColumn( 'order_id','order_invoice_id');
        });
    }
}
