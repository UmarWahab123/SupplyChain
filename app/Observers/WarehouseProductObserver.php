<?php

namespace App\Observers;

use App\Models\Common\WarehouseProduct;
use App\Models\WooCom\EcomProduct;
use App\Helpers\GuzzuleRequestHelper;
use App\Models\Common\Deployment;

class WarehouseProductObserver
{
    /**
     * Handle the warehouse product "created" event.
     *
     * @param  \App\WarehouseProduct  $warehouseProduct
     * @return void
     */
    public function created(WarehouseProduct $warehouseProduct)
    {
        //dd('here');
        $link = '/api/warehouseproducts-create';
        $curl_output =  $this->curl_call($link, $warehouseProduct);
        return $curl_output;
    }

    /**
     * Handle the warehouse product "updated" event.
     *
     * @param  \App\WarehouseProduct  $warehouseProduct
     * @return void
     */
    public function updated(WarehouseProduct $warehouseProduct)
    {
        $external_link = config('app.external_link');
        $curl_output =  $this->curl_call($external_link, $warehouseProduct, $external_link);

        // $woocom_product = EcomProduct::where('web_product_id',$warehouseProduct->product_id)->first();
        // if ($woocom_product != null)
        // {
        //     $stock_quantity = round($warehouseProduct->available_quantity,0);
        //     if($stock_quantity <= 0)
        //     {
        //         $stock_quantity = 0;
        //     }
        //     $data = [
        //       'stock_quantity' => $stock_quantity,
        //       'stock_status' => ($stock_quantity != 0) ? 'instock' : 'outofstock'
        //     ];
        //     // dd($woocom_product->ecom_product_id, $stock_quantity);
        //     $id = intval($woocom_product->ecom_product_id);
        //     $product_ecom = \Codexshaper\WooCommerce\Facades\Product::update($id, $data);
        // }
        // \Log::info($warehouseProduct);

        $link = '/api/warehouseproducts-update';
        $curl_output =  $this->curl_call($link, $warehouseProduct);

        $token = config('app.external_token');
        $deployment = Deployment::where('type','woocommerce')->first();
        $url = @$deployment->url.'/wp-json/supplychain-woocommerce/v1/update-product/';
        $body =  ['product_id'=>  $warehouseProduct->product_id];
        $method = 'POST';
        $response = GuzzuleRequestHelper::guzzuleRequest($token,$url,$method,$body); 

        return $curl_output;
    }

    /**
     * Handle the warehouse product "deleted" event.
     *
     * @param  \App\WarehouseProduct  $warehouseProduct
     * @return void
     */
    public function deleted(WarehouseProduct $warehouseProduct)
    {
        $link = '/api/warehouseproducts-delete';
        $curl_output =  $this->curl_call($link, $warehouseProduct);
        return $curl_output;
    }


    public function curl_call($link, $data, $option_url = null){

        $url =  $url = $option_url != null ? $option_url : config('app.ecom_url').$link;;
        $curl = curl_init();
         //dd($curl);
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

        // dd($response);
        $err = curl_error($curl);
        curl_close($curl);
        // if ($err) {
        //     return "cURL Error #:" . $err;
        // } else {
        //     return $response;
        // }

    }

}
