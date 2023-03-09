<?php

use App\Models\Common\Status;
use Illuminate\Database\Seeder;

class StatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Status::truncate();
      $sql = file_get_contents(database_path() . '/seeds/statuses.sql');

         DB::statement($sql);
    }
}
