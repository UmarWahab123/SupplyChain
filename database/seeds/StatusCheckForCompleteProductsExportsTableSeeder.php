<?php

use Illuminate\Database\Seeder;

class StatusCheckForCompleteProductsExportsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('status_check_for_complete_products_exports')->delete();
        
        \DB::table('status_check_for_complete_products_exports')->insert(array (
            0 => 
            array (
                'id' => 1,
                'last_downloaded' => null,
                'status' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ),
        ));
        
        
    }
}