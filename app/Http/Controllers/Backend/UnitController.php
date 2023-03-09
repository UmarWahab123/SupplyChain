<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Common\Product;
use App\Models\Common\Unit;
use App\UnitHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\Datatables\Datatables;
use Auth;


class UnitController extends Controller
{
    public function index(){    	
    	// dd('hello');
    	return $this->render('backend.units.index');
     }

    public function getData()
    {
        $query = Unit::all();

        return Datatables::of($query)
        
            
        ->addColumn('action', function ($item) { 
            $html_string = '<a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon deleteIcon delete-unit" title="Delete"><i class="fa fa-trash"></i></a>
                      </div>';
            return $html_string;         
            })
         ->addColumn('title', function ($item) { 
              
               $html = '<span class="inputDoubleClick font-weight-bold" data-id="'.$item->id.'" data-fieldvalue="'.$item->title.'">'.($item->title != null ? $item->title : "--" ).'</span><input type="text" name="title" data-id="'.$item->id.'" value="'.$item->title.'" class="title form-control input-height d-none fieldFocus" style="width:100px" maxlength="10" >'; 
                 return $html;       
          })

         ->addColumn('decimal_places', function ($item) { 
              
               $html = '<span class="inputDoubleClick font-weight-bold" data-id="'.$item->id.'" data-fieldvalue="'.$item->decimal_places.'">'.($item->decimal_places != null ? $item->decimal_places : "--" ).'</span><input type="text" name="decimal_places" data-id="'.$item->id.'" value="'.$item->decimal_places.'" class="title form-control input-height d-none fieldFocus" style="width:100px" maxlength="5" >'; 
                 return $html;       
          })
        ->addColumn('created_at', function ($item) { 
            return $item->created_at != null ?  Carbon::parse(@$item->created_at)->format('d/m/Y') : '--';         
            })
            
        ->addColumn('updated_at', function ($item) { 
            return $item->updated_at != null ?  Carbon::parse(@$item->updated_at)->format('d/m/Y') : '--';        
            })
    	   ->setRowId(function ($item){
             return $item->id;
            })
         ->rawColumns(['action','title','decimal_places','created_at','updated_at'])
         ->make(true);
    }

     public function add(Request $request){
     	// dd("hello");
      	$validator = $request->validate([
      		    'title' => 'required|unique:units',  
              'decimal_places' => 'required|size:1',  		
      	]);
      	
  	        $unit = new Unit;
  	        $unit->title = $request->title;
            $unit->decimal_places = $request->decimal_places;
            $unit->save();  
            $unit['delete'] = false;
            $link = '/api/create-product-unit';
            $curl_output =  $this->curl_call($link, $unit);   	
      	
      	return response()->json(['success' => true]);

    }

    public function edit(Request $request){
     
      $unit = Unit::find($request->id);
      foreach($request->except('id','old_value') as $key => $value)
        {
                
            if($key=='title')
            {
              if(strtolower($unit->title) != strtolower($value))
                  {
                    $validator = $request->validate([
                        'title' => 'required|unique:units'
                    ]);
                  }
                   $key='Title';
                   $unit->title = $value;
                   
            }
             elseif($key=='decimal_places')
            {       
                   $key='Decimals';
                   $validator = $request->validate([
                        'decimal_places' => 'required|size:1'  
                    ]);
                   $unit->decimal_places = $value;
            }
                   $unit->save(); 
                  //CreateUnitHistoriesTable
                  $unit_history = new UnitHistory;
                  $unit_history->user_id = Auth::user()->id;
                  $unit_history->unit_id =  $request->id; 
                  $unit_history->old_value = $request->old_value;
                  $unit_history->column_name = $key;
                  $unit_history->new_value = $value;
                  $unit_history->save();

        }
        $unit['delete'] = false;
            $link = '/api/create-product-unit';
            $curl_output =  $this->curl_call($link, $unit);
           return response()->json(['success' => true]);
     }
     

    public function delete(Request $request)
    {
      $unit = Unit::find($request->id);
      $unit_to_be_delete = $unit;

      $checkUnitExistance = Product::where('buying_unit',$unit->id)->orWhere('selling_unit',$unit->id)->get();
      if($checkUnitExistance->count() > 0)
      {
        return response()->json(['error' => true , 'successmsg' => 'You cannot delete this unit, this unit has binding with the Products.']);  
      }
      else
      {
        $delHistory = UnitHistory::where('unit_id', $request->id)->delete();
        $unit->delete();
        $unit_to_be_delete['delete'] = true;
        $link = '/api/create-product-unit';

        $curl_output =  $this->curl_call($link, $unit_to_be_delete);
        return response()->json(['error' => false , 'successmsg' => 'Unit Deleted Successfully']);
      }
    }

    public function getUnitHistory(Request $request)
    {
      $query = UnitHistory::with('user','unit_detail')->orderBy('id','DESC')->get();
       return Datatables::of($query)
         ->addColumn('user_name',function($item){
              return @$item->user_id != null ? $item->user->name : '--';
          })

         ->addColumn('item',function($item){

            return @$item->unit_id != null ? $item->unit_detail->title : '--';

          })


         ->addColumn('column_name',function($item){
              return @$item->column_name != null ? $item->column_name : '--';
          })

         ->addColumn('old_value',function($item){
              return @$item->old_value != null ? $item->old_value : '--';
          })

         ->addColumn('new_value',function($item){
            return @$item->new_value != null ? $item->new_value : '--';
          })
           ->addColumn('created_at',function($item){
              return @$item->created_at != null ? $item->created_at->format('d/m/Y H:i:s') : '--';
          })
          // ->setRowId(function ($item) {
          //   return $item->id;
          // })

            ->rawColumns(['user_name','item','column_name','old_value','new_value','created_at'])
            ->make(true);

    } 

    public function curl_call($link, $data){
        try
        {
        $url =  config('app.ecom_url').$link;
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/json",
            "postman-token: 08f91779-330f-bf8f-1a64-d425e13710f9"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        return $response;
     }
     catch(\Excepion $e)
     {
        return response()->json(["error" => $e->getMessage()]);
     }
    }
}
