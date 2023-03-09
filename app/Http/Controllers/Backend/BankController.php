<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Common\Bank;
use App\Models\Common\CompanyBank;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class BankController extends Controller
{
    public function index()
    {
    	return $this->render('backend.banks.index');
    }

    public function getData()
    {
        $query = Bank::all();

        return Datatables::of($query)
        
        ->addColumn('action', function ($item) { 
            $html_string = '<div class="icons">'.'
                <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon" title="Edit"><i class="fa fa-pencil"></i></a> 
                <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon deleteIcon delete-bank" title="Delete"><i class="fa fa-trash"></i></a>
            </div>';
            return $html_string;         
        })
        
        ->addColumn('title', function ($item) { 
            return $item->title != null ?  $item->title : '--';         
        })

        ->addColumn('description', function ($item) { 
            return $item->description != null ?  $item->description : '--';         
        })

        ->addColumn('account_no', function ($item) { 
            return $item->account_no != null ?  $item->account_no : '--';         
        })

        ->addColumn('branch', function ($item) { 
            return $item->branch != null ?  $item->branch : '--';         
        })

        ->addColumn('image', function ($item) { 
            return $item->qr_image != null ?  $item->qr_image : '--';         
        })

        ->addColumn('created_at', function ($item) { 
            return $item->created_at != null ?  Carbon::parse(@$item->created_at)->format('d/m/Y') : '--';         
        })

        ->setRowId(function ($item) {
            return $item->id;
        })
        
        ->rawColumns(['action','created_at','description','title','account_no','branch','image'])
        
        ->make(true);
    }

    public function add(Request $request)
    {
    	$validator = $request->validate([
    		'title'       => 'required',  
            'description' => 'required',  		
            'account_no'  => 'required',        
            'branch'      => 'required',        
    	]);

        // if($request->hasfile('image'))
        // {
        //     $fileNameWithExt          = $request->file('image')->getClientOriginalName();
        //     $fileName                 = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
        //     $extension                = $request->file('image')->getClientOriginalExtension();
        //     $fileNameToStore          = $fileName.'_'.time().'.'.$extension;
        //     $fileNameToStore          = str_replace(' ', '',$fileNameToStore);
        //     $path                     = $request->file('image')->move('public/uploads/',$fileNameToStore);
        // }
    	
    	$banks 				= new Bank;
    	$banks->title 		= $request->title;
        $banks->description = $request->description;
        $banks->account_no  = $request->account_no;
        $banks->branch      = $request->branch;
        
        if($request->hasfile('image'))
        {
            $image = $request->file('image');
            $new_name = rand() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads'), $new_name);
            $banks->qr_image    = $new_name;
        }
    	$banks->save();   	
  
    	return response()->json(['success' => true]);
    }

    public function getBankData(Request $request)
    {
    	$getBank =  Bank::find($request->id);
    	return response()->json(['success' => true, 'bank' => $getBank]);
    }

    public function edit(Request $request)
    {
        $validator = $request->validate([
            'title_e'       => 'required',
            'description_e' => 'required',  
            'account_no_e'  => 'required',        
            'branch_e'      => 'required', 
        ]); 
	    $bank = Bank::find($request->editid);
	    // if(strtolower($bank->title) != strtolower($request['title']))
     //  	{
	    //     $validator = $request->validate([
	    //         'title' => 'required',
	    //         'description' => 'required',  
	    //     ]);
     //  	}
	    // else
	    // {

     //    }

        $bank->title       = $request['title_e'];
        $bank->description = $request['description_e'];
        $bank->account_no  = $request['account_no_e'];
        $bank->branch      = $request['branch_e'];

        if($request->hasfile('image_e')){
            $image = $request->file('image_e');
            $new_name = rand() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads'), $new_name); 
            $bank->qr_image      = $new_name; 
        }
        $bank->save();
        return response()->json(['success' => true]);
     }

    public function delete(Request $request)
    {
    	$banks  = Bank::find($request->id);
        $checkBankStat = CompanyBank::where('bank_id',$banks->id)->get();
        if($checkBankStat->count() > 0)
        {
            $errorMsg = "This bank is bound with a Company you cannot delete this Bank !!!";
            return response()->json(['error' => true , 'errorMsg' => $errorMsg ]);
        }
        else
        {
            $banks->delete();
            return response()->json(['error' => false , 'successmsg' => 'Bank Deleted Successfully']);
        }
    }
}
