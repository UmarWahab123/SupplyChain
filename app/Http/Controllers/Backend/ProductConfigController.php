<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\GlobalAccessForRole;
use App\QuotationConfig;
use App\QuotationConfigColumn;
use App\Models\Common\CustomerCategory;
use App\Variable;

class ProductConfigController extends Controller
{
    public function index()
    {
    	$page_settings = QuotationConfig::where('section','products_management_page')->first();

    	$getCategories = CustomerCategory::where('is_deleted',0)->get();

        return view('backend.products-config.index',compact('page_settings','getCategories'));

    }

	public function addProductPageSetting(Request $request)
	{
		$checkConfig = QuotationConfig::where('section','products_management_page')->first();
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
			$order_config->section = "products_management_page";
			$order_config->print_prefrences = serialize($page_setting);
			$order_config->save();
		}

		return response()->json(['success'=>true]);
	}

	public function UpdateCustomerCategoryConfig(Request $request)
    {
    	// dd($request->all());
        $customerCategory = CustomerCategory::where('id',$request->id)->first();

        if($customerCategory != null)
        {
        	if($request->title == 'fixed')
        	{
        		$customerCategory->show = $request->new_select_value;
        	}
        	else
        	{
        		$customerCategory->suggested_price_show = $request->new_select_value;
        	}
        	$customerCategory->save();
        	return response()->json(['success' => true]);
        }
        else
        {
        	return response()->json(['success' => false]);

        }
    }

    public function addProductDetailPageSetting(Request $request) {
        $global_terminologies = Variable::where('slug', $request->title)->first();
        $slug = $request->title;
        $title = $global_terminologies['standard_name'];

        if($request->pages == 'product_information') {
            $checkConfig = QuotationConfig::where('section','product_detail_page')->first();
            if($checkConfig)
            {
                $page_setting = ["slug" => $slug, "title" => $title, "status" => 0];
                $arrayPageSetting = unserialize($checkConfig->print_prefrences);
                array_push($arrayPageSetting, $page_setting);

                $checkConfig->print_prefrences = serialize($arrayPageSetting);
                $checkConfig->save();
            }
            else
            {
                $page_setting[] = ["slug" => $slug, "title" => $title, "status" => 0];

                $order_config = new QuotationConfig;
                $order_config->section = "product_detail_page";
                $order_config->print_prefrences = serialize($page_setting);
                $order_config->save();
            }

            return response()->json(['success'=>true]);

        } else if($request->pages == 'supplier_information') {
            $checkConfig = QuotationConfig::where('section','product_detail_page_supplier_detail')->first();
            if($checkConfig)
            {
                $page_setting = ["slug" => $slug, "title" => $title, "status" => 0];
                $arrayPageSetting = unserialize($checkConfig->print_prefrences);
                array_push($arrayPageSetting, $page_setting);

                $checkConfig->print_prefrences = serialize($arrayPageSetting);
                $checkConfig->save();
            }
            else
            {
                $page_setting[] = ["slug" => $slug, "title" => $title, "status" => 0];

                $order_config = new QuotationConfig;
                $order_config->section = "product_detail_page_supplier_detail";
                $order_config->print_prefrences = serialize($page_setting);
                $order_config->save();
            }

            return response()->json(['success'=>true]);
        } else {
            return response()->json(['success'=>false]);
        }

    }

    public function updateProductDetailConfig(Request $request) {
        $checkConfig = QuotationConfig::where('section','product_detail_page')->first();
        if($checkConfig)
        {
            $settings = unserialize($checkConfig->print_prefrences);
            $length = count($request->menus);
            for($i = 0; $i < $length; $i++)
            {
                if(isset($settings[$i]) && isset($request->menus[$i]))
                {
                if($settings[$i]['slug'] == $request->menus[$i])
                {
                    $settings[$i]['status'] = $request->menu_stat[$i];
                }
                }
            }
        }

        $checkConfig->print_prefrences = serialize($settings);
        $checkConfig->save();
        return response(['success'=>true]);
    }

    public function updateSupplierDetailConfig(Request $request) {
        $checkConfig = QuotationConfig::where('section','product_detail_page_supplier_detail')->first();
        if($checkConfig)
        {
            $settings = unserialize($checkConfig->print_prefrences);

            $length = count($request->menus);
            for($i = 0; $i < $length; $i++)
            {
                if(isset($settings[$i]) && isset($request->menus[$i]))
                {
                    if($settings[$i]['slug'] == $request->menus[$i])
                    {
                        $settings[$i]['status'] = $request->menu_stat[$i];
                    }
                }
            }
        }

        $checkConfig->print_prefrences = serialize($settings);
        $checkConfig->save();
        return response(['success'=>true]);
    }

    public function ProductDetailConfig() {
        $product_detail_page = QuotationConfig::where('section','product_detail_page')->first();
        $product_detail_page_supplier_detail = QuotationConfig::where('section','product_detail_page_supplier_detail')->first();
        $global_terminologies = Variable::where('section', 'Product Information')->get();

        return view('backend.products-config.product-detail-config',compact('global_terminologies','product_detail_page', 'product_detail_page_supplier_detail'));
    }

    public function getSelectedSectionConfig(Request $request)
    {
        if ($request->value == "0" || $request->value == 0) {
            $config = QuotationConfig::where('section','product_detail_page')->first();
        }
        else if ($request->value == "1" || $request->value == 1) {
            $config = QuotationConfig::where('section','product_detail_page_supplier_detail')->first();
        }
        $settings = unserialize($config->print_prefrences);
        $html = '<option>Select Column</option>';
        foreach ($settings as $value) {
            $html .= '<option value="'.$value['slug'].'">'.$value['title'].'</option>';
        }
        return response()->json(['success' => true, 'html' => $html]);
    }

    public function deleteProductDetailConfig(Request $request)
    {
        if ($request->pages == "0" || $request->pages == 0) {
            $config = QuotationConfig::where('section','product_detail_page')->first();
        }
        else if ($request->pages == "1" || $request->pages == 1) {
            $config = QuotationConfig::where('section','product_detail_page_supplier_detail')->first();
        }
        if ($config) {
            $settings = unserialize($config->print_prefrences);
            $new_array = [];
            foreach ($settings as $key => $value) {
                if ($value['slug'] == $request->title) {
                    unset($settings[$key]);
                    break;
                }
            }
            foreach ($settings as $value){
                $array = ["slug" => $value['slug'], "title" => $value['title'], "status" => $value['status']];
                array_push($new_array, $array);
            }
            $config->print_prefrences = serialize($new_array);
            $config->save();
            return response()->json(['success' => true]);
        }
    }
}
