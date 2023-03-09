<?php

use Illuminate\Database\Seeder;

class PaymentTermsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $sql = file_get_contents(database_path() . '/seeds/payment_terms.sql');
    
         DB::statement($sql);   
    }
}
