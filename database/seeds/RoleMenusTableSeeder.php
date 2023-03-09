<?php

use Illuminate\Database\Seeder;

class RoleMenusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sql = file_get_contents(database_path() . '/seeds/role_menus.sql');
    
         DB::statement($sql);
    }
    
}
