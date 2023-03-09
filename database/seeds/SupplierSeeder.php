<?php

use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = file_get_contents(database_path() . '/seeds/suppliers.sql');
      DB::statement($sql);

        
    }
}
