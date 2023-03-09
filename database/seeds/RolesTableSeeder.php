<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $sql = file_get_contents(database_path() . '/seeds/roles.sql');
    
         DB::statement($sql);
    }
}
