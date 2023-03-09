<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call(CountriesTableSeeder::class);
        $this->call(StatesTableSeeder::class);
        $this->call(ProductCategoriesTableSeeder::class);
        $this->call(ProductTypesTableSeeder::class);
        $this->call(CustomerCategoriesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(StatusCheckForCompleteProductsExportsTableSeeder::class);

        $this->call(ConfigurationTableSeeder::class);
        $this->call(StatusesTableSeeder::class);
        $this->call(VariablesTableSeeder::class);
        $this->call(MenusTableSeeder::class);
        $this->call(PaymentTermsTableSeeder::class);
        $this->call(PaymentTypesTableSeeder::class);
        $this->call(QuatationConfigsTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(GlobalAccessForRolesTableSeeder::class);
        $this->call(CustomerSeeder::class);
        $this->call(OrderSeeder::class);
        $this->call(SpoilageSeeder::class);



    }

}
