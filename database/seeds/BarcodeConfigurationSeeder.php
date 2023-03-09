<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarcodeConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sql = file_get_contents(database_path() . '/seeds/barcode_configuration.sql');
        DB::statement($sql);
    }
}
