<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProductTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('types')->insert([

                [
                    'title' => 'Chilled',
                    'created_at'=> Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' =>Carbon::now()->format('Y-m-d H:i:s'),
                    
                ],
                [
                    'title' => 'Frozen',
                    'created_at'=> Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    
                ],
                [
                    'title' => 'Dry',
                    'created_at'=> Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    
                ],
                [
                    'title' => 'Fresh',
                    'created_at'=> Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    
                ],
            ]);
    }
}
