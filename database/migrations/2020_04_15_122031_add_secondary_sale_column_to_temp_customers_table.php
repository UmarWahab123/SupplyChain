<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSecondarySaleColumnToTempCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_customers', function (Blueprint $table) {
            $table->string('secondary_sale')->after('sales_person')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('temp_customers', function (Blueprint $table) {
            $table->dropColumn('secondary_sale');
        });
    }
}
