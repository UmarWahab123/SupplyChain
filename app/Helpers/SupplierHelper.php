<?php

namespace App\Helpers;

use App\Models\Common\Supplier;
use App\Models\Common\SupplierContacts;
use App\Http\Controllers\Purchasing\SupplierController;

class SupplierHelper
{
	public static function saveSuppDataSuppDetail($request)
	{
		$completed = 0;
		$supp_detail = Supplier::where('id',$request->supplier_id)->first();

		foreach($request->except('supplier_id') as $key => $value)
		{
			if($key == 'country')
			{
				$supp_detail->$key = $value;
				$supp_detail->state = NULL;
			}
			else if($key == 'reference_number')
			{
                $supplier = Supplier::where('reference_number', $value)->first();
                if ($supplier) {
                    return response()->json(['success' => false, 'supplier_code_exists' => true]);
                }
				$supp_detail->$key = $value;
			}
			if($value != '')
			{
				$supp_detail->$key = $value;
			}
		}
		$supp_detail->save();

		if($supp_detail->status == 0)
		{
			$request->id = $request->supplier_id;
			$mark_as_complete = (new SupplierController)->doSupplierCompleted($request);
			$json_response = json_decode($mark_as_complete->getContent());
			if($json_response->success == true)
			{
				$supplier_complete = Supplier::find($request->id);
				$supplier_complete->status = 1;
				$supplier_complete->save();
				$completed = 1;
			}
		}
		return response()->json(['success' => true,'completed' => $completed]);
	}

	public static function saveSuppContactsData($request)
	{
		$supp_note_detail = SupplierContacts::where('id',$request->id)->where('supplier_id',$request->supplier_id)->first();

		foreach($request->except('supplier_id','id') as $key => $value)
		{
			if($value != '')
			{
				$supp_note_detail->$key = $value;
			}
		}
		$supp_note_detail->save();
		return response()->json(['success' => true]);
	}


}
