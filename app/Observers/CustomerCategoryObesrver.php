<?php

namespace App\Observers;

use App\Models\Common\CustomerCategory;

class CustomerCategoryObesrver
{
    /**
     * Handle the customer category "created" event.
     *
     * @param  \App\CustomerCategory  $customerCategory
     * @return void
     */
    public function created(CustomerCategory $customerCategory)
    {
        $link = '/api/customercategory-create';
        $curl_output =  $this->curl_call($link, $customerCategory);
        return $curl_output;
    }

    /**
     * Handle the customer category "updated" event.
     *
     * @param  \App\CustomerCategory  $customerCategory
     * @return void
     */
    public function updated(CustomerCategory $customerCategory)
    {
        $link = '/api/customercategory-update';
        $curl_output =  $this->curl_call($link, $customerCategory);
        return $curl_output;
    }

    /**
     * Handle the customer category "deleted" event.
     *
     * @param  \App\CustomerCategory  $customerCategory
     * @return void
     */
    public function deleted(CustomerCategory $customerCategory)
    {
        //
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
