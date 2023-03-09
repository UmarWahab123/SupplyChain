<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\GlobalAccessForRole;
use App\QuotationConfig;
use App\QuotationConfigColumn;
use App\Models\Common\Warehouse;

class GroupConfigController extends Controller
{
    public function index()
    {
    	$page_settings = QuotationConfig::where('section','groups_management_page')->first();
        $warehouse = Warehouse::where('is_bonded',1)->where('status',1)->get();
     
        return view('backend.group-config.index',compact('page_settings','warehouse'));

    }

    public function addProductPageSetting(Request $request)
	{
		$checkConfig = QuotationConfig::where('section','groups_management_page')->first();
		if($checkConfig)
		{
		// dd(unserialize($checkConfig->print_prefrences));
			$page_setting = ["slug" => $request->slug, "title" => $request->title, "status" => 0];

			$arrayPageSetting = unserialize($checkConfig->print_prefrences);
			array_push($arrayPageSetting, $page_setting);

			$checkConfig->print_prefrences = serialize($arrayPageSetting);
			$checkConfig->save();
		}
		else
		{
			$page_setting[] = ["slug" => $request->slug, "title" => $request->title, "status" => 0];

			$order_config = new QuotationConfig;
			$order_config->section = "groups_management_page";
			$order_config->print_prefrences = serialize($page_setting);
			$order_config->save();
		}

		return response()->json(['success'=>true]);
	}

	public function updateGroupsConfig(Request $request)
      {
      	// dd($request->all());
        $checkConfig = QuotationConfig::where('section','groups_management_page')->first();
        if($checkConfig)
        {
        $settings = unserialize($checkConfig->print_prefrences);
        $length = count($request->menus);

        for($i = 0; $i < $length; $i++)
        {
        if($settings[$i]['slug'] == $request->menus[$i])
        {
        $settings[$i]['status'] = $request->menu_stat[$i];
        }
        }
        }

        $checkConfig->print_prefrences = serialize($settings);
        $checkConfig->save();
        return response(['success'=>true]);
      }

    public function addWarehouseConfig(Request $request)
    {
        $checkConfig = QuotationConfig::where('section','warehouse_management_page')->first();
        if($checkConfig)
        {
        // dd(unserialize($checkConfig->print_prefrences));
            $page_setting = ["slug" => $request->slug, "title" => $request->title, "status" => 0];

            $arrayPageSetting = unserialize($checkConfig->print_prefrences);
            array_push($arrayPageSetting, $page_setting);

            $checkConfig->print_prefrences = serialize($arrayPageSetting);
            $checkConfig->save();
        }
        else
        {
            $page_setting[] = ["slug" => $request->slug, "title" => $request->title, "status" => 0];

            $order_config = new QuotationConfig;
            $order_config->section = "warehouse_management_page";
            $order_config->print_prefrences = serialize($page_setting);
            $order_config->save();
        }

        return response()->json(['success'=>true]);
    }

    public function updateWarehouseConfig(Request $request)
      {
        // dd($request->all());
        $checkConfig = QuotationConfig::where('section','warehouse_management_page')->first();
        if($checkConfig)
        {
        $settings = unserialize($checkConfig->print_prefrences);
        $length = count($request->menus);

        for($i = 0; $i < $length; $i++)
        {
        if($settings[$i]['slug'] == $request->menus[$i])
        {
        $settings[$i]['status'] = $request->menu_stat[$i];
        }
        }
        }

        $checkConfig->print_prefrences = serialize($settings);
        $checkConfig->save();
        return response(['success'=>true]);
      }
}
