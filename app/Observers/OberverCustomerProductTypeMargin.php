<?php

namespace App\Observers;

use App\Models\Common\CustomerTypeProductMargin;

class OberverCustomerProductTypeMargin
{
    /**
     * Handle the customer type product margin "created" event.
     *
     * @param  \App\CustomerTypeProductMargin  $customerTypeProductMargin
     * @return void
     */
    public function created(CustomerTypeProductMargin $customerTypeProductMargin)
    {
        $link = '/api/customertypepromargin-update';
        $curl_output =  $this->curl_call($link, $customerTypeProductMargin);
        return $curl_output;
    }

    /**
     * Handle the customer type product margin "updated" event.
     *
     * @param  \App\CustomerTypeProductMargin  $customerTypeProductMargin
     * @return void
     */
    public function updated(CustomerTypeProductMargin $customerTypeProductMargin)
    {
        $link = '/api/customertypepromargin-update';
        $curl_output =  $this->curl_call($link, $customerTypeProductMargin);
        return $curl_output;
    }

    /**
     * Handle the customer type product margin "deleted" event.
     *
     * @param  \App\CustomerTypeProductMargin  $customerTypeProductMargin
     * @return void
     */
    public function deleted(CustomerTypeProductMargin $customerTypeProductMargin)
    {
        //
    }

     public function curl_call($link, $data){
        $url =  config('app.ecom_url').$link;
        echo $url;
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
