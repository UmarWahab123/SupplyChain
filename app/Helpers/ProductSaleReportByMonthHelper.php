<?php
namespace App\Helpers;
use App\ExportStatus;
use Auth;
use App\Jobs\ProductSaleReportByMonthJob;


class ProductSaleReportByMonthHelper{
	
  public static function CheckStatusProductSalesReportByMonth($request)
  {
      $status=ExportStatus::where('type','product_sales_report_by_month')->where('user_id',Auth::user()->id)->first();
      if($status!=null)
      {
        return response()->json(['status'=>$status->status]);
      }
      else
      {
        return response()->json(['status'=>0]);
      }
  }

  public static function RecursiveExportStatusProductSalesReportByMonth($request)
  {
      $status=ExportStatus::where('user_id',Auth::user()->id)->where('type','product_sales_report_by_month')->first();
      return response()->json(['status'=>$status->status,'exception'=>$status->exception,'file_name'=>$status->file_name]);
  }

  public static function ExportProductSalesReportByMonth($request)
  {
    $statusCheck=ExportStatus::where('type','product_sales_report_by_month')->where('user_id',Auth::user()->id)->first();
    if(Auth::user()->role_id == 3)
    {
      $request['sale_person'] = null;
    }

    if(Auth::user()->role_id != 3)
    {
      $request['sale_person_filter'] = null;
    }
    $data=$request->all();
    if($statusCheck==null)
    {
      $new=new ExportStatus();
      $new->type='product_sales_report_by_month';
      $new->user_id=Auth::user()->id;
      $new->status=1;
      if($new->save())
      {
        ProductSaleReportByMonthJob::dispatch($data,Auth::user()->id,Auth::user()->role_id);
        return response()->json(['status'=>1]);
      }
    }
    else if($statusCheck->status==0 || $statusCheck->status==2)
    {

      ExportStatus::where('type','product_sales_report_by_month')->where('user_id',Auth::user()->id)->update(['status'=>1,'exception'=>null]);
      ProductSaleReportByMonthJob::dispatch($data,Auth::user()->id,Auth::user()->role_id);
      return response()->json(['status'=>1]);
    }
    else
    {
      return response()->json(['msg'=>'Export already being prepared','status'=>2]);
    }
  }
	
}