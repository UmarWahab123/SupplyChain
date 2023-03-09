<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

class ConfigurationTemplate extends Model
{
    protected $fillable = ['notification_configuration_id','notification_type','subject','body','values'];

    public function getActualBody($body, $order, $order_history, $order_product)
    {
    	$body = str_replace('[[status_prefix]]', $order->status_prefix, $body);
    	$body = str_replace('[[ref_prefix]]', $order->ref_prefix, $body);
    	$body = str_replace('[[ref_id]]', $order->ref_id, $body);
    	$body = str_replace('[[refrence_code]]', @$order_product->product->refrence_code, $body);
    	$body = str_replace('[[user]]', Auth::user()->name, $body);
    	$body = str_replace('[[column_name]]', $order_history->column_name, $body);
    	$body = str_replace('[[old_value]]', $order_history->old_value, $body);
    	$body = str_replace('[[new_value]]', $order_history->new_value, $body);
    	$body = str_replace('[[created_at]]', $order_history->created_at, $body);
        $body = str_replace('[[product_description]]', $order_history->product->short_desc, $body);
    	return $body;
    }

    public function getActualNotificationBody($body, $order, $order_history, $order_product)
    {
    	$body = str_replace('[[status_prefix]]', $order->status_prefix, $body);
    	$body = str_replace('[[ref_prefix]]', $order->ref_prefix, $body);
    	$body = str_replace('[[ref_id]]', $order->ref_id, $body);
    	$body = str_replace('[[refrence_code]]', @$order_product->product->refrence_code, $body);
    	$body = str_replace('[[old_value]]', $order_history->old_value, $body);
    	$body = str_replace('[[new_value]]', $order_history->new_value, $body);
    	return $body;
    }

}
