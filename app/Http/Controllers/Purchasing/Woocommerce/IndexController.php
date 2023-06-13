<?php

namespace App\Http\Controllers\Purchasing\Woocommerce;

use Auth;
use App\user;
use Illuminate\Http\Request;
use App\ExportStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Purchasing\Woocommerce\IndexController;
use App\Models\Common\Status;
use App\Jobs\WoocommerceProductJob;


class IndexController extends Controller
{
    public function wocomProducts(Request $request){
        $productIds= $request->input('selected_products');
        $user_id = Auth::user()->id;
        $type = 'wocommerce_products';
        $status = ExportStatus::where('user_id',Auth::user()->id)->where('type',$type)->first();
        if($status == null){
            $new = new ExportStatus();
            $new->type = $type;
            $new->user_id = Auth::user()->id;
            $new->status = 1;
            $new->save();

            WoocommerceProductJob::dispatch($productIds,Auth::user()->id);
            return response()->json(['msg'=>"File is now getting prepared",'status'=>1,'recursive'=>true]);
        }
        elseif($status->status == 1){
            return response()->json(['msg'=>"File is Already Being prepared",'status'=>2]); 
        }
        elseif($status->status== 0 || $status->status== 2)
        {
            ExportStatus::where('type',$type)->where('user_id', auth()->user()->id)->update(['status'=>1,'exception'=>null,'user_id'=>Auth::user()->id]);
            WoocommerceProductJob::dispatch($productIds,Auth::user()->id);
            return response()->json(['msg'=>"File is now getting prepared",'status'=>1,'exception'=>null]);
        }
    }
    public function wocomRecursiveExportStatusForProducts(){
         $status=ExportStatus::where('user_id',Auth::user()->id)->where('type', 'wocommerce_products')->first();

        return response()->json(['msg'=>"File Created!",'status'=>$status->status,'exception'=>$status->exception]);
    }
}
