<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;
use App\Models\Common\State;
use App\Models\Common\Country;
use Illuminate\Support\Carbon;

class DistrcitController extends Controller
{
   
   
    public function index(){    	
    	// dd('hello');
    	$countries = Country::all();
    	return $this->render('backend.districts.index',compact('countries'));
    }

    public function getData(Request $request)
    {
        $query = State::all();

        if($request->select_country != null)
        {
        	$query = $query->where('country_id' , $request->select_country);
        }

        return Datatables::of($query)
        
            
        ->addColumn('action', function ($item) { 
            // $html_string = '<div class="icons">'.'
            //               <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon" title="Edit"><i class="fa fa-pencil"></i></a> 
            //               <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon deleteIcon delete-state"><i class="fa fa-trash"></i></a>
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
    	$validator = $request->validate([   		
    		'name' => 'required|unique:states',    		
    	]);
    	
    	$state = new State;
    	$state->name = $request->name;
    	$state->country_id = $request->country_id;
    	$state->save();   	
    	
    	return response()->json(['success' => true]);

    }

    public function edit(Request $request){
        // dd(@$request->all());
    $state = State::find($request->editid);
      //  $validator = $request->validate([
    		// 'abbrevation' => 'required|unique:countries',    		
    		// 'name' => 'required|unique:countries',    		
      //  ]);

    	$state->name = $request->name;
        $state->thai_name = $request->thai_name;
    	$state->country_id = $request->e_country_id;
    	$state->save();   	
    	
       return response()->json(['success' => true]);
     }

    public function delete(Request $request)
     {
        $state = State::find($request->id);
        $state->delete();
       return response()->json(['error' => false , 'successmsg' => 'City Deleted Successfully']);

     } 

     public function getedit(Request $request)
     {
        $state = State::find($request->id);
       return response()->json(['district' => $state ]);

     } 


}
