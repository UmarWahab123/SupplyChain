<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRefNoRelatedColumnsInStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->string('prefix')->after('parent_id')->nullable();
            $table->string('counter_formula')->after('prefix')->nullable();
            $table->string('reset')->after('counter_formula')->nullable();
            $table->string('counter')->after('reset')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('statuses', function (Blueprint $table) {
            $table->dropColumn('prefix');
            $table->dropColumn('counter_formula');
            $table->dropColumn('reset');
            $table->dropColumn('counter');
            
        });
    }
}
