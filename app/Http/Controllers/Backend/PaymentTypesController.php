<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Common\CustomerPaymentType;
use App\Models\Common\PaymentType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class PaymentTypesController extends Controller
{
    public function index(){
    	// dd('hello');
    	return $this->render('backend.payment-types.index');
    }

    public function getData()
    {
        $query = PaymentType::all();
        return Datatables::of($query)

        ->addColumn('action', function ($item) {
            $html_string = '<div class="icons">'.'
                <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon" title="Edit"><i class="fa fa-pencil"></i></a>';
            $html_string .= '
                <a href="javascript:void(0);" class="actionicon deleteIcon deletePaymentType" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>
                </div>';
            return $html_string;
        })
        ->addColumn('visible_in_customer', function ($item) {
            $checked = $item->visible_in_customer ? "checked" : "";
            $html_string = '<input type="checkbox" '.$checked.'  class="actionicon check_visible_in_customer"/>';
            return $html_string;
        })
        ->addColumn('created_at', function ($item) {
            return $item->created_at != null ? Carbon::parse(@$item->created_at)->format('d/m/Y') : "--";
        })
        ->setRowId(function ($item) {
            return $item->id;
        })
        /*->setRowAttr([
            'visible_in_customer' => function($item) {
                return $item->visible_in_customer;
            },
        ])*/
        ->rawColumns(['action', 'visible_in_customer', 'created_at'])
        ->make(true);
    }

    public function checkPaymenttTypeOfCustomer(Request $request)
    {
        $cpt = CustomerPaymentType::where('payment_type_id', $request->id)->get();
        if($cpt->count() > 0)
        {
            return response()->json(['success' => false]);
        }
        else
        {
            return response()->json(['success' => true]);
        }
    }

    public function deletePaymentType(Request $request)
    {
        $payment_type = PaymentType::find($request->id);
        $payment_type->delete();
        return response()->json(['success' => true]);
    }

    public function add(Request $request)
    {
    	$validator = $request->validate([
			'title'       => 'required|unique:payment_types',
			'description' => 'required',
    	]);

		$payment_type              = new PaymentType;
		$payment_type->title       = $request->title;
		$payment_type->description = $request->description;
		$payment_type->visible_in_customer = $request->visible_in_customer;
    	$payment_type->save();

    	return response()->json(['success' => true]);

    }

    public function edit(Request $request)
    {
		$payment_type = PaymentType::find($request->editid);
        if(strtolower($payment_type->title) != strtolower($request['title']))
        {
         $validator = $request->validate([
            'title' => 'required|unique:payment_types',
         ]);
        }

    	$payment_type->title = $request['title'];
    	$payment_type->description = $request['description'];
        $payment_type->save();
        return response()->json(['success' => true]);
    }

    public function paymentVisibleInCustomer(Request $request){
		$payment_type = PaymentType::find($request->payment_type_id);

    	$payment_type->visible_in_customer = $request['visible_in_customer'];
        $payment_type->save();
        return response()->json(['success' => true]);
    }

}
