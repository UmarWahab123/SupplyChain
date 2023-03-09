<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Datatables;
use App\CourierType;
use Carbon\Carbon;

class CourierTypeController extends Controller
{
    public function addCourierType(Request $request)
    {
		if ($request->id != null) {
			$query = CourierType::find($request->id);
			$query->type = $request->type;
			$query->save();
			return response()->json(['success' => true, 'msg' => 'Courier Type Updated Successfully', 'type' =>$query->toArray()]);
		}
		$validator = $request->validate([
		'type' => 'required|unique:courier_types',
		]);

		$courierType = new CourierType;
    	$courierType->type = $request->type;
    	$courierType->status = 1;
		$courierType->save();
		return response()->json(['success' => true, 'msg' => 'Courier Type Added Successfully', 'type' => $courierType->toArray()]);
	}

	public function getCourierTypesData()
	{
		$query = CourierType::where('status',1);
		return Datatables::of($query)
		->addColumn('action', function ($item) {
		$html_string = '<div class="icons">'.'
		<a href="javascript:void(0);" data-id="'.$item->id.'" class="actionicon tickIcon btn-Edit" title="Edit" data-toggle="modal" data-target="#addCourierType"><i class="fa fa-pencil"></i></a>
		<a href="javascript:void(0);" data-id="'.$item->id.'" class="actionicon deleteIcon btn-Delete" title="Delete"><i class="fa fa-ban"></i></a>
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

	public function delete(Request $request)
	{
		$query = CourierType::find($request->id);
		if ($query->couriers->count() == 0) {
			$query->status = 0;
			$query->save();
			return response()->json(['success' => true, 'msg' => 'Courier Type Deleted Successfully', 'type' => $query->toArray()]);
		}
		else{
			return response()->json(['success' => false, 'msg' => 'Cannot allowed to delete. Courier Type is bounded to Courier']);
		}
	}
}
