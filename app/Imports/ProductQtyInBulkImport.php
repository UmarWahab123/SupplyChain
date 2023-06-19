<?php

namespace App\Imports;

use App\ExportStatus;
use App\Models\Common\Product;
use App\Models\Common\StockManagementIn;
use App\Models\Common\StockManagementOut;
use App\Models\Common\Warehouse;
use Illuminate\Support\Collection;
use App\Models\Common\WarehouseProduct;
use Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Helpers\MyHelper;
use App\Jobs\BulkStockAdjustmentJob;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Order\Order;
use App\Models\Common\PurchaseOrders\PurchaseOrder;

class ProductQtyInBulkImport implements ToCollection ,WithStartRow
{
    protected $error_empty = '';

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function __construct()
    {

    }

    public function collection(Collection $rows)
    {
        $error_msg = null;
        $error = 0;
        // dd($rows);
        if($rows[0][0] == 'Stock Adjustment') {
            if($rows->count() > 1)
            {
                $export_status = ExportStatus::where('type', 'stock_bulk_upload')->where('user_id', Auth::user()->id)->first();

                if ($export_status == null)
                {
                    $export_status = new ExportStatus();
                    $export_status->type = 'stock_bulk_upload';
                }
                $export_status->user_id = Auth()->user()->id;
                $export_status->status = 1;
                $export_status->exception = null;
                $export_status->error_msgs = null;
                $export_status->save();

                BulkStockAdjustmentJob::dispatch($rows, Auth::user()->id);

                // $row1 = $rows->toArray();
                // $remove = array_shift($row1);
                // foreach ($row1 as $row)
                // {
                //     if ($row[7] != null)
                //     {
                //         $product = Product::where('refrence_code', $row[7])->first();
                //         if ($product != null)
                //         {
                //             $warehouse = Warehouse::where('status',1)->where('warehouse_title', $row[1])->first();
                //             if ($warehouse != null)
                //             {
                //                 if ($row[16] !== null)
                //                 {
                //                     if (is_numeric($row[16]))
                //                     {
                //                         if ($row[17] != null)
                //                         {
                //                             if (is_numeric($row[17]))
                //                             {
                //                                 $UNIX_DATE = ($row[17] - 25569) * 86400;
                //                                 $row[17] = date("Y-m-d", $UNIX_DATE);
                //                             }
                //                             $date = str_replace("/", "-", $row[17]);
                //                             $row[17] = Carbon::parse($date)->format('Y-m-d');
                //                         }
                //                         $stock = StockManagementIn::where('expiration_date', $row[17])->where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->first();
                //                         // $warehouse_products = WarehouseProduct::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->first();
                //                         // $my_helper =  new MyHelper;
                //                         // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_products);

                //                         $reserve = 0;
                //                         $reserve += $row[14] != null ? $row[14] : 0;
                //                         $warehouse_products = WarehouseProduct::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->first();
                //                         $warehouse_products->current_quantity += round($row[16], 3);
                //                         $warehouse_products->reserved_quantity == $reserve;
                //                         $warehouse_products->available_quantity = $warehouse_products->current_quantity - ($reserve+$warehouse_products->ecommerce_reserved_quantity);
                //                         $warehouse_products->save();


                //                         if ($stock != null)
                //                         {
                //                             $stock_out               = new StockManagementOut;
                //                             $stock_out->smi_id       = $stock->id;
                //                             $stock_out->product_id   = $product->id;
                //                             $stock_out->title   = 'Manual Adjustment';
                //                             $stock_out->cost   = @$product->selling_price;
                //                             if ($row[16] < 0) {
                //                                 $stock_out->quantity_out = $row[16];
                //                                 $stock_out->available_stock = $row[16];
                //                             } else {
                //                                 $stock_out->quantity_in  = $row[16];
                //                                 $stock_out->available_stock  = $row[16];
                //                             }
                //                             $stock_out->created_by   = Auth::user()->id;
                //                             $stock_out->warehouse_id = $warehouse->id;
                //                             $stock_out->save();
                //                             if (is_numeric($row[16]))
                //                             {
                //                                 if($row[16] < 0)
                //                                 {
                //                                     $dummy_order = Order::createManualOrder($stock_out);
                //                                 }else{
                //                                     $dummy_order = PurchaseOrder::createManualPo($stock_out);
                //                                 }
                //                             }
                //                         }
                //                         else
                //                         {
                //                             if ($row[17] == null)
                //                             {
                //                                 $stock = StockManagementIn::where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->whereNull('expiration_date')->first();
                //                             }

                //                             if ($stock == null)
                //                             {
                //                                 $stock = new StockManagementIn;
                //                             }

                //                             $stock->title           = 'Adjustment';
                //                             $stock->product_id      = $product->id;
                //                             $stock->created_by      = Auth::user()->id;
                //                             $stock->warehouse_id    = $warehouse->id;
                //                             $stock->expiration_date = $row[17];
                //                             $stock->save();

                //                             $stock_out               = new StockManagementOut;
                //                             $stock_out->smi_id       = $stock->id;
                //                             $stock_out->product_id   = $product->id;
                //                             $stock_out->title   = 'Manual Adjustment';
                //                             $stock_out->cost   = @$product->selling_price;
                //                             if (is_numeric($row[16]))
                //                             {
                //                                 if ($row[16] < 0)
                //                                 {
                //                                     $stock_out->quantity_out = $row[16];
                //                                     $stock_out->available_stock = $row[16];
                //                                 }
                //                                 else
                //                                 {
                //                                     $stock_out->quantity_in  = $row[16];
                //                                     $stock_out->available_stock = $row[16];
                //                                 }
                //                             }
                //                             $stock_out->created_by   = Auth::user()->id;
                //                             $stock_out->warehouse_id = $warehouse->id;
                //                             $stock_out->save();
                //                             if (is_numeric($row[16]))
                //                             {
                //                                 if($row[16] < 0)
                //                                 {
                //                                     $dummy_order = Order::createManualOrder($stock_out);
                //                                 }else{
                //                                     $dummy_order = PurchaseOrder::createManualPo($stock_out);
                //                                 }
                //                             }
                //                         }
                //                     }
                //                     else
                //                     {
                //                         $error_msg .='Adjust 1 Value Formate is not correct ('.$row[16].')&';
                //                         $error = 1;
                //                     }
                //                 }


                //                 if ($row[19] != null)
                //                 {
                //                     if (is_numeric($row[19]))
                //                     {
                //                         if ($row[20] != null)
                //                         {
                //                             if (is_numeric($row[20]))
                //                             {
                //                                 $UNIX_DATE = ($row[20] - 25569) * 86400;
                //                                 $row[20] = date("Y-m-d", $UNIX_DATE);
                //                             }
                //                             $date = str_replace("/", "-", $row[20]);
                //                             $row[20] = Carbon::parse($date)->format('Y-m-d');
                //                         }
                //                         $stock = StockManagementIn::where('expiration_date', $row[20])->where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->first();
                //                         // $warehouse_products = WarehouseProduct::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->first();
                //                         // $my_helper =  new MyHelper;
                //                         // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_products);

                //                         $warehouse_products = WarehouseProduct::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->first();
                //                         $warehouse_products->current_quantity += round($row[19], 3);
                //                         $warehouse_products->available_quantity = $warehouse_products->current_quantity - ($warehouse_products->reserved_quantity+$warehouse_products->ecommerce_reserved_quantity);
                //                         $warehouse_products->save();

                //                         if ($stock != null)
                //                         {
                //                             $stock_out               = new StockManagementOut;
                //                             $stock_out->smi_id       = $stock->id;
                //                             $stock_out->product_id   = $product->id;
                //                             $stock_out->title   = 'Manual Adjustment';
                //                             $stock_out->cost   = @$product->selling_price;
                //                             if ($row[19] < 0)
                //                             {
                //                                 $stock_out->quantity_out = $row[19];
                //                                 $stock_out->available_stock = $row[19];
                //                             }
                //                             else
                //                             {
                //                                 $stock_out->quantity_in  = $row[19];
                //                                 $stock_out->available_stock = $row[19];
                //                             }
                //                             $stock_out->created_by   = Auth::user()->id;
                //                             $stock_out->warehouse_id = $warehouse->id;
                //                             $stock_out->save();
                //                             if (is_numeric($row[19]))
                //                             {
                //                                 if($row[19] < 0)
                //                                 {
                //                                     $dummy_order = Order::createManualOrder($stock_out);
                //                                 }else{
                //                                     $dummy_order = PurchaseOrder::createManualPo($stock_out);
                //                                 }
                //                             }
                //                         }
                //                         else
                //                         {
                //                             if ($row[20] == null)
                //                             {
                //                                 $stock = StockManagementIn::where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->whereNull('expiration_date')->first();
                //                             }

                //                             if ($stock == null)
                //                             {
                //                                 $stock = new StockManagementIn;
                //                             }

                //                             $stock->title           = 'Adjustment';
                //                             $stock->product_id      = $product->id;
                //                             $stock->created_by      = Auth::user()->id;
                //                             $stock->warehouse_id    = $warehouse->id;
                //                             $stock->expiration_date = $row[20];
                //                             $stock->save();

                //                             $stock_out               = new StockManagementOut;
                //                             $stock_out->smi_id       = $stock->id;
                //                             $stock_out->product_id   = $product->id;
                //                             $stock_out->title   = 'Manual Adjustment';
                //                             $stock_out->cost   = @$product->selling_price;
                //                             if (is_numeric($row[19]))
                //                             {
                //                                 if ($row[19] < 0)
                //                                 {
                //                                     $stock_out->quantity_out = $row[19];
                //                                     $stock_out->available_stock = $row[19];
                //                                 }
                //                                 else
                //                                 {
                //                                     $stock_out->quantity_in  = $row[19];
                //                                     $stock_out->available_stock = $row[19];
                //                                 }
                //                             }
                //                             $stock_out->created_by   = Auth::user()->id;
                //                             $stock_out->warehouse_id = $warehouse->id;
                //                             $stock_out->save();
                //                             if (is_numeric($row[19]))
                //                             {
                //                                 if($row[19] < 0)
                //                                 {
                //                                     $dummy_order = Order::createManualOrder($stock_out);
                //                                 }else{
                //                                     $dummy_order = PurchaseOrder::createManualPo($stock_out);
                //                                 }
                //                             }
                //                         }
                //                     }
                //                     else
                //                     {
                //                         $error_msg .='Adjust 2 Value Formate is not correct ('.$row[19].')&';
                //                         $error = 1;
                //                     }
                //                 }

                //                 if ($row[22] != null)
                //                 {
                //                     if (is_numeric($row[22]))
                //                     {
                //                         if ($row[23] != null)
                //                         {
                //                             if (is_numeric($row[23]))
                //                             {
                //                                 $UNIX_DATE = ($row[23] - 25569) * 86400;
                //                                 $row[23] = date("Y-m-d", $UNIX_DATE);
                //                             }
                //                             $date = str_replace("/", "-", $row[23]);
                //                             $row[23] = Carbon::parse($date)->format('Y-m-d');
                //                         }
                //                         $stock = StockManagementIn::where('expiration_date', $row[23])->where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->first();
                //                         // $warehouse_products = WarehouseProduct::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->first();
                //                         // $my_helper =  new MyHelper;
                //                         // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_products);

                //                         $warehouse_products = WarehouseProduct::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->first();
                //                         $warehouse_products->current_quantity += round($row[22], 3);
                //                         $warehouse_products->available_quantity = $warehouse_products->current_quantity - ($warehouse_products->reserved_quantity+$warehouse_products->ecommerce_reserved_quantity);
                //                         $warehouse_products->save();
                //                         // $my_helper =  new MyHelper;
                //                         // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_products);

                //                         if ($stock != null)
                //                         {
                //                             $stock_out               = new StockManagementOut;
                //                             $stock_out->smi_id       = $stock->id;
                //                             $stock_out->product_id   = $product->id;
                //                             $stock_out->title   = 'Manual Adjustment';
                //                             $stock_out->cost   = @$product->selling_price;
                //                             if ($row[22] < 0)
                //                             {
                //                                 $stock_out->quantity_out = $row[22];
                //                                 $stock_out->available_stock = $row[22];
                //                             }
                //                             else
                //                             {
                //                                 $stock_out->quantity_in  = $row[22];
                //                                 $stock_out->available_stock = $row[22];
                //                             }
                //                             $stock_out->created_by   = Auth::user()->id;
                //                             $stock_out->warehouse_id = $warehouse->id;
                //                             $stock_out->save();
                //                             if (is_numeric($row[22]))
                //                             {
                //                                 if($row[22] < 0)
                //                                 {
                //                                     $dummy_order = Order::createManualOrder($stock_out);
                //                                 }else{
                //                                     $dummy_order = PurchaseOrder::createManualPo($stock_out);
                //                                 }
                //                             }
                //                         }
                //                         else
                //                         {
                //                             if ($row[23] == null)
                //                             {
                //                                 $stock = StockManagementIn::where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->whereNull('expiration_date')->first();
                //                             }

                //                             if ($stock == null)
                //                             {
                //                                 $stock = new StockManagementIn;
                //                             }

                //                             $stock->title           = 'Adjustment';
                //                             $stock->product_id      = $product->id;
                //                             $stock->created_by      = Auth::user()->id;
                //                             $stock->warehouse_id    = $warehouse->id;
                //                             $stock->expiration_date = $row[23];
                //                             $stock->save();

                //                             $stock_out               = new StockManagementOut;
                //                             $stock_out->smi_id       = $stock->id;
                //                             $stock_out->product_id   = $product->id;
                //                             $stock_out->title   = 'Manual Adjustment';
                //                             $stock_out->cost   = @$product->selling_price;
                //                             if (is_numeric($row[22]))
                //                             {
                //                                 if ($row[22] < 0)
                //                                 {
                //                                     $stock_out->quantity_out = $row[22];
                //                                     $stock_out->available_stock = $row[22];
                //                                 }
                //                                 else
                //                                 {
                //                                     $stock_out->quantity_in  = $row[22];
                //                                     $stock_out->available_stock = $row[22];
                //                                 }
                //                             }
                //                             $stock_out->created_by   = Auth::user()->id;
                //                             $stock_out->warehouse_id = $warehouse->id;
                //                             $stock_out->save();
                //                             if (is_numeric($row[22]))
                //                             {
                //                                 if($row[22] < 0)
                //                                 {
                //                                     $dummy_order = Order::createManualOrder($stock_out);
                //                                 }else{
                //                                     $dummy_order = PurchaseOrder::createManualPo($stock_out);
                //                                 }
                //                             }
                //                         }
                //                     }
                //                     else
                //                     {
                //                         $error_msg .='Adjust 3 Value Formate is not correct ('.$row[22].')&';
                //                         $error = 1;
                //                     }
                //                 }
                //             }
                //         }

                //     }
                // }
                // if($error == 1)
                // {
                //     return redirect('bulk-quantity-upload-form')->with('errormsg',$error_msg);
                // }
                // else
                // {
                //     return redirect('bulk-quantity-upload-form')->with('successmsg','Stock Adjusted Successfully');
                // }
            }
            else
            {
                return redirect('bulk-quantity-upload-form')->with('msg','File is Empty Upload Valid File!');
            }
        }  else {
            return redirect('bulk-quantity-upload-form')->with('msg','Please Upload Valid File');
        }

    }


    public function startRow():int
    {
        return 1;
    }
}
