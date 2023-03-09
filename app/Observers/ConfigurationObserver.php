<?php

namespace App\Observers;

use App\Models\Common\Configuration;

class ConfigurationObserver
{
    /**
     * Handle the configuration "created" event.
     *
     * @param  \App\Configuration  $configuration
     * @return void
     */
    // public function created(Configuration $configuration)
    // {
        

    // }

    /**
     * Handle the configuration "updated" event.
     *
     * @param  \App\Configuration  $configuration
     * @return void
     */
    public function updated(Configuration $configuration)
    {   
        
        $link = '/api/configuration-update';
        $curl_output =  $this->curl_call($link, $configuration);
        return $curl_output;
    }

    public function curl_call($link, $data){
        $url =  config('app.ecom_url').$link;
        // echo $url;
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
        // dd($response);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            // return "cURL Error #:" . $err;
        // } else {
            // return $response;
        }
    }

}
