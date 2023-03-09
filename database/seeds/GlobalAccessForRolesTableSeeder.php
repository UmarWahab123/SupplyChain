<?php

use Illuminate\Database\Seeder;

class GlobalAccessForRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sql = file_get_contents(database_path() . '/seeds/global_access_for_roles.sql');
    
         DB::statement($sql);  

    }
}
