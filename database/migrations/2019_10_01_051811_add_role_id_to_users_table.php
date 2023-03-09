<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRoleIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

            $table->bigInteger('parent_id')->unsigned()->after('password')->nullable();
            $table->bigInteger('role_id')->unsigned()->after('parent_id')->nullable();
            // $table->foreign('role_id','users_role_id_foreign')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if(Schema::hasColumn('users','parent_id'))
            {
             $table->dropColumn('parent_id');
            } 

            if(Schema::hasColumn('users','role_id'))
            {
             // $table->dropForeign('users_role_id_foreign');
             $table->dropColumn('role_id');
            }
        });
    }
}
