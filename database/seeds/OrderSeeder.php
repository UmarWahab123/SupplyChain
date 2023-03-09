<?php

use App\Models\Common\Order\Order;
use Illuminate\Database\Seeder;


class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Order::create([
            'id'                    => 26,
            'status_prefix'         => 'DRD',
            'ref_prefix'            => 'H',
            'ref_id'                => 21000,
            'in_status_prefix'      => 'INB',
            'in_ref_prefix'         => 'H',
            'in_ref_id'             => 2010100,
            'full_inv_no'           => 'INB-H11120135',
            'manual_ref_no'         => NULL,
            'user_id'               => 24,
            'from_warehouse_id'     => 2,
            'customer_id'           => 1111,
            'total_amount'          => 130000,
            'total_paid'            => 50000,
            'vat_total_paid'        => 2659,
            'non_vat_total_paid'    => 0,
            'vat'                   => 0,
            'delivery_request_date' => 2022-05-21,
            'credit_note_date'      => 2022-05-23,
            'payment_due_date'      => 2022-05-27,
            'payment_terms_id'      => 4,
            'target_ship_date'      => 2022-05-29,
            'memo'                  => 'PO will follow',
            'discount'              => 1300,
            'shipping'              => 200,
            'billing_address_id'    => 173,
            'shipping_address_id'   => 173,
            'is_vat'                => 0,
            'is_manual'             => 0,
            'primary_status'        => 3,
            'status'                => 11,
            'payment_image'         => NULL,
            'created_by'            => 10,
            'previous_primary_status'=> 2,
            'previous_status'       => 34,
            'converted_to_invoice_on'=>2022-05-30,
            'cancelled_date'        => 2022-05-29,
            'ecommerce_order'       => 1,
            'ecommerce_order_id'    => 13,
            'ecommerce_order_no'    => 'ipBKxUsRuo4s',
            'delivery_note'         => NULL,
            'order_note_type'       => 2,
            'ecom_order'            => 0,
            'dont_show'             => 0,
            'is_tax_order'          => NULL,
            'created_at'            => NULL,
            'updated_at'            => NULL
        ]);
    }
}
