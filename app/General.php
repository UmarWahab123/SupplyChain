<?php

namespace App;
use App\QuotationConfig;
use Illuminate\Http\Request;

class General {

	public function __construct()
	{

	}

	public function getTargetShipDateConfig() 
	{
	    $config_target_ship_date = QuotationConfig::where('section','target_ship_date')->first();
        if($config_target_ship_date != null)
        {
            $targetShipDate = unserialize($config_target_ship_date->print_prefrences);
        }
        else
        {
            $targetShipDate = null;
        }

        return $targetShipDate;
	}
}