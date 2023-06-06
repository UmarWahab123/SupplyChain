<?php
namespace App\Helpers;

use App\Models\Common\ProductType;
use App\Models\Common\ProductCategory;
use Carbon\Carbon;
use DB;
use Exception;
use App\ExportStatus;
use App\FailedJobException;
class MarginReportHelper{

	public static function getFirstColumnData($item, $filter)
  {
    if($filter == 'product') {
      return $item->refrence_code;
    }
    elseif($filter == 'sales'){
      return $item->name;
    }
    elseif($filter == 'office'){
      return $item->warehouse_title;
    }
    elseif($filter == 'product_category'){
      return $item->title;
    }
    elseif($filter == 'customer_type'){
      return $item->title;
    }
    elseif($filter == 'product_type'){
      return $item->title;
    }
    elseif($filter == 'product_type 2'){
      return $item->title;
    }
    elseif($filter == 'product_type 3'){
        return $item->title;
      }
    elseif($filter == 'supplier'){
      return $item->supplier != null ? $item->supplier->reference_name : '--';
    }
  }

  public static function getGPData($filter, $sales, $cogs, $adjustment_out)
  {
    $gp = 0;
    if($filter == 'product') {
      $gp = $sales - $cogs - abs($adjustment_out);
    }
    elseif($filter == 'sales'){
      $gp = $sales - $cogs;
    }
    elseif($filter == 'office'){
      $gp = $sales - $cogs - abs($adjustment_out);
    }
    elseif($filter == 'product_category'){
      $gp = $sales - $cogs - abs($adjustment_out);
    }
    elseif($filter == 'customer_type'){
      $gp = $sales - $cogs;
    }
    elseif($filter == 'product_type'){
      $gp = $sales - $cogs - abs($adjustment_out);
    }
    elseif($filter == 'product_type 2'){
      $gp = $sales - $cogs - abs($adjustment_out);
    }
    elseif($filter == 'product_type 3'){
        $gp = $sales - $cogs - abs($adjustment_out);
      }
    elseif($filter == 'supplier'){
      $gp = $sales - $cogs;
    }
    return number_format($gp,2,'.','');
  }

  public static function getAdjustmentOut($item, $request)
  {
    if($request['filter'] == 'product') {
      return $item->manual_adjustment != null ? $item->manual_adjustment()->sum(\DB::raw('quantity_out * cost')) : 0;
    }
    elseif($request['filter'] == 'sales'){
      return 0;
    }
    elseif($request['filter'] == 'office'){
      return $item->manual_adjustment != null ? $item->manual_adjustment()->sum(\DB::raw('quantity_out * cost')) : 0;
    }
    elseif($request['filter'] == 'product_category'){
      $stock = (new ProductCategory)->get_manual_adjustments_for_export($request,$item->category_id);
      return (clone $stock)->sum(\DB::raw('cost * quantity_out'));
    }
    elseif($request['filter'] == 'customer'){
      return 0;
    }
    elseif($request['filter'] == 'customer_type'){
      return 0;
    }
    elseif($request['filter'] == 'product_type'){
      $stock = (new ProductType)->get_manual_adjustments_for_export($request,$item->category_id);
      return (clone $stock)->sum(\DB::raw('cost * quantity_out'));
    }
    elseif($request['filter'] == 'product_type 2'){
       $stock = (new ProductType)->get_manual_adjustments_for_export($request,$item->category_id);
       return (clone $stock)->sum(\DB::raw('cost * quantity_out'));
    }
    elseif($request['filter'] == 'product_type 3'){
        $stock = (new ProductType)->get_manual_adjustments_for_export($request,$item->category_id);
        return (clone $stock)->sum(\DB::raw('cost * quantity_out'));
     }
    elseif($request['filter'] == 'supplier'){
      // return $item->manual_adjustment != null ? $item->manual_adjustment()->sum(\DB::raw('quantity_out * cost')) : 0;
      return 0;
    }
  }
  public static function getSpoilageData($from_date, $to_date){
      try {
          $from_date = Carbon::createFromFormat('d/m/Y', $from_date)->format('Y-m-d');
          $to_date = Carbon::createFromFormat('d/m/Y', $to_date)->format('Y-m-d');
          } catch (InvalidArgumentException $e) {
              // Handle the exception, log the error, or display a meaningful message
              return response()->json(['error' => 'Invalid date format'], 400);
          }
          $spoilageStock = DB::table('stock_management_outs')
          ->join('customers', 'stock_management_outs.customer_id', '=', 'customers.id')
          ->join('suppliers', 'stock_management_outs.supplier_id', '=', 'suppliers.id')
          ->join('products', 'stock_management_outs.product_id', '=', 'products.id')
          ->select(
              'products.refrence_code as reference_code',
              'suppliers.reference_name as default_supplier',
              'customers.reference_name as customer',
              DB::raw('SUM(ABS(stock_management_outs.quantity_out)) as quantity'),
              DB::raw('SUM(ABS(stock_management_outs.quantity_out) * stock_management_outs.cost) as cogs_total'),
              DB::raw('MIN(stock_management_outs.cost) as unit_cogs')
          )
          ->where('customers.manual_customer', 2);

          if(!empty($from_date)){
            $spoilageStock = $spoilageStock->whereDate('stock_management_outs.created_at', '>=', $from_date);
          }
          if(!empty($to_date)){
            $spoilageStock = $spoilageStock->whereDate('stock_management_outs.created_at', '<=', $to_date);
          }
          $spoilageStock = $spoilageStock->groupBy('suppliers.reference_name', 'products.refrence_code')
          ->orderBy('reference_code');
          
           return $spoilageStock;
    }
}
