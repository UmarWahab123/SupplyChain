<?php

namespace App\Http\Controllers\Backend;

use App\GlobalAccessForRole;
use App\Http\Controllers\Controller;
use App\QuotationConfig;
use App\QuotationConfigColumn;
use DB;
use Illuminate\Http\Request;

class SearchConfiguration extends Controller
{
    public function index()
    {
        $checkSearchConfig = QuotationConfig::where('section','search_apply_configuration')->first();
    	if($checkSearchConfig)
    	{
    		$search_array_config = unserialize($checkSearchConfig->print_prefrences);
    	}
    	else
    	{
    		$search_array_config = [];
    	}

    	$checkConfig = QuotationConfig::where('section','search_configuration')->first();
        if($checkConfig)
        {
            $search_array = unserialize($checkConfig->print_prefrences);
        }
        else
        {
            $search_array = [];
        }

        return view('backend.search-config.index',compact('search_array','search_array_config'));
    }

    public function updateSearchConfig(Request $request)
    {
    	$checkConfig = QuotationConfig::where('section','search_configuration')->first();
        if($checkConfig)
        {
	        $settings = unserialize($checkConfig->print_prefrences);
	        $length = count($request->menus);
	        for($i = 0; $i < $length; $i++) 
	        {
	            if($settings['slug'][$i] == $request->menus[$i])
	            {
	             	$settings['status'][$i] = $request->menu_stat[$i];
	            }
          	}
        	$checkConfig->print_prefrences = serialize($settings);
        	$checkConfig->save();
        }
        else
        {
        	$settings = [];
        	$length = count($request->menus);
	        for($i = 0; $i < $length; $i++) 
	        {
	            $settings = ["slug" => $request->menus, "status" => $request->menu_stat];
          	}

        	$new_search_config 					 = new QuotationConfig;
        	$new_search_config->section 		 = "search_configuration";
        	$new_search_config->print_prefrences = serialize($settings);
        	$new_search_config->save();
        }

        return response(['success'=>true]);
    }

    public function updateSearchConfigColumns(Request $request)
    {
        $checkConfig = QuotationConfig::where('section','search_apply_configuration')->first();
        if($checkConfig)
        {
            $settings = unserialize($checkConfig->print_prefrences);
            $length = count($request->menus);
            for($i = 0; $i < $length; $i++) 
            {
                if($settings['slug'][$i] == $request->menus[$i])
                {
                    $settings['status'][$i] = $request->menu_stat[$i];
                }
            }
            $checkConfig->print_prefrences = serialize($settings);
            $checkConfig->save();
        }
        else
        {
            $settings = [];
            $length = count($request->menus);
            for($i = 0; $i < $length; $i++) 
            {
                $settings = ["slug" => $request->menus, "status" => $request->menu_stat];
            }

            $new_search_config                   = new QuotationConfig;
            $new_search_config->section          = "search_apply_configuration";
            $new_search_config->print_prefrences = serialize($settings);
            $new_search_config->save();
        }

        return response(['success'=>true]);
    }

}
