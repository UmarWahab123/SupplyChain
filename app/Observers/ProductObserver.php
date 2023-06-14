<?php

namespace App\Observers;

use App\Models\Common\Product;
use App\Helpers\GuzzuleRequestHelper;
use App\Models\Common\Deployment;

class ProductObserver
{
    /**
     * Handle the product "created" event.
     *
     * @param  \App\Product  $product
     * @return void
     */
    public function created(Product $product)
    {
        $link = '/api/products-update';
        $curl_output =  $this->curl_call($link, $product);
        return $curl_output;
    }

    /**
     * Handle the product "updated" event.
     *
     * @param  \App\Product  $product
     * @return void
     */
    public function updated(Product $product)
    {
        $external_link = config('app.external_link');
        $curl_output =  $this->curl_call($external_link, $product, $external_link);
        // \Log::info($external_link);

        // dd('in products update function observer ');
        $link = '/api/products-update';
        $curl_output =  $this->curl_call($link, $product);

        //Update Product on Wordpress //

        $token = config('app.external_token');
        $deployment = Deployment::where('type','woocommerce')->first();
        $url = @$deployment->url.'/wp-json/supplychain-woocommerce/v1/update-product/';
        $body = ['product_id'=> $product->id];
        $method = 'POST';
        $response = GuzzuleRequestHelper::guzzuleRequest($token,$url,$method,$body); 

        // \Log::info($curl_output);
        return $curl_output;
    }

    /**
     * Handle the product "deleted" event.
     *
     * @param  \App\Product  $product
     * @return void
     */
    public function deleted(Product $product)
    {
        // dd($product);
        $link = '/api/products-delete';
        $curl_output =  $this->curl_call($link, $product);
        return $curl_output;
    }

    public function curl_call($link, $data, $option_url = null){
        $url = $option_url != null ? $option_url : config('app.ecom_url').$link;
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
        // \Log::info($response);
        
        $err = curl_error($curl);
        curl_close($curl);
        // if ($err) {
        //     return "cURL Error #:" . $err;
        // } else {
        //     return $response;
        // }
    }
}
