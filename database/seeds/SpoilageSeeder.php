<?php

use App\Models\Common\Spoilage;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SpoilageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Spoilage::truncate();

        $spoilages = [
            [
                'id' => 1,
                'title' => 'Broken',
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'title' => 'Wastage',
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s')
            ],
            [
                'id' => 3,
                'title' => 'Lost',
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s')
            ],
            [
                'id' => 4,
                'title' => 'Expired',
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s')
            ]
        ];

        Spoilage::insert($spoilages);
    }
}
