<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Http\Controllers\Controller;
use App\Models\Common\Country;
use Illuminate\Support\Carbon;

class CountryController extends Controller
{
   
    public function index(){    	
    	// dd('hello');
    	return $this->render('backend.countries.index');
    }

    public function getData()
    {
        $query = Country::all();

        return Datatables::of($query)
        
            
        ->addColumn('action', function ($item) { 
            // $html_string = '<div class="icons">'.'
            //               <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon"><i class="fa fa-pencil"></i></a> 
            //               <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon deleteIcon delete-country"><i class="fa fa-trash"></i></a>
            //           </div>';
            $html_string = '<div class="icons">'.'
                          <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon" title="Edit"><i class="fa fa-pencil"></i></a> 
                      </div>';
            return $html_string;         
            })
        ->addColumn('created_at', function ($item) { 
            return $item->created_at != null ?  Carbon::parse(@$item->created_at)->format('d/m/Y') : '--';         
            })
            
        ->addColumn('updated_at', function ($item) { 
        return $item->updated_at != null ?  Carbon::parse(@$item->updated_at)->format('d/m/Y') : '--';        
        })
        	 ->setRowId(function ($item) {
             return $item->id;
         })
            ->rawColumns(['action','created_at','updated_at'])
            ->make(true);
    }

     public function add(Request $request){
     	// dd("hello");
    	$validator = $request->validate([
    		'abbrevation' => 'required|unique:countries',    		
    		'name' => 'required|unique:countries',    		
    	]);
    	
    	$country = new Country;
    	$country->abbrevation = $request->abbrevation;
    	$country->name = $request->name;
    	$country->save();   	
    	
    	return response()->json(['success' => true]);

    }

    public function edit(Request $request){
    $country = Country::find($request->editid);
      //  $validator = $request->validate([
    		// 'abbrevation' => 'required|unique:countries',    		
    		// 'name' => 'required|unique:countries',    		
      //  ]);

       	$country->abbrevation = $request->abbrevation;
      $country->name = $request->name;
    	$country->thai_name = $request->thai_name;
    	$country->save();   	
    	
       return response()->json(['success' => true]);
     }

    public function delete(Request $request)
     {
        $country = Country::find($request->id);
        $country->delete();
       return response()->json(['error' => false , 'successmsg' => 'Country Deleted Successfully']);

     } 

}
