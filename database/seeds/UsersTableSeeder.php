<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $sql = file_get_contents(database_path() . '/seeds/users.sql');
    
         DB::statement($sql);
         $password = Hash::make('123456');
         User::create([
             'name'=>'Super Admin',
             'company_id'=>1,
             'warehouse_id'=>1,
             'user_name'=>'admin',
             'email'=>'admin@supplychain.com',
             'password'=>$password,
             'role_id'=>10,
             'status'=>1,
             'is_include_in_reports'=>1
         ]);

    }
}
