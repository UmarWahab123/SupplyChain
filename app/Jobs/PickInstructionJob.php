<?php

namespace App\Jobs;

use App\ExportStatus;
use App\FailedJobException;
use Carbon\Carbon;
use App\QuotationConfig;
use App\Mail\PartialMail;
use App\OrdersPaymentRef;
use App\OrderTransaction;
use App\Models\Common\Status;
use Illuminate\Bus\Queueable;
use App\Models\Common\Warehouse;
use App\Models\Common\Order\Order;
use Illuminate\Support\Facades\DB;
use App\Models\Common\OrderHistory;
use Illuminate\Support\Facades\Mail;
use App\Models\Common\StockOutHistory;
use Illuminate\Queue\SerializesModels;
use App\Models\Common\CustomerCategory;
use App\Models\Common\WarehouseProduct;
use App\Helpers\QuantityReservedHistory;
use App\Models\Common\StockManagementIn;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\StockManagementOut;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Common\Order\OrderStatusHistory;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use Illuminate\Queue\MaxAttemptsExceededException;

class PickInstructionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request = null;
    protected $user = null;
    public $timeout = 1800;
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request, $user)
    {
        $this->request = $request;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $request = $this->request;
        $user = $this->user;
        $error_msg = '';
        $order = Order::where('id', $request['order_id'])->first();
        if ($order->is_processing == 1) {
            $error_msg = json_encode(['already_confirmed' => true]);
            // $error_msg = response()->json(['already_confirmed' => true]);
            $this->updateExportStatus($error_msg, $user->id);
            return;
        }
        $order->is_processing = 1;
        $order->save();
        DB::beginTransaction();
        try {
            $stock_cost = [];
            if ($order->primary_status == 3) {
                DB::rollBack();
                $order->is_processing = 0;
                $order->save();
                // $error_msg = response()->json(['already_confirmed' => true]);
                $error_msg = json_encode(['already_confirmed' => true]);
                $this->updateExportStatus($error_msg, $user->id);
                return;
            }
            $order->primary_status = 3;
            $order->save();
            $order_products = OrderProduct::where('order_id', $order->id)->where('is_billed', '=', 'Product')->get();
            $order_products_billed = OrderProduct::where('order_id', $order->id)->where('is_billed', '=', 'Billed')->get();

            $rec_date_ot = Carbon::now();
            $rec_date =  date('Y-m-d', strtotime($rec_date_ot));
            if ($request['page_info'] == "draft") {
                if ($order_products->count() > 0) {
                    foreach ($order_products as $order_product) {
                        $order_product->pcs_shipped = $order_product->number_of_pieces;
                        $order_product->qty_shipped = $order_product->quantity;
                        $order_product->save();
                    }
                }
            }

            $pi_config = [];
            $pi_config = QuotationConfig::where('section', 'pick_instruction')->first();
            if ($pi_config != null) {
                $pi_config = unserialize($pi_config->print_prefrences);
            }
            foreach ($order_products as $order_product) {
                if ($order_product->is_retail == 'qty') {
                    if ($order_product->qty_shipped == null) {
                        DB::rollBack();
                        $order->is_processing = 0;
                        $order->save();
                        $error_msg = json_encode(['qty_shipped' => 'is_null', 'product' => $order_product->product->refrence_code]);
                        $this->updateExportStatus($error_msg, $user->id);
                        return;
                    } else {
                        $pids = PurchaseOrder::where('status', 21)->WhereNotNull('from_warehouse_id')->whereHas('PoWarehouse', function ($qq) use ($order_product) {
                            $qq->where('from_warehouse_id', $order_product->user_warehouse_id);
                        })->pluck('id')->toArray();

                        $order_ids = Order::where('primary_status', 2)->where('id', '!=', $order_product->order_id)->whereHas('order_products', function ($q) use ($order_product) {
                            $q->where('from_warehouse_id', $order_product->user_warehouse_id);
                        })->pluck('id')->toArray();

                        if ($pi_config['pi_confirming_condition'] == 2) {
                            $warehouse_product = WarehouseProduct::where('product_id', $order_product->product_id)->where('warehouse_id', $order_product->user_warehouse_id)->first();
                            $stock_qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity : ' 0';
                            if (($stock_qty < $order_product->quantity) && ($order_product->qty_shipped !== '0')) {
                                DB::rollBack();
                                $order->is_processing = 0;
                                $order->save();
                                // $error_msg = response()->json(['success' => false, 'type' => 'stock', 'stock_qty' => 'less_than_order', 'product' => $order_product->product->refrence_code]);
                                $error_msg = json_encode(['success' => false, 'type' => 'stock', 'stock_qty' => 'less_than_order', 'product' => $order_product->product->refrence_code]);
                                $this->updateExportStatus($error_msg, $user->id);
                                return;
                            } else {
                                if ($stock_qty <= 0  && ($order_product->qty_shipped !== '0')) {
                                    if ($stock_qty < 0) {
                                        DB::rollBack();
                                        $order->is_processing = 0;
                                        $order->save();
                                        // $error_msg = response()->json(['success' => false, 'type' => 'stock', 'stock_qty' => 'less_than_zero', 'product' => $order_product->product->refrence_code]);
                                        $error_msg = json_encode(['success' => false, 'type' => 'stock', 'stock_qty' => 'less_than_zero', 'product' => $order_product->product->refrence_code]);
                                        $this->updateExportStatus($error_msg, $user->id);
                                        return;
                                    } else if ($stock_qty == 0) {
                                        DB::rollBack();
                                        $order->is_processing = 0;
                                        $order->save();
                                        // $error_msg = response()->json(['success' => false, 'type' => 'stock', 'stock_qty' => 'equals_to_zero', 'product' => $order_product->product->refrence_code]);
                                        $error_msg = json_encode(['success' => false, 'type' => 'stock', 'stock_qty' => 'equals_to_zero', 'product' => $order_product->product->refrence_code]);
                                        $this->updateExportStatus($error_msg, $user->id);
                                        return;
                                    }
                                }
                            }
                        } elseif ($pi_config['pi_confirming_condition'] == 3) {
                            $warehouse_product = WarehouseProduct::where('product_id', $order_product->product_id)->where('warehouse_id', $order_product->user_warehouse_id)->first();
                            $stock_qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity : ' 0';
                            $order_rsv_qty = OrderProduct::whereIn('order_id', $order_ids)->where('product_id', $order_product->product_id)->sum('quantity');
                            $pick_rsv_qty = PurchaseOrderDetail::whereIn('po_id', $pids)->where('product_id', $order_product->product_id)->sum('quantity');
                            $available_qty = $stock_qty - ($order_rsv_qty + $pick_rsv_qty);
                            if ($order_product->quantity > $available_qty) {
                                DB::rollBack();
                                $order->is_processing = 0;
                                $order->save();
                                // $error_msg = response()->json(['success' => false, 'type' => 'available', 'available_qty' => 'less_than_order', 'product' => $order_product->product->refrence_code]);
                                $error_msg = json_encode(['success' => false, 'type' => 'available', 'available_qty' => 'less_than_order', 'product' => $order_product->product->refrence_code]);
                                $this->updateExportStatus($error_msg, $user->id);
                                return;
                            } else {
                                if ($available_qty <= 0) {
                                    if ($available_qty < 0) {
                                        DB::rollBack();
                                        $order->is_processing = 0;
                                        $order->save();
                                        // $error_msg = response()->json(['success' => false, 'type' => 'available', 'available_qty' => 'less_than_zero', 'product' => $order_product->product->refrence_code]);
                                        $error_msg = json_encode(['success' => false, 'type' => 'available', 'available_qty' => 'less_than_zero', 'product' => $order_product->product->refrence_code]);
                                        $this->updateExportStatus($error_msg, $user->id);
                                        return ;
                                    } else if ($available_qty == 0) {
                                        DB::rollBack();
                                        $order->is_processing = 0;
                                        $order->save();
                                        // $error_msg = response()->json(['success' => false, 'type' => 'available', 'available_qty' => 'equals_to_zero', 'product' => $order_product->product->refrence_code]);
                                        $error_msg = json_encode(['success' => false, 'type' => 'available', 'available_qty' => 'equals_to_zero', 'product' => $order_product->product->refrence_code]);
                                        $this->updateExportStatus($error_msg, $user->id);
                                        return;
                                    }
                                }
                            }
                        }
                    }
                } else if ($order_product->is_retail == 'pieces') {
                    if ($order_product->pcs_shipped == null || $order_product->qty_shipped == null) {
                        DB::rollBack();
                        $order->is_processing = 0;
                        $order->save();
                        // $error_msg = response()->json(['pcs_shipped' => 'is_null', 'product' => $order_product->product->refrence_code]);
                        $error_msg = json_encode(['pcs_shipped' => 'is_null', 'product' => $order_product->product->refrence_code]);
                        $this->updateExportStatus($error_msg, $user->id);
                        return;
                    }
                }
            }
            $status_history = new OrderStatusHistory;
            $status_history->user_id = $user->id;
            $status_history->order_id = $order->id;
            if ($order->ecommerce_order == 1) {
                $getStatus = Status::find($order->status);
                if ($getStatus) {
                    $status_history->status = $getStatus->title;
                }
            } else {
                $status_history->status = 'DI(Waiting To Pick)';
            }
            $status_history->new_status = 'Invoice';
            $status_history->save();

            $order_total = 0;
            foreach ($order_products as $order_product) {
                $warehouse_id = $order_product->user_warehouse_id != null ? $order_product->user_warehouse_id : ($order_product->get_order->user_created != null ? $order_product->get_order->user_created->warehouse_id : $user->warehouse_id);
                $order_product->status = 11;
                $order_product->save();
                $item_unit_price = number_format($order_product->unit_price, 2, '.', '');
                if ($order_product->qty_shipped !== null) {
                    $total_cost = null;
                    $total_count = null;
                    if ($order_product->expiration_date != null) {
                        $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', $warehouse_id)->where('expiration_date', $order_product->expiration_date)->whereNotNull('expiration_date')->first();
                        $shipped = $order_product->qty_shipped;
                        if ($stock == null) {
                            $stock = new StockManagementIn;
                            $stock->title = 'Adjustment';
                            $stock->product_id = $order_product->product_id;
                            $stock->created_by = $user->id;
                            $stock->warehouse_id = $warehouse_id;
                            $stock->expiration_date = $order_product->expiration_date;
                            $stock->save();
                        }
                        if ($stock != null) {
                            $stock_out = new StockManagementOut;
                            $stock_out->smi_id = $stock->id;
                            $stock_out->order_id = $order_product->order_id;
                            $stock_out->order_product_id = $order_product->id;
                            $stock_out->product_id = $order_product->product_id;
                            $stock_out->quantity_out = $order_product->qty_shipped != null ? '-' . $order_product->qty_shipped : 0;
                            $stock_out->created_by = $user->id;
                            $stock_out->warehouse_id = $warehouse_id;
                            $stock_out->available_stock = $order_product->qty_shipped != null ? '-' . $order_product->qty_shipped : 0;
                            $stock_out->save();

                            //To find from which stock the order will be deducted
                            $find_stock = $stock->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();
                            if ($find_stock->count() > 0) {
                                foreach ($find_stock as $out) {

                                    if (abs($stock_out->available_stock) > 0) {
                                        if ($out->available_stock >= abs($stock_out->available_stock)) {
                                            $history_quantity = $stock_out->available_stock;
                                            $stock_out->parent_id_in .= $out->id . ',';
                                            $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                            $stock_out->available_stock = 0;

                                            $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, abs($history_quantity));
                                        } else {
                                            $history_quantity = $out->available_stock;
                                            $stock_out->parent_id_in .= $out->id . ',';
                                            $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                            $out->available_stock = 0;

                                            $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, round(abs($history_quantity), 4));
                                        }
                                        $out->save();
                                        $stock_out->save();
                                    }
                                }
                            }
                        }
                    } else {
                        $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', $warehouse_id)->whereNotNull('expiration_date')->orderBy('expiration_date', "ASC")->get();
                        $shipped = $order_product->qty_shipped;
                        foreach ($stock as $st) {
                            $stock_out_in = StockManagementOut::where('smi_id', $st->id)->sum('quantity_in');
                            $stock_out_out = StockManagementOut::where('smi_id', $st->id)->sum('quantity_out');
                            $balance = ($stock_out_in) + ($stock_out_out);
                            $balance = round($balance, 3);
                            if ($balance > 0) {
                                $inStock = $balance - $shipped;
                                if ($inStock >= 0) {
                                    $stock_out = new StockManagementOut;
                                    $stock_out->smi_id = $st->id;
                                    $stock_out->order_id = $order_product->order_id;
                                    $stock_out->order_product_id = $order_product->id;
                                    $stock_out->product_id = $order_product->product_id;
                                    $stock_out->quantity_out = $shipped != null ? '-' . $shipped : 0;
                                    $stock_out->available_stock = '-' . $shipped;
                                    $stock_out->created_by = $user->id;
                                    $stock_out->warehouse_id = $warehouse_id;
                                    $stock_out->save();


                                    //To find from which stock the order will be deducted
                                    $find_stock = $st->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();
                                    if ($find_stock->count() > 0) {
                                        foreach ($find_stock as $out) {

                                            if ($shipped > 0) {
                                                if ($out->available_stock >= $shipped) {
                                                    $stock_out->parent_id_in .= $out->id . ',';
                                                    $out->available_stock = $out->available_stock - $shipped;
                                                    $stock_out->available_stock = 0;
                                                    $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, abs($shipped));
                                                } else {
                                                    $history_quantity = $out->available_stock;
                                                    $stock_out->parent_id_in .= $out->id . ',';
                                                    $stock_out->available_stock = $out->available_stock - $shipped;
                                                    $out->available_stock = 0;
                                                    $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, round(abs($history_quantity), 4));
                                                }
                                                $out->save();
                                                $stock_out->save();
                                                $shipped = abs($stock_out->available_stock);
                                            }
                                        }
                                    }
                                    $shipped = 0;
                                    break;
                                } else {
                                    $stock_out = new StockManagementOut;
                                    $stock_out->smi_id = $st->id;
                                    $stock_out->order_id = $order_product->order_id;
                                    $stock_out->order_product_id = $order_product->id;
                                    $stock_out->product_id = $order_product->product_id;
                                    $stock_out->quantity_out = -$balance;
                                    $stock_out->available_stock = -$balance;
                                    // $stock_out->available_stock = $inStock;
                                    $stock_out->created_by = $user->id;
                                    $stock_out->warehouse_id = $warehouse_id;
                                    $stock_out->save();

                                    //To find from which stock the order will be deducted
                                    $find_stock = $st->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();

                                    $find_available_stock = $find_stock->sum('available_stock');
                                    if ($find_stock->count() > 0) {
                                        foreach ($find_stock as $out) {

                                            if ($balance > 0) {
                                                if ($out->available_stock >= $balance) {
                                                    $stock_out->parent_id_in .= $out->id . ',';
                                                    $out->available_stock = $out->available_stock - $balance;
                                                    $stock_out->available_stock = 0;

                                                    $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, abs($balance));
                                                } else {
                                                    $history_quantity = $out->available_stock;
                                                    $stock_out->parent_id_in .= $out->id . ',';
                                                    $stock_out->available_stock = $out->available_stock - $balance;
                                                    $out->available_stock = 0;
                                                    $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, round(abs($history_quantity), 4));
                                                }
                                                $out->save();
                                                $stock_out->save();

                                                $balance = abs($stock_out->available_stock);
                                            }
                                        }
                                    }
                                    $shipped = abs($inStock);
                                }
                            }
                        }
                        if ($shipped != 0) {
                            $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', $warehouse_id)->whereNull('expiration_date')->first();
                            if ($stock == null) {
                                $stock = new StockManagementIn;
                                $stock->title = 'Adjustment';
                                $stock->product_id = $order_product->product_id;
                                $stock->created_by = $user->id;
                                $stock->warehouse_id = $warehouse_id;
                                $stock->expiration_date = $order_product->expiration_date;
                                $stock->save();
                            }

                            //To find from which stock the order will be deducted
                            $find_stock = $stock->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();

                            $stock_out = new StockManagementOut;
                            $stock_out->smi_id = $stock->id;
                            $stock_out->order_id = $order_product->order_id;
                            $stock_out->order_product_id = $order_product->id;
                            $stock_out->product_id = $order_product->product_id;
                            $stock_out->quantity_out = $shipped != null ? '-' . $shipped : 0;
                            $stock_out->available_stock = $shipped != null ? '-' . $shipped : 0;
                            $stock_out->created_by = $user->id;
                            $stock_out->warehouse_id = $warehouse_id;
                            $stock_out->save();

                            if ($find_stock->count() > 0) {
                                foreach ($find_stock as $out) {

                                    if ($shipped > 0) {
                                        if ($out->available_stock >= $shipped) {
                                            $stock_out->parent_id_in .= $out->id . ',';
                                            $out->available_stock = $out->available_stock - $shipped;
                                            $stock_out->available_stock = 0;
                                            $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, abs($shipped));
                                        } else {
                                            $history_quantity = $out->available_stock;
                                            $stock_out->parent_id_in .= $out->id . ',';
                                            $stock_out->available_stock = $out->available_stock - $shipped;
                                            $out->available_stock = 0;
                                            $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, round(abs($history_quantity), 4));
                                        }
                                        $out->save();
                                        $stock_out->save();
                                        $shipped = abs($stock_out->available_stock);
                                    }
                                }
                            } else {
                                $stock_out->available_stock = '-' . @$shipped;
                                $stock_out->save();
                            }
                        }
                    }

                    if ($order_product->get_order->ecommerce_order == 1) {
                        $new_his = new QuantityReservedHistory;
                        $re      = $new_his->updateCurrentReservedQuantity($order_product, 'Order Confirmed By Warehouse Ecom Reserved Subtracted ', 'subtract', $user->id);
                    } else {
                        $new_his = new QuantityReservedHistory;
                        $re      = $new_his->updateCurrentReservedQuantity($order_product, 'Order Confirmed By Warehouse Reserved Subtracted ', 'subtract', $user->id);
                    }
                } else if ($order_product->qty_shipped == 0) {
                    $msg = $order_product->get_order->ecommerce_order == 1 ? 'Ecom' : '';
                    $new_his = new QuantityReservedHistory;
                    $re      = $new_his->updateCurrentReservedQuantity($order_product, 'Order Confirmed By Warehouse ' . $msg . ' Reserved Subtracted ', 'subtract', $user->id);
                }

                if ($order_product->is_retail == 'qty') {
                    // $total_price = $order_product->qty_shipped * $order_product->unit_price;
                    $total_price = $item_unit_price * $order_product->qty_shipped;
                    $num = $order_product->qty_shipped;
                } else if ($order_product->is_retail == 'pieces') {
                    // $total_price = $order_product->pcs_shipped * $order_product->unit_price;
                    $total_price = $item_unit_price * $order_product->pcs_shipped;
                    $num = $order_product->pcs_shipped;
                }
                // $product = $order_product->product;
                $discount = $order_product->discount;

                if ($discount != null) {
                    $dis = $discount / 100;
                    $discount_value = $dis * $total_price;
                    $result = $total_price - $discount_value;
                } else {
                    $result = $total_price;
                }

                $order_product->total_price = round($result, 2);

                // $order_product->total_price_with_vat = (($order_product->vat/100)*$result)+$result;
                // $unit_price = round($order_product->unit_price,2);
                $vat = $order_product->vat;
                // $vat_amount = @$unit_price * ( @$vat / 100 );
                //  $vat_amountt = @$item_unit_price * ( @$vat / 100 );
                // $vat_amount = number_format(floor($vat_amountt*10000)/10000,4,'.','');

                $vat_amountt = @$item_unit_price * (@$vat / 100);
                $vat_amount = number_format($vat_amountt, 4, '.', '');
                $vat_amount_total_over_item = $vat_amount * $num;
                $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                    // $unit_price_with_vat = $order_product->unit_price_with_vat * $num;
                    // $unit_price_with_vat = number_format(floor(($order_product->unit_price_with_vat * $num)*10000)/10000,4,'.','');
                    $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                } else {
                    // $unit_price_with_vat = round(@$unit_price+@$vat_amount,2) * $num;
                    //   $unit_price_with_vatt = (@$item_unit_price+@$vat_amount) * $num;
                    // $unit_price_with_vat = number_format(floor($unit_price_with_vatt*10000)/10000,4,'.','');
                    $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                }
                if (@$discount !== null) {
                    $percent_value = $discount / 100;
                    $dis_value = $unit_price_with_vat * $percent_value;
                    $tpwt = $unit_price_with_vat - @$dis_value;

                    $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                    $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                    $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                } else {
                    $tpwt = $unit_price_with_vat;
                }
                $order_product->total_price_with_vat = round($tpwt, 2);
                $order_product->save();
                $order_total += @$order_product->total_price_with_vat;

                $order_history = new OrderHistory;
                $order_history->user_id = $user->id;
                $order_history->reference_number = $order_product->product->refrence_code;
                $order_history->column_name = "Qty Sent";
                $order_history->old_value = null;
                $order_history->new_value = $order_product->qty_shipped;
                $order_history->order_id = $order_product->order_id;
                $order_history->save();

                if ($order_product->pcs_shipped != null) {
                    $order_history = new OrderHistory;
                    $order_history->user_id = $user->id;
                    $order_history->reference_number = $order_product->product->refrence_code;
                    $order_history->column_name = "Pieces Sent";
                    $order_history->old_value = null;
                    $order_history->new_value = $order_product->pcs_shipped;
                    $order_history->order_id = $order_product->order_id;
                    $order_history->save();
                }
            }

            foreach ($order_products_billed as $order_product) {
                $item_unit_price = number_format($order_product->unit_price, 2, '.', '');

                if ($order_product->is_retail == 'qty') {
                    // $total_price = $order_product->qty_shipped * $order_product->unit_price;
                    $total_price = $item_unit_price * $order_product->qty_shipped;
                    $num = $order_product->qty_shipped;
                } else if ($order_product->is_retail == 'pieces') {
                    // $total_price = $order_product->pcs_shipped * $order_product->unit_price;
                    $total_price = $item_unit_price * $order_product->pcs_shipped;
                    $num = $order_product->pcs_shipped;
                }
                // $product = $order_product->product;
                $discount = $order_product->discount;

                if ($discount != null) {
                    $dis = $discount / 100;
                    $discount_value = $dis * $total_price;
                    $result = $total_price - $discount_value;
                } else {
                    $result = $total_price;
                }

                $order_product->total_price = round($result, 2);

                // $order_product->total_price_with_vat = (($order_product->vat/100)*$result)+$result;
                // $unit_price = round($order_product->unit_price,2);
                $vat = $order_product->vat;
                // $vat_amount = @$unit_price * ( @$vat / 100 );
                //  $vat_amountt = @$item_unit_price * ( @$vat / 100 );
                // $vat_amount = number_format(floor($vat_amountt*10000)/10000,4,'.','');

                $vat_amountt = @$item_unit_price * (@$vat / 100);
                $vat_amount = number_format($vat_amountt, 4, '.', '');
                $vat_amount_total_over_item = $vat_amount * $num;
                $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                    // $unit_price_with_vat = $order_product->unit_price_with_vat * $num;
                    // $unit_price_with_vat = number_format(floor(($order_product->unit_price_with_vat * $num)*10000)/10000,4,'.','');
                    $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                } else {
                    // $unit_price_with_vat = round(@$unit_price+@$vat_amount,2) * $num;
                    //   $unit_price_with_vatt = (@$item_unit_price+@$vat_amount) * $num;
                    // $unit_price_with_vat = number_format(floor($unit_price_with_vatt*10000)/10000,4,'.','');
                    $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                }
                if (@$discount !== null) {
                    $percent_value = $discount / 100;
                    $dis_value = $unit_price_with_vat * $percent_value;
                    $tpwt = $unit_price_with_vat - @$dis_value;

                    $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                    $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                    $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                } else {
                    $tpwt = $unit_price_with_vat;
                }
                $order_product->total_price_with_vat = round($tpwt, 2);
                $order_product->save();
                $order_total += @$order_product->total_price_with_vat;
            }

            $order->primary_status = 3;
            if ($order->ecommerce_order == 1) {
                // This will directly goes to paid status
                $order->status = 24;
                $order_products = OrderProduct::where('order_id', $order->id)->update([
                    'status' => 24
                ]);
            } else {
                $order->status = 11;
            }

            $order->total_amount = @$order_total;
            $order->converted_to_invoice_on = Carbon::now();
            // $order->target_ship_date = Carbon::now()->format('Y-m-d');

            $inv_status = Status::where('id', 3)->first();
            $counter_formula = $inv_status->counter_formula;
            $counter_formula = explode('-', $counter_formula);
            $counter_length = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;

            $date = Carbon::now();
            $date = $date->format($counter_formula[0]);
            $company_prefix = @$user->getCompany->prefix;
            if ($order->ecommerce_order == 1) {
                $quotation_qry = QuotationConfig::where('section', 'ecommerce_configuration')->first();
                $quotation_config =  unserialize($quotation_qry->print_prefrences);
                $default_warehouse = $quotation_config['status'][5];
                $warehouse_short_code = Warehouse::select('order_short_code')->where('id', $default_warehouse)->first();
                $company_prefix = $warehouse_short_code->order_short_code;
            } else {
                $company_prefix = $company_prefix;
            }
            // $company_prefix          = @$order->customer->primary_sale_person->getCompany->prefix;
            $draft_customer_category = $order->customer->CustomerCategory;

            if ($order->customer->category_id == 6 && $order->ecommerce_order != 1) {
                $p_cat = CustomerCategory::where('id', 4)->first();
                $ref_prefix = $p_cat->short_code;
            } else {
                $ref_prefix              = $draft_customer_category->short_code;
            }

            if ($order->is_vat == 0) {
                $status_prefix           = $inv_status->prefix . $company_prefix;
                $c_p_ref = Order::where('in_status_prefix', '=', $status_prefix)->where('in_ref_prefix', $ref_prefix)->where('in_ref_id', 'LIKE', "$date%")->orderby('converted_to_invoice_on', 'DESC')->first();
            } else {
                $status_prefix           = "DO" . $company_prefix;
                $c_p_ref = Order::where('in_status_prefix', '=', $status_prefix)->where('manual_ref_no', 'LIKE', "$date%")->orderby('converted_to_invoice_on', 'DESC')->first();
            }

            $str = @$c_p_ref->in_ref_id;
            $onlyIncrementGet = substr($str, 4);
            if ($str == null) {
                $onlyIncrementGet = 0;
            }
            $system_gen_no = str_pad(@$onlyIncrementGet + 1, $counter_length, 0, STR_PAD_LEFT);
            $system_gen_no = $date . $system_gen_no;

            $order->in_status_prefix = $status_prefix;

            $order->in_ref_id = $system_gen_no;
            if ($order->is_vat == 0) {
                $order->in_ref_prefix = $ref_prefix;
            }
            if ($order->is_vat == 1) {
                $order->manual_ref_no = $system_gen_no;
            }
            $order->full_inv_no = $status_prefix . '-' . $ref_prefix . $system_gen_no;
            // $order->full_inv_no = 'unique-1234';
            $order->save();

            if ($order->ecommerce_order == 1) {
                $base_link  = config('app.ecom_url');
                $order_payment_ref = new OrdersPaymentRef;
                $order_payment_ref->payment_reference_no = 'EC' . $order->in_ref_id;
                $order_payment_ref->customer_id = $order->customer_id;
                $order_payment_ref->payment_method = 3;
                $order_payment_ref->received_date = $rec_date;
                $order_payment_ref->save();

                $order_transaction                   = new OrderTransaction;
                $order_transaction->order_id         = $request['order_id'];
                $order_transaction->customer_id         = $order->customer_id;
                $order_transaction->order_ref_no         = $order->ref_id;
                $order_transaction->user_id         = $order->user_id;
                $order_transaction->payment_method_id = 3;
                $order_transaction->payment_reference_no = $order_payment_ref->id;
                $order_transaction->received_date    = $rec_date_ot;
                $order_transaction->total_received   = $order->total_amount;
                $order_transaction->vat_total_paid   = $order->vat_total_paid;
                $order_transaction->non_vat_total_paid   = $order->non_vat_total_paid;
                $order_transaction->save();

                //for status at ecom date sending through APi
                $uri = $base_link . "/api/updateorderstatus/" . $order->ecommerce_order_no . "/" . $order->primary_status . "/" . $order->status;
                // $test =  $this->sendRequest($uri);
                $test =  app('App\Http\Controllers\Warehouse\HomeController')->sendRequest($uri);
            }

            $is_partial = false;
            $partial_pick = QuotationConfig::where('section', 'partial_pick_instruction')->first();
            if ($partial_pick != null) {
                if ($partial_pick->display_prefrences == 1 || $partial_pick->display_prefrences == "1") {
                    $ordered_products = OrderProduct::where('order_id', $order->id)->where('is_billed', '=', 'Product')->get();
                    if ($ordered_products->count() > 0) {
                        foreach ($ordered_products as $products) {
                            if ($products->qty_shipped < $products->quantity) {
                                $is_partial = true;
                            }
                        }
                    }

                    if ($is_partial == true) {
                        //To send email for partial order
                        $to_email = config('app.partial_email');
                        $fr_email = config('app.mail_username');
                        Mail::to($to_email)->send(new PartialMail($order->id, $fr_email));
                    }
                }
            }
            $order->is_processing = 0;
            $order->save();


            DB::commit();
            // $error_msg = response()->json(['success' => true, 'full_inv_no' => $order->full_inv_no, 'order_id' => $order->id]);
            $error_msg = json_encode(['success' => true, 'full_inv_no' => $order->full_inv_no, 'order_id' => $order->id]);
            $this->updateExportStatus($error_msg, $user->id);
            return ;
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            $order_check = Order::where('id', $request['order_id'])->first();
            $order_check->is_processing = 0;
            $order_check->save();
            $this->failed($e);
            // $c_p_ref = Order::where('in_status_prefix','=',$status_prefix)->where('in_ref_prefix',$ref_prefix)->where('in_ref_id',$system_gen_no)->first();
            // $c_p_ref->converted_to_invoice_on = carbon::now();
            // $c_p_ref->save();
            // DB::commit();
        }
        catch(MaxAttemptsExceededException $e) {
            DB::rollBack();
            $order_check = Order::where('id', $request['order_id'])->first();
            $order_check->is_processing = 0;
            $order_check->save();
            $this->failed($e);
        }
    }

    public function failed($exception)
    {
      ExportStatus::where('type', 'pick_instruction_job')->where('user_id', $this->user->id)->update(['status'=>2,'exception'=>$exception->getLine() . ', ' .$exception->getMessage()]);
      $failedJobException=new FailedJobException();
      $failedJobException->type="pick_instruction_job";
      $failedJobException->exception=$exception->getLine() . ', ' .$exception->getMessage();
      $failedJobException->save();
    }

    public function updateExportStatus($error_msg, $user_id)
    {
        $job_status = ExportStatus::where('type', 'pick_instruction_job')->where('user_id', $user_id)->first();
        $job_status->status = 0;
        $job_status->exception = null;
        $job_status->error_msgs = $error_msg;
        $job_status->save();
    }
}
