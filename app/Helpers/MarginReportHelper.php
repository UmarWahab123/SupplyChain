<?php
namespace App\Helpers;

use App\Models\Common\ProductType;
use App\Models\Common\ProductCategory;

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
}
