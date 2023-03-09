<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;
use App\Models\Common\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StatusesController extends Controller
{
    public function index(){    	
    	$statuses = Status::where('parent_id',0)->get();
    	return $this->render('backend.statuses.index',compact('statuses'));
    }

    public function getData()
    {
        $query = Status::where('parent_id',0)->get();

        return Datatables::of($query)
        
            
        ->addColumn('action', function ($item) { 
            $html_string = '<div class="icons">'.'
                          <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon" title="Edit"><i class="fa fa-pencil"></i></a> 
                      </div>';
            return $html_string;         
            })

        ->addColumn('title', function ($item) {
            if($item->parent_id == 0)
            {
                $html_string = '<div>'.'
                          <a href="'.route('sub-statuses-list',$item->id).'" data-id="'.$item->id.'"  class="parent"><b>'.$item->title.'</b></a> 
                      </div>';
            }
            else
            {
                $html_string = '<div>'.$item->title.'</div>';
            }                 
            return $html_string;         
        })

        ->setRowId(function ($item) {
            return $item->id;
        })

        ->addColumn('created_at',function($item){
            return @$item->created_at != null ? $item->created_at->format('d/m/Y') : '--';
        })

        ->addColumn('updated_at',function($item){
            return @$item->updated_at != null ? $item->updated_at->format('d/m/Y') : '--';
        })

        ->rawColumns(['action','title','created_at','updated_at'])
        ->make(true);
    }

     public function add(Request $request){
     	// dd("hello");
    	$validator = $request->validate([
    		'title' => 'required',    		
    	]);
    	
    	$status = new Status;
    	$status->title = $request->title;
        $status->parent_id = $request->parent_id ? $request->parent_id : 0;
    	$status->save();   	
    	
    	return response()->json(['success' => true]);

    }

    public function edit(Request $request){
     
         $validator = $request->validate([
             'title' => 'required',
         ]);

       $status = Status::find($request->editid);
       $status->title = $request['title'];
       $status->parent_id = $request->parent_id ? $request->parent_id : 0;
       $status->save();

       return response()->json(['success' => true]);
       // return redirect()->back()->with('successmsg','Status updated successfully');
     }

     public function subStatuses($id){      
        // dd('hello');
        $statuses = Status::where('parent_id',0)->get();
        return $this->render('backend.statuses.sub-statuses',compact('id','statuses'));
    }

    public function getSubData($id)
    {
        
        $query = Status::where('parent_id',$id)->get();
        return Datatables::of($query)
        
            
        ->addColumn('action', function ($item) { 
            $html_string = '<div class="icons">'.'
                          <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon" title="Edit"><i class="fa fa-pencil"></i></a> 
                      </div>';
            return $html_string;         
            })

        ->addColumn('title', function ($item) {
            $html_string = '<div>'.$item->title.'</div>';
            return $html_string;

                 
            })
        ->addColumn('created_at',function($item){
            return @$item->created_at != null ? $item->created_at->format('d/m/Y') : '--';
        })

        ->addColumn('updated_at',function($item){
            return @$item->updated_at != null ? $item->updated_at->format('d/m/Y') : '--';
        })
             ->setRowId(function ($item) {
             return $item->id;
         })
            ->rawColumns(['action','title','created_at','updated_at'])
            ->make(true);
    

    }
}
