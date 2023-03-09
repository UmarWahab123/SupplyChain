<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Common\Brand;
use Yajra\Datatables\Datatables;

class BrandController extends Controller
{
    public function index(){    	
    	// dd('hello');
    	return $this->render('backend.brands.index');
    }

    public function getData()
    {
        $query = Brand::all();

        return Datatables::of($query)
        
            
        ->addColumn('action', function ($item) { 
            $html_string = '<div class="icons">'.'
                          <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon" title="Edit"><i class="fa fa-pencil"></i></a> 
                      </div>';
            return $html_string;         
            })
        	 ->setRowId(function ($item) {
             return $item->id;
         })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function add(Request $request){

    	$validator = $request->validate([
    		'title' => 'required|unique:brands',    		
        ]);
    	
    	$Brand = new Brand;
    	$Brand->title = $request->title;
    	$Brand->save();   	
    	
    	return response()->json(['success' => true]);

    }

    public function edit(Request $request){
     
     $validator = $request->validate([
         'title' => 'required|unique:brands',
     ]);

       $Brand = Brand::find($request->editid);
       $Brand->title = $request['title'];
       $Brand->save();
       return response()->json(['success' => true]);
     }
}
