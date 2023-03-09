<?php

use App\Models\Common\Order\CustomerBillingDetail;
use App\Models\Sales\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Customer::create([
            'id'                    => 2,
            'reference_number'      => 'RF 1',
            'reference_no'          => 1,
            'company'               => 'Xtremes Marketing Private Limited',
            'reference_name'        => 'Xtremes Pakistan',
            'category_id'           => 1,
            'credit_term'           => '[XM - 1B]',
            'user_id'               => 1,
            'primary_sale_id'       => 1,
            'secondary_sale_id'     => 1,
            'first_name'            => 'Zeeshan',
            'last_name'             => 'Alamgir',
            'email'                 => 'zeeshan@gmail.com',
            'phone'                 => '0310 0909090',
            'address_line_1'        => '[XM - 1B]',
            'address_line_2'        => '[XM - 2B]',
            'country'               => 'Pakistan',
            'state'                 => 'Punjab',
            'ecommerce_customer_id' => 1,
            'ecommerce_customer'    => 'Zeeshan',
            'city'                  => 'Rawalpindi',
            'postalcode'            => '2300',
            'status'                => 1,
            'logo'                  => NULL,
            'language'              => 'en',
            'customer_credit_limit' => '999999',
            'manual_customer'       => 1,
            'last_order_date'       => '2022-05-27 12:49:00',
            'created_at'            => NULL,
            'updated_at'            => NULL,
            'deleted_at'            => NULL
        ]);

        CustomerBillingDetail::create([
            'id'                    => 1,
            'customer_id'           => 2,
            'title'                 => 'KATATHANI PHUKET BEACH RESORT',
            'show_title'            => 1,
            'billing_contact_name'  => 'Haddi',
            'billing_email'         => 'haddi@gmail.com',
            'company_name'          => 'Daraz',
            'billing_phone'         => '1212',
            'cell_number'           => '03331010100',
            'billing_fax'           => '111-777-999',
            'billing_address'       => 'Peshawar',
            'billing_country'       => 'Thai',
            'billing_state'         => '2022-05-27 12:49:00',
            'billing_city'          => 'Phuket',
            'billing_zip'           => 2100,
            'tax_id'                => 343,
            'is_default'            => 1,
            'is_default_shipping'   => 0,
            'status'                => 1,
            'created_at'            => NULL,
            'created_by'            => NULL,
            'updated_at'            => NULL,
            'deleted_at'            => NULL
        ]);
    }
}
