<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnsTypeOfSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('reference_number')->nullable()->change();
            $table->string('company')->nullable()->change();
            $table->string('credit_term')->nullable()->change();
            $table->string('first_name')->nullable()->change();
            $table->string('last_name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('secondary_phone')->nullable()->change();
            $table->string('address_line_1')->nullable()->change();
            $table->string('address_line_2')->nullable()->change();
            $table->string('postalcode')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('reference_number')->nullable(false)->change();
            $table->binary('company')->nullable(false)->change();
            $table->binary('credit_term')->nullable(false)->change();
            $table->binary('first_name')->nullable(false)->change();
            $table->binary('last_name')->nullable(false)->change();
            $table->binary('email')->nullable(false)->change();
            $table->binary('phone')->nullable(false)->change();
            $table->binary('secondary_phone')->nullable(false)->change();
            $table->binary('address_line_1')->nullable(false)->change();
            $table->binary('address_line_2')->nullable(false)->change();
            $table->binary('postalcode')->nullable(false)->change();
        });
    }
}
