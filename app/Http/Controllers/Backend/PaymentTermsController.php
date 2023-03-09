<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Common\PaymentTerm;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Carbon;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\Order\Order;

class PaymentTermsController extends Controller
{
     public function index(){    	
    	// dd('hello');
    	return $this->render('backend.payment-term.index');
    }

    public function getData()
    {
        $query = PaymentTerm::all();

        return Datatables::of($query)
        
            
        ->addColumn('action', function ($item) { 
            $html_string = '<div class="icons">'.'
                          <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon"  title="Edit"><i class="fa fa-pencil"></i></a>
                          <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon deleteIcon delete-icon" title="Delete"><i class="fa fa-trash"></i></a> 
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
			'title'       => 'required|unique:payment_terms',  
			'description' => 'required',  		
    	]);
    	
		$payment_term              = new PaymentTerm;
		$payment_term->title       = $request->title;
		$payment_term->description = $request->description;
    	$payment_term->save();   	
    	
    	return response()->json(['success' => true]);

    }

     public function edit(Request $request){
		$payment_term              = PaymentTerm::find($request->editid);
        if(strtolower($payment_term->title) != strtolower($request['title']))
        {
         $validator = $request->validate([
            'title' => 'required|unique:payment_terms',
         ]);
        }

		$payment_term->title       = $request['title'];
		$payment_term->description = $request['description'];
       $payment_term->save();
       return response()->json(['success' => true , 'successmsg' => 'Payment Term Updated Successfully']);

     }

     public function deletePaymentTerm(Request $request)
     {
        $payment_term = PaymentTerm::find($request->id);

        $pos_useage = PurchaseOrder::where('payment_terms_id',$request->id)->get();
        if($pos_useage->count() > 0)
        {
            return response()->json(['error' => true , 'successmsg' => 'Cannot delete this Payment Term, It\'s in a use of Purchase Orders.']);
        }

        $order_useage = Order::where('payment_terms_id',$request->id)->get();
        if($order_useage->count() > 0)
        {
            return response()->json(['error' => true , 'successmsg' => 'Cannot delete this Payment Term, It\'s in a use of Orders.']);
        }
        
        $payment_term->delete();
        return response()->json(['error' => false , 'successmsg' => 'Payment Term deleted Successfully']);

     }

}
