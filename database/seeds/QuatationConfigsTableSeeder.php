<?php

use Illuminate\Database\Seeder;

class QuatationConfigsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sql = file_get_contents(database_path() . '/seeds/quotation_configs.sql');
    
         DB::statement($sql);    

    }
}
