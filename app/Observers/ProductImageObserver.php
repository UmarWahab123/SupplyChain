<?php

namespace App\Observers;

use App\Models\Common\ProductImage;
use App\Helpers\GuzzuleRequestHelper;
use App\Models\Common\Deployment;

class ProductImageObserver
{
    /**
     * Handle the product image "created" event.
     *
     * @param  \App\ProductImage  $productImage
     * @return void
     */
    public function created(ProductImage $productImage)
    {

        $link = '/api/productimage-create';
        $curl_output =  $this->curl_call($link, $productImage);
        if(@$productImage->woocommerce_enabled){
            $token = config('app.external_token');
            $deployment = Deployment::where('type','woocommerce')->first();
            $url = @$deployment->url.'/wp-json/supplychain-woocommerce/v1/update-product/';
            $body = ['product_id'=> $productImage->product_id];
            $method = 'POST';
            $response = GuzzuleRequestHelper::guzzuleRequest($token,$url,$method,$body);
          }
        return $curl_output;
    }

    /**
     * Handle the product image "updated" event.
     *
     * @param  \App\ProductImage  $productImage
     * @return void
     */
    public function updated(ProductImage $productImage)
    {
        $link = '/api/productimage-update';
        $curl_output =  $this->curl_call($link, $productImage);
        return $curl_output;
    }

    /**
     * Handle the product image "deleted" event.
     *
     * @param  \App\ProductImage  $productImage
     * @return void
     */
    public function deleted(ProductImage $productImage)
    {
        $link = '/api/productimage-delete';
        $curl_output =  $this->curl_call($link, $productImage);
        $curl_output =  $this->curl_call($link, $productImage);
        if(@$productImage->woocommerce_enabled){
            $token = config('app.external_token');
            $deployment = Deployment::where('type','woocommerce')->first();
            $url = @$deployment->url.'/wp-json/supplychain-woocommerce/v1/delete-product/';
            $body = ['product_id'=> $productImage->product_id];
            $method = 'POST';
            $response = GuzzuleRequestHelper::guzzuleRequest($token,$url,$method,$body);
          }
        return $curl_output;
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
