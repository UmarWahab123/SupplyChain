<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehouseZipCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_zip_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->integer('zipcode');
            $table->string('shipping_charges');
            $table->bigInteger('warehouse_id')->unsigned();
            $table->timestamps();
        });



        // Schema::table('warehouse_zip_codes',function($table){
        //     $table->foreign('warehouse_id','warehouse_key')->references('id')->on('warehouses')->onDelete('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse_zip_codes');
    }
}
