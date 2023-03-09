<?php

namespace App\Http\Controllers\Backend;

use App\GlobalAccessForRole;
use App\Http\Controllers\Controller;
use App\QuotationConfig;
use App\QuotationConfigColumn;
use DB;
use Illuminate\Http\Request;
class QuotationConfigController extends Controller
{
    public function index()
    {
        // $config=QuotationConfig::get();
        $showedColumns=[0];
        $global_access=GlobalAccessForRole::where('type','Quote')->get();
        $global_access_prints=GlobalAccessForRole::where('type','quote_print')->get();

        $page_settings = QuotationConfig::where('section','quotation')->first();
        // dd(unserialize($page_settings->print_prefrences));
        // $showedColumns=[10,11,12,13,14];
        // $orderableShowedColumns=[1,2,3,4,5,6,7,8,9];

        $target_ship_date = QuotationConfig::where('section','target_ship_date')->first();
        if($target_ship_date)
        {
            $target_ship_date=unserialize($target_ship_date->print_prefrences);
        }
        else
        {
            $target_ship_date=[];
        }
        //$showedColumns=[10,11,12,13,14];
        //$orderableShowedColumns=[1,2,3,4,5,6,7,8,9];
        $quotationColumns=null;
        $quotationColumns=QuotationConfigColumn::where('section','quotation')->orderby('index')->get();
        $config=QuotationConfig::where('section','quotation')->first();
        if($config->show_columns!=null)
        {
            $showedColumns=json_decode($config->show_columns);
            //$orderableShowedColumns=array_intersect($quotationColumns->pluck('id')->toArray(),json_decode($config->show_columns));    
        }
        $default_ordering=QuotationConfigColumn::where('section','quotation')->pluck('index')->toArray();
        $default_ordering=implode(', ',$default_ordering);
        return view('backend.quotation-config.index',compact('global_access','default_ordering','showedColumns','orderableShowedColumns','quotationColumns','global_access_prints','page_settings','target_ship_date'));
    }
    public function addConfiugration(Request $request)
    {
            $config=QuotationConfig::get();
            if($config->isEmpty())
            {
                if($request->columns=='null')
                $newConfig=new QuotationConfig();
                $newConfig->section="quotation";
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
                            QuotationConfigColumn::where('section','quotation')->where('column_id',$value)->update(['index'=>$index]);
                        }
                    }
                }
                return response()->json(['success'=>true]);
            }
            else
            {
               
                $config=QuotationConfig::first();
                $config->section="quotation";
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
                            QuotationConfigColumn::where('section','quotation')->where('column_id',$value)->update(['index'=>$index]);
                        }
                    }
                }             
                return response()->json(['success'=>true]);
            }
            

    }

    public function addPageSetting(Request $request)
    {
        $checkConfig = QuotationConfig::where('section','quotation')->first();
        // $checkConfig = QuotationConfig::where('section','page_setting')->first();
        if($checkConfig)
        {
            // dd(unserialize($checkConfig->print_prefrences));
            if($checkConfig->print_prefrences != null)
            {
                $page_setting = ["slug" => $request->slug, "title" => $request->title, "status" => 0];
                $arrayPageSetting = unserialize($checkConfig->print_prefrences);
                array_push($arrayPageSetting, $page_setting);
                $checkConfig->print_prefrences = serialize($arrayPageSetting);
            }
            else
            {
                $page_setting[] = ["slug" => $request->slug, "title" => $request->title, "status" => 0];
                $checkConfig->print_prefrences = serialize($page_setting);
            }
            $checkConfig->save();
        }
        else
        {
            $page_setting[] = ["slug" => $request->slug, "title" => $request->title, "status" => 0];

            $order_config                   = new QuotationConfig;
            $order_config->section          = "quotation";
            $order_config->print_prefrences = serialize($page_setting);
            $order_config->save();
        }

        return response()->json(['success'=>true]);
    }

}
