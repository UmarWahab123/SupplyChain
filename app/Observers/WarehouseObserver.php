<?php

namespace App\Observers;

use App\Models\Common\Warehouse;

class WarehouseObserver
{
    /**
     * Handle the warehouse "created" event.
     *
     * @param  \App\Warehouse  $warehouse
     * @return void
     */
    public function created(Warehouse $warehouse)
    {
        $link = '/api/warehouse-create';
        $curl_output =  $this->curl_call($link, $warehouse);
        return $curl_output;
    }

    /**
     * Handle the warehouse "updated" event.
     *
     * @param  \App\Warehouse  $warehouse
     * @return void
     */
    public function updated(Warehouse $warehouse)
    {
        $link = '/api/warehouse-update';
        $curl_output =  $this->curl_call($link, $warehouse);
        return $curl_output;
    }

    /**
     * Handle the warehouse "deleted" event.
     *
     * @param  \App\Warehouse  $warehouse
     * @return void
     */
    public function deleted(Warehouse $warehouse)
    {
        $link = '/api/warehouse-delete';
        $curl_output =  $this->curl_call($link, $warehouse);
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
