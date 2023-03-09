<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnsTypeInSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supplier_notes', function (Blueprint $table) {
            $table->string('note_title')->nullable()->change();
            $table->string('note_description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supplier_notes', function (Blueprint $table) {
            $table->string('note_title')->nullable(false)->change();            
            $table->string('note_description')->nullable(false)->change();
        });
    }
}
