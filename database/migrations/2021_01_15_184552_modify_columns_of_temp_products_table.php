<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnsOfTempProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_products', function (Blueprint $table) {
            $table->string('weight')->change();
            $table->string('min_stock')->change();
            $table->string('m_o_q')->change();
            $table->string('unit_conversion_rate')->change();
            $table->string('import_tax_book')->change();
            $table->string('import_tax_actual')->change();
            $table->string('vat')->change();
            $table->string('extra_cost')->change();
            $table->string('extra_tax')->change();
            $table->string('freight')->change();
            $table->string('landing')->change();
            $table->string('gross_weight')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('temp_products', function (Blueprint $table) {
            $table->float('weight')->change();
            $table->float('min_stock')->change();
            $table->float('m_o_q')->change();
            $table->float('unit_conversion_rate')->change();
            $table->float('import_tax_book')->change();
            $table->float('import_tax_actual')->change();
            $table->float('vat')->change();
            $table->float('extra_cost')->change();
            $table->float('extra_tax')->change();
            $table->float('freight')->change();
            $table->float('landing')->change();
            $table->float('gross_weight')->change();
        });
    }
}
