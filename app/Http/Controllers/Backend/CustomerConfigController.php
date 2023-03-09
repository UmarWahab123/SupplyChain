<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Variable;
use App\QuotationConfig;

class CustomerConfigController extends Controller
{
    public function CustomerDetailConfig() {
        $customer_detail_page = QuotationConfig::where('section','customer_detail_page')->first();
        if($customer_detail_page == null) {
            $page_setting[] = ["slug" => 'customer_reference_code', "title" => 'Customer Reference Code', "status" => 0];
            $order_config = new QuotationConfig;
            $order_config->section = "customer_detail_page";
            $order_config->print_prefrences = serialize($page_setting);
            $order_config->save();
        }

        $global_terminologies_all = Variable::where('section', 'Customer detail')->get();
        return view('backend.customer-config.customer-detail-config',compact('global_terminologies_all','customer_detail_page'));
    }

    public function addConfiugration(Request $request)
    {
            $config=QuotationConfig::where('type','customer_detail_page')->first();

            if($config->isEmpty())
            {
                $newConfig=new QuotationConfig();
                $newConfig->section="customer_detail_page";
                $newConfig->display_prefrences=json_encode($request->value);
                if($request->columns==null)
                $newConfig->show_columns=null;
                else
                $newConfig->show_columns=json_encode($request->columns);
                if($newConfig->save())
                {
                    if($request->update=='true')
                    {
                        foreach(array_combine($request->index,$request->orderAbleValue) as $index =>$value)
                        {
                            QuotationConfigColumn::where('section','customer_detail_page')->where('column_id',$value)->update(['index'=>$index]);
                        }
                    }
                }
                return response()->json(['success'=>true]);
            }
            else
            {

                $config=QuotationConfig::where('type','customer_detail_page')->first();
                $config->section="customer_detail_page";
                if($request->update=='true')
                $config->display_prefrences=json_encode($request->value);
                if($request->columns==null)
                $config->show_columns=null;
                else
                $config->show_columns=json_encode($request->columns);
                if($config->save())
                {
                    if($request->update=='true')
                    {
                        foreach(array_combine($request->index,$request->orderAbleValue) as $index =>$value)
                        {
                            QuotationConfigColumn::where('section','customer_detail_page')->where('column_id',$value)->update(['index'=>$index]);
                        }
                    }
                }
                return response()->json(['success'=>true]);
            }


    }

    public function updateCustomerDetailConfig(Request $request) {
        $checkConfig = QuotationConfig::where('section','customer_detail_page')->first();
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
}
