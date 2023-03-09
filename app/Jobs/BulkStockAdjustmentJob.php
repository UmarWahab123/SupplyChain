<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\ExportStatus;
use Illuminate\Bus\Queueable;
use App\Models\Common\Product;
use App\Models\Common\Warehouse;
use App\Models\Common\Order\Order;
use Illuminate\Queue\SerializesModels;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\StockManagementIn;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Common\StockManagementOut;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Common\PurchaseOrders\PurchaseOrder;

class BulkStockAdjustmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rows = null;
    protected $user_id = null;
    public $timeout = 1500;
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($rows, $user_id)
    {
        $this->rows = $rows;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $rows = $this->rows;
        $user_id = $this->user_id;

        $error_msg = null;
        $error = 0;
        try {
            $row1 = $rows->toArray();
            $remove = array_shift($row1);
            foreach ($row1 as $row)
            {
                $product_code = $row[7];
                $reserved_qty = $row[14];

                $adjust_1 = $row[16];
                $expiry_1 = $row[17];

                $adjust_2 = $row[19];
                $expiry_2 = $row[20];

                $adjust_3 = $row[22];
                $expiry_3 = $row[23];

                if ($product_code != null)
                {
                    $product = Product::where('refrence_code', $product_code)->first();
                    if ($product != null)
                    {
                        $warehouse = Warehouse::where('status',1)->where('warehouse_title', $row[1])->first();
                        if ($warehouse != null)
                        {
                            if ($adjust_1 !== null)
                            {
                                if (is_numeric($adjust_1))
                                {
                                    if ($expiry_1 != null)
                                    {
                                        if (is_numeric($expiry_1))
                                        {
                                            $UNIX_DATE = ($expiry_1 - 25569) * 86400;
                                            $expiry_1 = date("Y-m-d", $UNIX_DATE);
                                        }
                                        $date = str_replace("/", "-", $expiry_1);
                                        $expiry_1 = Carbon::parse($date)->format('Y-m-d');
                                    }
                                    $stock = StockManagementIn::where('expiration_date', $expiry_1)->where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->first();
                                    // $warehouse_products = WarehouseProduct::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->first();
                                    // $my_helper =  new MyHelper;
                                    // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_products);

                                    $reserve = 0;
                                    $reserve += $reserved_qty != null ? $reserved_qty : 0;
                                    $warehouse_products = WarehouseProduct::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->first();
                                    $warehouse_products->current_quantity += round($adjust_1, 3);
                                    $warehouse_products->reserved_quantity == $reserve;
                                    $warehouse_products->available_quantity = $warehouse_products->current_quantity - ($reserve+$warehouse_products->ecommerce_reserved_quantity);
                                    $warehouse_products->save();


                                    if ($stock != null)
                                    {
                                        $stock_out               = new StockManagementOut();
                                        $stock_out->smi_id       = $stock->id;
                                        $stock_out->product_id   = $product->id;
                                        $stock_out->title   = 'Manual Adjustment';
                                        $stock_out->cost   = @$product->selling_price;
                                        if ($adjust_1 < 0) {
                                            $stock_out->quantity_out = $adjust_1;
                                            $stock_out->available_stock = $adjust_1;
                                        } else {
                                            $stock_out->quantity_in  = $adjust_1;
                                            $stock_out->available_stock  = $adjust_1;
                                        }
                                        $stock_out->created_by   = $user_id;
                                        $stock_out->warehouse_id = $warehouse->id;
                                        $stock_out->save();
                                        if (is_numeric($adjust_1))
                                        {
                                            if($adjust_1 < 0)
                                            {
                                                $dummy_order = Order::createManualOrder($stock_out);
                                            }else{
                                                $dummy_order = PurchaseOrder::createManualPo($stock_out);
                                            }
                                        }
                                    }
                                    else
                                    {
                                        if ($expiry_1 == null)
                                        {
                                            $stock = StockManagementIn::where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->whereNull('expiration_date')->first();
                                        }

                                        if ($stock == null)
                                        {
                                            $stock = new StockManagementIn;
                                        }

                                        $stock->title           = 'Adjustment';
                                        $stock->product_id      = $product->id;
                                        $stock->created_by      = $user_id;
                                        $stock->warehouse_id    = $warehouse->id;
                                        $stock->expiration_date = $expiry_1;
                                        $stock->save();

                                        $stock_out               = new StockManagementOut;
                                        $stock_out->smi_id       = $stock->id;
                                        $stock_out->product_id   = $product->id;
                                        $stock_out->title   = 'Manual Adjustment';
                                        $stock_out->cost   = @$product->selling_price;
                                        if (is_numeric($adjust_1))
                                        {
                                            if ($adjust_1 < 0)
                                            {
                                                $stock_out->quantity_out = $adjust_1;
                                                $stock_out->available_stock = $adjust_1;
                                            }
                                            else
                                            {
                                                $stock_out->quantity_in  = $adjust_1;
                                                $stock_out->available_stock = $adjust_1;
                                            }
                                        }
                                        $stock_out->created_by   = $user_id;
                                        $stock_out->warehouse_id = $warehouse->id;
                                        $stock_out->save();
                                        if (is_numeric($adjust_1))
                                        {
                                            if($adjust_1 < 0)
                                            {
                                                $dummy_order = Order::createManualOrder($stock_out);
                                            }else{
                                                $dummy_order = PurchaseOrder::createManualPo($stock_out);
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    $error_msg .='Adjust 1 Value Formate is not correct ('.$adjust_1.')&';
                                    $error = 1;
                                }
                            }


                            if ($adjust_2 != null)
                            {
                                if (is_numeric($adjust_2))
                                {
                                    if ($expiry_2 != null)
                                    {
                                        if (is_numeric($expiry_2))
                                        {
                                            $UNIX_DATE = ($expiry_2 - 25569) * 86400;
                                            $expiry_2 = date("Y-m-d", $UNIX_DATE);
                                        }
                                        $date = str_replace("/", "-", $expiry_2);
                                        $expiry_2 = Carbon::parse($date)->format('Y-m-d');
                                    }
                                    $stock = StockManagementIn::where('expiration_date', $expiry_2)->where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->first();
                                    // $warehouse_products = WarehouseProduct::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->first();
                                    // $my_helper =  new MyHelper;
                                    // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_products);

                                    $warehouse_products = WarehouseProduct::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->first();
                                    $warehouse_products->current_quantity += round($adjust_2, 3);
                                    $warehouse_products->available_quantity = $warehouse_products->current_quantity - ($warehouse_products->reserved_quantity+$warehouse_products->ecommerce_reserved_quantity);
                                    $warehouse_products->save();

                                    if ($stock != null)
                                    {
                                        $stock_out               = new StockManagementOut;
                                        $stock_out->smi_id       = $stock->id;
                                        $stock_out->product_id   = $product->id;
                                        $stock_out->title   = 'Manual Adjustment';
                                        $stock_out->cost   = @$product->selling_price;
                                        if ($adjust_2 < 0)
                                        {
                                            $stock_out->quantity_out = $adjust_2;
                                            $stock_out->available_stock = $adjust_2;
                                        }
                                        else
                                        {
                                            $stock_out->quantity_in  = $adjust_2;
                                            $stock_out->available_stock = $adjust_2;
                                        }
                                        $stock_out->created_by   = $user_id;
                                        $stock_out->warehouse_id = $warehouse->id;
                                        $stock_out->save();
                                        if (is_numeric($adjust_2))
                                        {
                                            if($adjust_2 < 0)
                                            {
                                                $dummy_order = Order::createManualOrder($stock_out);
                                            }else{
                                                $dummy_order = PurchaseOrder::createManualPo($stock_out);
                                            }
                                        }
                                    }
                                    else
                                    {
                                        if ($expiry_2 == null)
                                        {
                                            $stock = StockManagementIn::where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->whereNull('expiration_date')->first();
                                        }

                                        if ($stock == null)
                                        {
                                            $stock = new StockManagementIn;
                                        }

                                        $stock->title           = 'Adjustment';
                                        $stock->product_id      = $product->id;
                                        $stock->created_by      = $user_id;
                                        $stock->warehouse_id    = $warehouse->id;
                                        $stock->expiration_date = $expiry_2;
                                        $stock->save();

                                        $stock_out               = new StockManagementOut;
                                        $stock_out->smi_id       = $stock->id;
                                        $stock_out->product_id   = $product->id;
                                        $stock_out->title   = 'Manual Adjustment';
                                        $stock_out->cost   = @$product->selling_price;
                                        if (is_numeric($adjust_2))
                                        {
                                            if ($adjust_2 < 0)
                                            {
                                                $stock_out->quantity_out = $adjust_2;
                                                $stock_out->available_stock = $adjust_2;
                                            }
                                            else
                                            {
                                                $stock_out->quantity_in  = $adjust_2;
                                                $stock_out->available_stock = $adjust_2;
                                            }
                                        }
                                        $stock_out->created_by   = $user_id;
                                        $stock_out->warehouse_id = $warehouse->id;
                                        $stock_out->save();
                                        if (is_numeric($adjust_2))
                                        {
                                            if($adjust_2 < 0)
                                            {
                                                $dummy_order = Order::createManualOrder($stock_out);
                                            }else{
                                                $dummy_order = PurchaseOrder::createManualPo($stock_out);
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    $error_msg .='Adjust 2 Value Formate is not correct ('.$adjust_2.')&';
                                    $error = 1;
                                }
                            }

                            if ($adjust_3 != null)
                            {
                                if (is_numeric($adjust_3))
                                {
                                    if ($expiry_3 != null)
                                    {
                                        if (is_numeric($expiry_3))
                                        {
                                            $UNIX_DATE = ($expiry_3 - 25569) * 86400;
                                            $expiry_3 = date("Y-m-d", $UNIX_DATE);
                                        }
                                        $date = str_replace("/", "-", $expiry_3);
                                        $expiry_3 = Carbon::parse($date)->format('Y-m-d');
                                    }
                                    $stock = StockManagementIn::where('expiration_date', $expiry_3)->where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->first();
                                    // $warehouse_products = WarehouseProduct::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->first();
                                    // $my_helper =  new MyHelper;
                                    // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_products);

                                    $warehouse_products = WarehouseProduct::where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->first();
                                    $warehouse_products->current_quantity += round($adjust_3, 3);
                                    $warehouse_products->available_quantity = $warehouse_products->current_quantity - ($warehouse_products->reserved_quantity+$warehouse_products->ecommerce_reserved_quantity);
                                    $warehouse_products->save();
                                    // $my_helper =  new MyHelper;
                                    // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_products);

                                    if ($stock != null)
                                    {
                                        $stock_out               = new StockManagementOut;
                                        $stock_out->smi_id       = $stock->id;
                                        $stock_out->product_id   = $product->id;
                                        $stock_out->title   = 'Manual Adjustment';
                                        $stock_out->cost   = @$product->selling_price;
                                        if ($adjust_3 < 0)
                                        {
                                            $stock_out->quantity_out = $adjust_3;
                                            $stock_out->available_stock = $adjust_3;
                                        }
                                        else
                                        {
                                            $stock_out->quantity_in  = $adjust_3;
                                            $stock_out->available_stock = $adjust_3;
                                        }
                                        $stock_out->created_by   = $user_id;
                                        $stock_out->warehouse_id = $warehouse->id;
                                        $stock_out->save();
                                        if (is_numeric($adjust_3))
                                        {
                                            if($adjust_3 < 0)
                                            {
                                                $dummy_order = Order::createManualOrder($stock_out);
                                            }else{
                                                $dummy_order = PurchaseOrder::createManualPo($stock_out);
                                            }
                                        }
                                    }
                                    else
                                    {
                                        if ($expiry_3 == null)
                                        {
                                            $stock = StockManagementIn::where('product_id', $product->id)->where('warehouse_id', $warehouse->id)->whereNull('expiration_date')->first();
                                        }

                                        if ($stock == null)
                                        {
                                            $stock = new StockManagementIn;
                                        }

                                        $stock->title           = 'Adjustment';
                                        $stock->product_id      = $product->id;
                                        $stock->created_by      = $user_id;
                                        $stock->warehouse_id    = $warehouse->id;
                                        $stock->expiration_date = $expiry_3;
                                        $stock->save();

                                        $stock_out               = new StockManagementOut;
                                        $stock_out->smi_id       = $stock->id;
                                        $stock_out->product_id   = $product->id;
                                        $stock_out->title   = 'Manual Adjustment';
                                        $stock_out->cost   = @$product->selling_price;
                                        if (is_numeric($adjust_3))
                                        {
                                            if ($adjust_3 < 0)
                                            {
                                                $stock_out->quantity_out = $adjust_3;
                                                $stock_out->available_stock = $adjust_3;
                                            }
                                            else
                                            {
                                                $stock_out->quantity_in  = $adjust_3;
                                                $stock_out->available_stock = $adjust_3;
                                            }
                                        }
                                        $stock_out->created_by   = $user_id;
                                        $stock_out->warehouse_id = $warehouse->id;
                                        $stock_out->save();
                                        if (is_numeric($adjust_3))
                                        {
                                            if($adjust_3 < 0)
                                            {
                                                $dummy_order = Order::createManualOrder($stock_out);
                                            }else{
                                                $dummy_order = PurchaseOrder::createManualPo($stock_out);
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    $error_msg .='Adjust 3 Value Formate is not correct ('.$adjust_3.')&';
                                    $error = 1;
                                }
                            }
                        }
                    }
                }
            }

            $export_status = ExportStatus::where('type', 'stock_bulk_upload')->where('user_id', $user_id)->first();
            $export_status->status = 0;
            if ($error == 1)
            {
                $export_status->error_msgs = $error_msg;
            }
            $export_status->save();
        } catch (\Throwable $th) {
            $export_status = ExportStatus::where('type', 'stock_bulk_upload')->where('user_id', $user_id)->first();
            $export_status->status = 2;
            $export_status->exception = $th->getMessage();
            $export_status->save();
        }
    }
}
