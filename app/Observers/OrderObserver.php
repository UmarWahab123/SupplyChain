<?php

namespace App\Observers;
use App\Models\Common\Order\Order;
use App\Models\WooCom\EcomProduct;

class OrderObserver
{
    /**
     * Handle the product "created" event.
     *
     * @param  \App\Product  $product
     * @return void
     */
    public function created(Order $order)
    {
        
    }

    /**
     * Handle the product "updated" event.
     *
     * @param  \App\Product  $product
     * @return void
     */
    public function updated(Order $order)
    {
        $external_link = config('app.external_link');
        $curl_output =  $this->curl_call($external_link, $order);
        
        if($order->woo_com_id != null)
        {
            $order_id = $order->woo_com_id;
            $woocom_order = \Codexshaper\WooCommerce\Facades\Order::find($order_id);
            $line_item = [];

            foreach ($order->order_products as $op) 
            {
                foreach ($woocom_order['line_items'] as $item) 
                {
                    if (intval($item->id) == intval($op->woocom_item_id)) 
                    {
                        $woocom_product = EcomProduct::where('web_product_id',$op->product_id)->first();
                        $data_array = [
                            'quantity' => $op->quantity,
                            'subtotal' => $op->total_price,
                            'total' => $op->total_price_with_vat,
                            'total_tax' => $op->vat_amount_total,
                            'price' => $op->unit_price,
                            "sku" => "",
                            "product_id" => $woocom_product->ecom_product_id
                        ];
                        array_push($line_item, $data_array);
                    }
                }
            }
            $data     = [
                'discount_total' => $order->discount,
                'total' => $order->total_amount,
                'status' => 'completed',
                'line_items' => $line_item
            ];

            $woocommerce_order = \Codexshaper\WooCommerce\Facades\Order::update($order_id, $data);
        }
        return $curl_output;
    }

    /**
     * Handle the product "deleted" event.
     *
     * @param  \App\Product  $product
     * @return void
     */
    public function deleted(Order $order)
    {
     
    }

    public function curl_call($link, $data){
        $url =  config('app.ecom_url').$link;
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/json",
            "postman-token: 08f91779-330f-bf8f-1a64-d425e13710f9"
          ),
        ));

        $response = curl_exec($curl);
        
        $err = curl_error($curl);
        curl_close($curl);
        // if ($err) {
        //     return "cURL Error #:" . $err;
        // } else {
        //     return $response;
        // }
    }
}
