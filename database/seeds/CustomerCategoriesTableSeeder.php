<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CustomerCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('customer_categories')->insert([

                [
                    'title' => 'RESTAURANT',
                    'created_at'=> Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' =>Carbon::now()->format('Y-m-d H:i:s'),
                    
                ],
                [
                    'title' => 'HOTEL',
                    'created_at'=> Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    
                ],
                [
                    'title' => 'RETAIL',
                    'created_at'=> Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    
                ],
                [
                    'title' => 'PRIVATE',
                    'created_at'=> Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    
                ],
                [
                    'title' => 'CATERING',
                    'created_at'=> Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    
                ],
            ]);
    }
}
