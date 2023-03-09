<?php

use Illuminate\Database\Seeder;

class VariablesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = file_get_contents(database_path() . '/seeds/variables.sql');
    
         DB::statement($sql);
    
    }
}
