<?php

namespace App\Helpers;
use App\Models\Pos\PosIntegration;
use App\Models\Common\Warehouse;
use Illuminate\Support\Str;

class PosControllerHelper {

    public static function store($request) {
        $warehouse_name = Warehouse::where('id','=',$request->warehouse_id)->first();
        $check_device = PosIntegration::where('device_name','=',$request->device_name)->first();
        if($check_device != null) {
            return response()->json(['success'=>false, 'message'=>'The device is already registered !']);
        } else {
            $data = new PosIntegration;
            $data->device_name = $request->device_name;
            $data->warehouse_id = $request->warehouse_id;
            $data->warehouse_name = $warehouse_name->warehouse_title;
            $data->token = Str::random(128);
            $data->status = 1;
            $data->save();
        return response()->json(['success'=>true, 'message'=>'Data save successfully !', 'token' => $data->token]);
        }
    }
}
