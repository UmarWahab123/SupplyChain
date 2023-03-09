<?php

namespace App\Http\Controllers\Backend;

use App\Models\Pos\PosIntegration;
use Illuminate\Http\Request;
use Yajra\DataTables\Datatables;

class POSIntegrationController
{

    public function PosData(Request $request)
    {
        if ($request->ajax()) {

            $data = PosIntegration::where('status',$request->status_id)->get();
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($item){
                        $btn = '<a href="javascript:void(0);" data-pos_status="'.$item->status.'" data-id="'.$item->id.'" class="actionicon editIcon" title="Edit"><i class="fa fa-pencil"></i></a>';
                        return $btn;
                    })
                    ->editColumn('created_at', function ($user) {
                        return $user->created_at->format('Y/m/d');
                    })
                    ->editColumn('status', function ($user) {
                        return ($user->status==1) ? "Active" : "InActive";
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }

        return view('backend.admin.index');
    }

    public function inActiveStatus(Request $request)
    {
        if($request->status == 1)
        {
            PosIntegration::where('id',$request->id)->update(['status'=>0]);
        }
        else
        {
            PosIntegration::where('id',$request->id)->update(['status'=>1]);
        }
        return response()->json(['status'=>200]);
    }
}
