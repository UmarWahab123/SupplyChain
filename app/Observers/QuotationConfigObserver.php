<?php

namespace App\Observers;

use App\QuotationConfig;

class QuotationConfigObserver
{
    /**
     * Handle the quotation config "created" event.
     *
     * @param  \App\QuotationConfig  $quotationConfig
     * @return void
     */
    public function created(QuotationConfig $quotationConfig)
    {
        $link = '/api/quotationconfig-create';
        $curl_output =  $this->curl_call($link, $quotationConfig);
        return $curl_output;
    }

    /**
     * Handle the quotation config "updated" event.
     *
     * @param  \App\QuotationConfig  $quotationConfig
     * @return void
     */
    public function updated(QuotationConfig $quotationConfig)
    {
        $link = '/api/quotationconfig-update';
        $curl_output =  $this->curl_call($link, $quotationConfig);
        return $curl_output;
    }

    /**
     * Handle the quotation config "deleted" event.
     *
     * @param  \App\QuotationConfig  $quotationConfig
     * @return void
     */
    public function deleted(QuotationConfig $quotationConfig)
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
