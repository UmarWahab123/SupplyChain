<?php

namespace App\Http\Controllers\Backend;
use App\Models\Common\Warehouse;
use App\Models\Common\Currency;
use App\GlobalAccessForRole;
use App\Http\Controllers\Controller;
use App\QuotationConfig;
use App\QuotationConfigColumn;
use DB;
use App\User;
use Illuminate\Http\Request;
use App\Models\Common\CustomerCategory;
use App\EcomerceHoliday;
use Carbon\Carbon;
use Yajra\Datatables\Datatables;

class EcommerceConfigController extends Controller
{
    public function index()
    {
        $warehouses=Warehouse::where('status',1)->get();
    	$ecommerceconfig = QuotationConfig::where('section','ecommerce_configuration')->first();
    	if($ecommerceconfig)
    	{
    		$search_array_config = unserialize($ecommerceconfig->print_prefrences);
    	}
    	else
    	{
    		$search_array_config = [];
    	}
        $customer_cat = CustomerCategory::all();
        $users        = User::where('status',1)->where('role_id',3)->whereNull('parent_id')->get();
        $currencies   = Currency::select('id','currency_name','currency_symbol')->get();
       
	   return view('backend.ecommerce-config.index',compact('search_array_config','warehouses', 'customer_cat','users','currencies'));
    }

    public function updateSearchConfig(Request $request)
    {
    	//dd($request->all());
    }

    public function updateSearchConfigColumns(Request $request)
    {
        $checkConfig = QuotationConfig::where('section','ecommerce_configuration')->first();
        if($checkConfig)
        { 
            $settings = unserialize($checkConfig->print_prefrences);
            $length = count($request->menus);
            for( $i = 0; $i < $length; $i++ ) 
            {
                if(@$settings['slug'][$i] == '')
                {
                    $settings['slug'][$i] = $request->menus[$i];
                }
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
            $new_search_config->section          = "ecommerce_configuration";
            $new_search_config->print_prefrences = serialize($settings);
            $new_search_config->save();
        }

        return response(['success'=>true]);
    }

    public function getAllHolidays(Request $request)
    {
        $query = EcomerceHoliday::orderBy('id', 'DESC');
        if($request->ajax())
        {
            return Datatables::of($query)
            ->addIndexColumn()
            ->addColumn('holiday_date', function ($item) {
                return  $item->holiday_date !== null ? Carbon::parse( $item->holiday_date)->format('d/m/Y') : 'N.A';
             })
            ->setRowId(function ($item) {
                return $item->id;
            })
            ->rawColumns(['holiday_date'])
            ->make(true);
        }
    }

    public function addEcomHolidays(Request $request)
    {
        $holiday_date = str_replace("/","-",$request->holiday_date);
        $holiday_date =  date('Y-m-d',strtotime($holiday_date));
        
        $checkIfExist = EcomerceHoliday::where('holiday_date', $holiday_date)->first();
        if($checkIfExist)
        {
            return response()->json(['success' => false]);
        }
        else
        {
            $add_new = new EcomerceHoliday;
            $add_new->holiday_date = $holiday_date;
            $add_new->save();

            return response()->json(['success' => true]);
        }
    }

}
