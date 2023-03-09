<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\GlobalAccessForRole;
use App\QuotationConfig;
use App\QuotationConfigColumn;
use App\Models\Common\PurchaseOrders\PoVatConfiguration;
class POConfigController extends Controller
{
    public function index()
    {
        $showedColumns=[];
        $page_settings = QuotationConfig::where('section','purchase_order')->first();
        $quotationColumns=null;
        $quotationColumns=QuotationConfigColumn::where('section','purchase_order')->orderby('index')->get();
        $config=QuotationConfig::where('section','purchase_order')->first();
        if($config->show_columns!=null)
        {
            $showedColumns=json_decode($config->show_columns);

            //$orderableShowedColumns=array_intersect($quotationColumns->pluck('id')->toArray(),json_decode($config->show_columns));    
        }
        $default_ordering=QuotationConfigColumn::where('section','purchase_order')->pluck('index')->toArray();
        $default_ordering=implode(', ',$default_ordering);
        return view('backend.po-config.index',compact('global_access','default_ordering','showedColumns','orderableShowedColumns','quotationColumns'));
    }
    public function addConfiugration(Request $request)
    {
        // dd($request->all());
            $config=QuotationConfig::where('section','purchase_order')->first();
            if($config==null)
            {
                $newConfig=new QuotationConfig();
                $newConfig->section="purchase_order";
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
                            QuotationConfigColumn::where('id',$value)->update(['index'=>$index]);
                        }
                    }
                }
                return response()->json(['success'=>true]);
            }
            else
            {
                $config=QuotationConfig::where('section','purchase_order')->first();
                $config->section="purchase_order";
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
                        foreach(array_combine($request->index,$request->ids) as $index =>$id)
                        {
                            QuotationConfigColumn::where('column_id',$id)->where('section','purchase_order')->update(['index'=>$index]);
                        }
                    }
                }             
                return response()->json(['success'=>true]);
            }
    }

    public function savePoVatConfiguration(Request $request)
    {
        // dd($request->all());
        $configuration = PoVatConfiguration::first();
        if($configuration == null)
        {
            $configuration = new PoVatConfiguration;
        }

        if($request->purchasing_vat)
        {
            $configuration->purchasing_vat = 1;
        }
        else
        {
            $configuration->purchasing_vat = 0;
        }

        if($request->unit_price_plus_vat)
        {
            $configuration->unit_price_plus_vat = 1;
        }
        else
        {
            $configuration->unit_price_plus_vat = 0;
        }

        if($request->total_amount_without_vat)
        {
            $configuration->total_amount_without_vat = 1;
        }
        else
        {
            $configuration->total_amount_without_vat = 0;
        }

        if($request->total_amount_inc_vat)
        {
            $configuration->total_amount_inc_vat = 1;
        }
        else
        {
            $configuration->total_amount_inc_vat = 0;
        }

        $configuration->save();

        return response()->json(['success' => true]);
    }
}
