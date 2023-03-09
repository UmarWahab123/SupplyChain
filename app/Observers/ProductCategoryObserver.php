<?php

namespace App\Observers;

use App\Models\Common\ProductCategory;

class ProductCategoryObserver
{
    /**
     * Handle the product category "created" event.
     *
     * @param  \App\ProductCategory  $productCategory
     * @return void
     */
    public function created(ProductCategory $productCategory)
    {
        $link = '/api/productcategory-create';
        $curl_output =  $this->curl_call($link, $productCategory);
        return $curl_output;
    }

    /**
     * Handle the product category "updated" event.
     *
     * @param  \App\ProductCategory  $productCategory
     * @return void
     */
    public function updated(ProductCategory $productCategory)
    {
        $link = '/api/productcategory-update';
        $curl_output =  $this->curl_call($link, $productCategory);
        return $curl_output;
    }

    public function deleted(ProductCategory $productCategory)
    {
        $link = '/api/productcategory-delete';
        $curl_output =  $this->curl_call($link, $productCategory);
        return $curl_output;
    }

     public function curl_call($link, $data){
        $url =  config('app.ecom_url').$link;
        // dd($url);
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
        // return $response.' -- aftab ';
        $err = curl_error($curl);
        curl_close($curl);

        // if ($err) {
        //     return "cURL Error #:" . $err;
        // } else {
        //     return $response;
        // }
    }
}
