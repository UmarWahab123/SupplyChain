<?php

namespace App\Http\Controllers\Backend;

use App\ExportStatus;
use App\Http\Controllers\Controller;
use App\Jobs\CurrencyUpdateOnProductLevelJob;
use App\Models\Common\Currency;
use App\Models\Common\Flag;
use App\Models\Common\Product;
use App\Models\Common\SupplierProducts;
use App\CurrencyHistory;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Yajra\Datatables\Datatables;
use Auth;

class CurrencyController extends Controller
{
    
  public function index()
  {
  	return $this->render('backend.currency.index');
  }

  public function getData()
  {
    $query = Currency::all();
    return Datatables::of($query)
        
    // ->addColumn('action', function ($item) { 
    //     $html_string = '<div class="icons">'.'
    //         <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon"><i class="fa fa-pencil"></i></a> 
    //       </div>';
    //     return $html_string;         
    // })

    ->addColumn('currency_name', function ($item) {     
      if($item->currency_name == null)
      {
        $html_string = '
        <span class="m-l-15 inputDoubleClick currency-name-'.$item->id.'"  data-id="'.$item->id.'" style="color:red">';
        $html_string .= '--';
        $html_string .= '</span>';

        $html_string .= '<input type="text"  name="currency_name" style="width: 100%;" class="fieldFocus d-none" value="">';
      }
      else 
      {
        $html_string = '
        <span class="m-l-15 inputDoubleClick currency-name-'.$item->id.'"  data-id="'.$item->id.'" data-fieldvalue="'.@$item->currency_name.'">';
        $html_string .= @$item->currency_name;
        $html_string .= '</span>';

        $html_string .= '<input type="text"  name="currency_name" style="width: 100%;" class="fieldFocus d-none" value="'.@$item->currency_name.'">';
      } 
      return $html_string; 
    })

    ->addColumn('currency_code', function ($item) {     

      if($item->currency_code == null)
      {
        $html_string = '
        <span class="m-l-15 inputDoubleClick currency-code-'.$item->id.'"  data-id="'.$item->id.'"  style="color:red">';
        $html_string .= '--';
        $html_string .= '</span>';

        $html_string .= '<input type="text"  name="currency_code" style="width: 100%;" class="fieldFocus d-none" value="">';
      }
      else
      {
        $html_string = '
        <span class="m-l-15 inputDoubleClick currency-code-'.$item->id.'"  data-id="'.$item->id.'" data-fieldvalue="'.@$item->currency_code.'">';
        $html_string .= @$item->currency_code;
        $html_string .= '</span>';

        $html_string .= '<input type="text"  name="currency_code" style="width: 100%;" class="fieldFocus d-none" value="'.@$item->currency_code.'">';
      }
      return $html_string; 
    })

    ->addColumn('currency_symbol', function ($item) {     

      if($item->currency_symbol == null)
      {
        $html_string = '
        <span class="m-l-15 inputDoubleClick currency-symbol-'.$item->id.'"  data-id="'.$item->id.'"  style="color:red">';
        $html_string .= '--';
        $html_string .= '</span>';

        $html_string .= '<input type="text"  name="currency_symbol" style="width: 100%;" class="fieldFocus d-none" value="">';
      }
      else
      {
        $html_string = '
        <span class="m-l-15 inputDoubleClick currency-symbol-'.$item->id.'"  data-id="'.$item->id.'" data-fieldvalue="'.@$item->currency_symbol.'">';
        $html_string .= @$item->currency_symbol;
        $html_string .= '</span>';

        $html_string .= '<input type="text"  name="currency_symbol" style="width: 100%;" class="fieldFocus d-none" value="'.@$item->currency_symbol.'">';
      }
      return $html_string; 
    })

    ->addColumn('conversion_rate', function ($item) {     
      if($item->conversion_rate == null)
      {
        $html_string = '
        <span class="m-l-15 inputDoubleClick conversion-rate-'.$item->id.'"  data-id="'.$item->id.'"  style="color:red">';
        $html_string .= '--';
        $html_string .= '</span>';

        $html_string .= '<input type="text"  name="conversion_rate" style="width: 100%;" class="fieldFocus d-none" value="">';
      }
      else
      {
        $html_string = '
        <span class="m-l-15 inputDoubleClick conversion-rate-'.$item->id.'"  data-id="'.$item->id.'" data-fieldvalue="'.number_format((float)@$item->conversion_rate, 3, '.', '').'">';
        $html_string .= number_format((float)@$item->conversion_rate, 3, '.', '');
        $html_string .= '</span>';

        $html_string .= '<input type="number"  name="conversion_rate" style="width: 100%;" class="fieldFocus d-none" value="'.number_format((float)@$item->conversion_rate, 3, '.', '').'">';
      }   
      return $html_string; 
    })
      
    ->addColumn('conversion_rate_2', function ($item) {  

      $html_string = '
      <span class="m-l-15 inputDoubleClick conversion-rate-'.$item->id.'"  data-id="'.$item->id.'" data-fieldvalue="'.($item->conversion_rate != 0 ? number_format((float)(1/$item->conversion_rate), 3, '.', '') : '').'">';
      $html_string .= ($item->conversion_rate != 0 ? number_format((float)(1/$item->conversion_rate), 3, '.', '') : '');
      $html_string .= '</span>';

      $html_string .= '<input type="number"  name="conversion_rate_2" style="width: 100%;" class="fieldFocus d-none" value="'.($item->conversion_rate != 0 ? number_format((float)(1/$item->conversion_rate), 3, '.', '') : '').'">';
           
        return $html_string; 
    })


    ->addColumn('last_updated_date', function ($item) { 
      return $item->last_updated_date != null ?  Carbon::parse(@$item->last_updated_date)->format('d/m/Y H:i:s') : '--';         
    })
    ->addColumn('last_update_by', function ($item) { 
      return $item->user != null ?  $item->user->name : '--';         
    })
            
    ->addColumn('updated_at', function ($item) { 
      return $item->updated_at != null ?  Carbon::parse(@$item->updated_at)->format('d/m/Y') : '--';        
    })
       
    ->addColumn('update_on_product_level', function ($item) { 
      $html_string = '<a class="actionicon tickIcon update_prices_on_product_level" title="Update on a Product level" href="javascript:void(0);" data-id="'.$item->id.'"><i class="fa fa-refresh"></i></a>';    

      return $html_string;   
    })
    ->setRowId(function ($item) {
      return $item->id;
    })
    ->rawColumns(['currency_name', 'currency_code', 'currency_symbol', 'conversion_rate','conversion_rate_2','created_at','updated_at','update_on_product_level'])
    ->make(true);
  }

  public function add(Request $request)
  {
    $curr = new Currency;  //create new record with all null values
    $curr->save();
    // Currency History
    $currency_history = new CurrencyHistory;
    $currency_history->user_id = Auth::user()->id;
    $currency_history->currency_id = $curr->id;
    $currency_history->column_name = 'New Currency Added';
    $currency_history->save();
    return response()->json(['success' => true]);
  }

  public function edit(Request $request)
  {
    $validator = $request->validate([
      'currency_name'  => 'required',  
      'currency_code'  => 'required|unique:currencies',  		
      'currency_symbol'=> 'required', 
      'conversion_rate'=> 'required', 
    ]);

    $cur                  = Currency::find($request->editid);
    $cur->currency_name   = $request['currency_name'];
    $cur->currency_code   = $request['currency_code'];
    $cur->currency_symbol = $request['currency_symbol'];
    $cur->conversion_rate = $request['conversion_rate'];
    $cur->save();
    return redirect()->back()->with('successmsg','Currency Updated Successfully');
  }

  public function UpdateRates(Request $request)
  {
    $currencies = Currency::all();
    foreach ($currencies as $currency) 
    {
      $old_value = '';
      if($currency->conversion_rate > 0)
      {
        $old_value =  1 / $currency->conversion_rate;
      }

      $conversion_rate = $currency->free_curr_convert($currency->currency_code,'USD');
      $currency->conversion_rate = $conversion_rate;
      $currency->save();

      $new_value = '';
      if($currency->conversion_rate > 0)
      {
        $new_value =  1 / $currency->conversion_rate;
      }

      $currency_history = new CurrencyHistory;
      $currency_history->user_id = Auth::user()->id;
      $currency_history->currency_id = $currency->id;
      $currency_history->column_name = 'Conversion Rate CUR=>THB ('.$currency->currency_name.')';
      $currency_history->old_value = $old_value;
      $currency_history->new_value = $new_value;
      $currency_history->save();

    }

    return response()->json(['success'=>true,'successmsg'=>'Currency Exchange Rates Updated Successfully']);
  }

  public function saveCurrencyData(Request $request)
  {
    $check_completed = 0;

    $currency = Currency::find($request->currency_det_id);
    foreach($request->except('currency_det_id', 'old_value') as $key => $value)
    {
      if($key == 'conversion_rate_2')
      {
        if($value != 0)
        {
          $value = 1/$value;
        }

        $currency->conversion_rate = $value;
      }
      else
      {
        $currency->$key = $value;
      }
      
    }
    $currency->save();

    // Currency History
    foreach($request->except('currency_det_id', 'old_value') as $key => $value)
    {
      $currency_history = new CurrencyHistory;
      $currency_history->user_id = Auth::user()->id;
      $currency_history->currency_id = $currency->id;
      
      if($key == 'conversion_rate_2'){
        $currency_history->column_name = 'Conversion Rate CUR=>THB ('.$currency->currency_name.')';
      }
      else if($key != 'currency_name')
      {
        $currency_history->column_name = $key .' ('.$currency->currency_name.')';
      }
      else{
        $currency_history->column_name = $key;
      }
      if ($request->old_value == 'undefined') {
        $currency_history->old_value = '--';
      }
      else{
        $currency_history->old_value = $request->old_value;
      }
      $currency_history->new_value = $value;
      $currency_history->save();
    }
    // Currency History

    if($currency->status == 0 && $check_completed == 0)
    {
      $request->id = $request->currency_det_id;
      $mark_as_complete = $this->doCurrencyCompleted($request);
      $json_response = json_decode($mark_as_complete->getContent());
      if($json_response->success == true)
      {
        $currency_complete = Currency::find($request->id);
        $currency_complete->status = 1;
        $currency_complete->save();
        $check_completed = 1;
      }
    }
    return response()->json(['completed' => $check_completed]); 
  }

  public function doCurrencyCompleted(Request $request)
  {
    if($request->id)
    {
      $currency = Currency::find($request->id);
      $missingPrams = array();
      
      if($currency->currency_name == null)
      {
          $missingPrams[] = 'Currency Name';
      }
      else if($currency->currency_code == null)
      {
        $missingPrams[] = 'Currency Code';
      }
      else if($currency->currency_symbol == null)
      {
        $missingPrams[] = 'Currency symbol';
      }
      else if($currency->conversion_rate == null)
      {
        $missingPrams[] = 'Conversion Rate';
      }
      
      if(sizeof($missingPrams) == 0)
      {
        $currency->status = 1;
        $currency->save();
        return response()->json(['success' => true]);
      }
      else
      {
        $message = implode(', ', $missingPrams);
        return response()->json(['success' => false, 'message' => $message]);
      }
    }
  }
  
  public function recursiveCurrencyStatusCheck()
  {
    $status = ExportStatus::where('type','currency_update_on_product_level')->first();
    return response()->json([
      'msg' => "Currency Update Script Is In Progress",
      'status' => $status->status,
      'exception' => $status->exception
    ]);
  }

  public function checkStatusForFirstTimeCurrencies()
  {
    $status = ExportStatus::where('type','currency_update_on_product_level')->where('user_id',Auth::user()->id)->first();
    if($status != null)
    {
      return response()->json([ 'status' => $status->status ]);
    }
    else
    {
      return response()->json([ 'status' => 0 ]);
    } 
  }

  public function currencyUpdateJobStatus(Request $request)
  {
    $currency_id = $request->currency_id;
    $status = ExportStatus::where('type','currency_update_on_product_level')->first();
     
    if($status == null)
    {
      $new          = new ExportStatus();
      $new->user_id = Auth::user()->id;
      $new->type    = 'currency_update_on_product_level';
      $new->status  = 1;
      $new->save();

      CurrencyUpdateOnProductLevelJob::dispatch($currency_id,Auth::user()->id);
      return response()->json(['msg' => "Currency update is being processed",'status' => 1,'recursive' => true]);
    }
    elseif($status->status == 1)
    {
      return response()->json(['msg' => "Currency update is already being processing",'status' => 2]);
    }
    elseif($status->status == 0 || $status->status == 2)
    {
      ExportStatus::where('type','currency_update_on_product_level')->update(['status' => 1,'exception' => null,'user_id' => Auth::user()->id]);
      CurrencyUpdateOnProductLevelJob::dispatch($currency_id,Auth::user()->id);
      return response()->json(['msg' => "Currency update is now in processing",'status' => 1,'exception' => null]);
    }
  }

  public function updatePricesOnProductLevel(Request $request)
  {
    // $flag_table = Flag::where('type','currency_update')->first();
    // if($flag_table == null)
    // {
    //   $flag_table = Flag::firstOrNew(['type'=>'currency_update']);
    //   $flag_table->currency_id = @$request->currency_id;
    //   $flag_table->save();
    //   return response()->json(['percent' => '2']);
    // }
    // elseif($flag_table->status == 0)
    // {
    //   $flag_table->currency_id = $request->currency_id;
    //   $flag_table->save();
    //   if($flag_table->total_rows != 0)
    //   {
    //     $percent = round(($flag_table->updated_rows/$flag_table->total_rows)*100);
    //   }
    //   else{
    //     $percent = '2';
    //   }
    //   return response()->json(['percent' => $percent]);
    // }
    // else
    // {
    //   $flag_table->delete();
    //   return response()->json(['success' => true]);
    // }
  }

  // Currency History
  public function getCurrencyHistory(Request $request)
    {
      $query = CurrencyHistory::orderBy('id','DESC')->get();
       return Datatables::of($query)
         ->addColumn('user_name',function($item){
              return $item->user_id != null ? $item->user->name : '--';
          })

         ->addColumn('column_name',function($item){
              return $item->column_name != null ? ucwords(str_replace('_', ' ',$item->column_name )): '--';
          })

         ->addColumn('old_value',function($item){
              return $item->old_value != null ? $item->old_value : '--';
          })

         ->addColumn('new_value',function($item){
              return $item->new_value != null ? $item->new_value : '--';
          })
           ->addColumn('created_at',function($item){
              return $item->created_at != null ? $item->created_at->format('d/m/Y h:i:s') : '--';
          })
 
            ->rawColumns(['user_name','column_name','old_value','new_value','created_at'])
            ->make(true);
       
    
    }
}
