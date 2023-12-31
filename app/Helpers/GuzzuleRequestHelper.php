<?php
namespace App\Helpers;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
/**
 * 
 */
class GuzzuleRequestHelper
{
	public static function guzzuleRequest($token = null, $url, $method, $data = null, $with_header = false)
	{
        $client = new Client(['verify' => false]);
        $headers['Accept'] = 'application/json';
        if($token)
        {
            $headers['Authorization'] = 'Bearer ' . $token;
        }
        
        if ($data) 
        {
            $response = $client->request($method,$url,[
                'headers' => $headers,
                'json' => $data
            ]);
        }
        else{
            $response = $client->request($method,$url,[
                'headers' => $headers
            ]);
        }
		$store_name = $response;
        // dd($store_name);
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();
        $content = json_decode($response->getBody(), true);
        if($with_header)
        {
            return [
                'content' => $content,
                'headers' => $response->getHeaders(),
            ];
        }
        return $content;
	}
}