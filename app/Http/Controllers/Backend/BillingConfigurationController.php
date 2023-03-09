<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\BillingConfiguration;
use App\Models\Common\Currency;
use App\UserLoginHistory;
use Carbon\Carbon;

class BillingConfigurationController extends Controller
{
    public function index()
    {
    	$annual = BillingConfiguration::firstOrCreate(['type' =>  'annual']);
    	$monthly = BillingConfiguration::firstOrCreate(['type' =>  'monthly']);
    	if ($annual->status == 0 && $monthly->status == 0) {
    		$annual->status = 1;
    		$annual->save();
    	}
    	$currencies = Currency::select('id', 'currency_name')->get();
    	return view('backend.billing.index', compact('annual', 'monthly', 'currencies'));
    }

    public function saveData(Request $request)
    {
    	$biling = BillingConfiguration::where('type', $request->type)->first();
		if ($request->field_name == 'annaul_official_launch_date' || $request->field_name == 'monthly_official_launch_date') {
			$date = str_replace("/","-",$request->field_value);
			$date = $date . Carbon::now()->format('H:i:s');
            $date =  date('Y-m-d H:i:s',strtotime($date));
			$biling->official_launch_date = $date;
		}
		else if ($request->field_name == 'total_users_allowed') {
			$biling->total_users_allowed = $request->field_value;
		}
		else if ($request->field_name == 'annual_billing_currency' || $request->field_name == 'monthly_billing_currency') {
			$biling->currency_id = $request->field_value;
		}
		else if ($request->field_name == 'current_annual_fee') {
			$biling->current_annual_fee = $request->field_value;
		}
		else if ($request->field_name == 'monthly_price_per_user') {
			$biling->monthly_price_per_user = $request->field_value;
		}
		else if ($request->field_name == 'no_of_free_users') {
			$biling->no_of_free_users = $request->field_value;
		}
		$biling->save();

		if ($request->field_name == 'annual_billing_currency') {
			return response()->json(['currency_name' => @$biling->currency->currency_name, 'symbol' => @$biling->currency->currency_symbol, 'current_annual_fee' => @$biling->current_annual_fee]);
		}
		else if ($request->field_name == 'monthly_billing_currency') {
			return response()->json(['currency_name' => @$biling->currency->currency_name, 'symbol' => @$biling->currency->currency_symbol, 'monthly_price_per_user' => $biling->monthly_price_per_user,]);
		}
		else if ($request->field_name == 'current_annual_fee' || $request->field_name == 'monthly_price_per_user') {
			return response()->json(['symbol' => @$biling->currency->currency_symbol]);
		}
		return response()->json(['success' => true]);
    }

   public function saveConfigType(Request $request)
   {
   		$billings = BillingConfiguration::all();
   		$annual = $billings->where('type', 'annual')->first();
   		$monthly = $billings->where('type', 'monthly')->first();
   		if ($annual != null && $monthly != null ) {
	   		if ($request->type == 'annual') {
	   			$annual->status = 1;
	   			$monthly->status = 0;
	   		}
	   		else if($request->type == 'monthly'){
	   			$annual->status = 0;
	   			$monthly->status = 1;
	   		}
	   		$annual->save();
	   		$monthly->save();
	   	}
	   	return response()->json(['success' => true]);
   }
}
