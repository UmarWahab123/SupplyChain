<?php

namespace App\Helpers;

use DB;
use Auth;
use Carbon\Carbon;
use App\QuotationConfig;
use App\Models\Common\Status;
use App\Models\Common\PoGroup;
use App\Models\Common\Product;
use App\Models\Common\Supplier;
use App\DraftPurchaseOrderHistory;
use App\Models\Common\Order\Order;
use App\Models\Common\PoGroupDetail;
use App\Models\Common\StockOutHistory;
use App\Models\Common\SupplierProducts;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\StockManagementIn;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\StockManagementOut;
use App\TransferDocumentReservedQuantity;
use App\Models\Common\PurchaseOrderDocument;
use App\Models\Common\DraftPurchaseOrderNote;
use App\Models\Common\Order\OrderStatusHistory;
use App\Models\Common\DraftPurchaseOrderDocument;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderNote;
use App\Models\Common\PurchaseOrders\DraftPurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PoGroupStatusHistory;
use App\Models\Common\PurchaseOrders\DraftPurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PurchaseOrderStatusHistory;

class TransferDocumentHelper
{
    public static function doActionDraftTd($request)
    {
        DB::beginTransaction();
        $action = $request->action;
        if ($action == 'save') {
            $draft_po = DraftPurchaseOrder::find($request->draft_po_id);

            if ($draft_po->draftPoDetail()->count() == 0) {
                $errorMsg =  'Please add some products in the Transfer Document';
                return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
            }

            $draft_po_detail = DraftPurchaseOrderDetail::where('po_id', $request->draft_po_id)->get();

            if ($draft_po_detail->count() > 0) {
                foreach ($draft_po_detail as $value) {
                    if ($value->quantity == null) {
                        $errorMsg =  'Quantity cannot be Null, please enter quantity of the added items';
                        return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                    }
                    if ($value->get_td_reserved->count() > 0) {
                        $total_res = $value->get_td_reserved()->sum('reserved_quantity');
                        $quant = $value->quantity;

                        if ($total_res != $quant) {
                            return response()->json(['res_error' => true, 'item' => $value->getProduct->refrence_code]);
                        }
                    }

                    $warehouse_stock = WarehouseProduct::where('product_id', $value->product_id)->where('warehouse_id', $value->draftPo->from_warehouse_id)->first();
                    if ($warehouse_stock) {
                        $pi_config = [];
                        $error_msg =  '';
                        $pi_config = QuotationConfig::where('section', 'pick_instruction')->first();
                        if ($pi_config != null) {
                            $pi_config = unserialize($pi_config->print_prefrences);
                        }
                        if ($pi_config['pi_confirming_condition'] == 2) {
                            $current_stock = $warehouse_stock->current_quantity;
                            $error_msg =  'Current Qty is less then the ordered qty for ';
                        } else if ($pi_config['pi_confirming_condition'] == 3) {
                            $current_stock = $warehouse_stock->available_quantity;
                            $error_msg =  'Available Qty is less then the ordered qty for ';
                        }
                        if ($pi_config['pi_confirming_condition'] == 2 || $pi_config['pi_confirming_condition'] == 3) {
                            if ($value->quantity > $current_stock) {
                                return response()->json(['success' => false, 'errorMsg' => $error_msg . $value->getProduct->refrence_code]);
                            }
                        }
                    }
                }
            }

            $td_status = Status::where('id', 19)->first();
            $counter_formula = $td_status->counter_formula;
            $counter_formula = explode('-', $counter_formula);
            $counter_length  = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;

            $year = Carbon::now()->year;
            $month = Carbon::now()->month;

            $year = substr($year, -2);
            $month = sprintf("%02d", $month);
            $date = $year . $month;

            $c_p_ref = PurchaseOrder::where('ref_id', 'LIKE', "$date%")->orderby('id', 'DESC')->first();
            $str = @$c_p_ref->ref_id;
            $onlyIncrementGet = substr($str, 4);
            if ($str == NULL) {
                // $str = $date.'0';
                $onlyIncrementGet = 0;
            }
            $system_gen_no = $date . str_pad(@$onlyIncrementGet + 1, $counter_length, 0, STR_PAD_LEFT);
            $date = date('y-m-d');

            $purchaseOrder = PurchaseOrder::create([
                'ref_id'              => $system_gen_no,
                'status'              => 20,
                'total'               => $draft_po->total,
                'total_quantity'      => $draft_po->total_quantity,
                'total_gross_weight'  => $draft_po->total_gross_weight,
                'total_import_tax_book' => $draft_po->total_import_tax_book,
                'total_import_tax_book_price' => $draft_po->total_import_tax_book_price,
                'supplier_id'         => $draft_po->supplier_id,
                'from_warehouse_id'   => $draft_po->from_warehouse_id,
                'created_by'          => Auth::user()->id,
                'memo'                => @$draft_po->memo,
                'payment_terms_id'    => $draft_po->payment_terms_id,
                'payment_due_date'    => $draft_po->payment_due_date,
                'target_receive_date' => $draft_po->target_receive_date,
                'transfer_date'       => $draft_po->transfer_date,
                'target_receive_date' => $draft_po->target_receive_date,
                'confirm_date'        => $date,
                'to_warehouse_id'     => $draft_po->to_warehouse_id,
            ]);

            // PO status history maintaining
            $page_status = Status::select('title')->whereIn('id', [20])->pluck('title')->toArray();
            $poStatusHistory = new PurchaseOrderStatusHistory;
            $poStatusHistory->user_id    = Auth::user()->id;
            $poStatusHistory->po_id      = $purchaseOrder->id;
            $poStatusHistory->status     = 'Created';
            $poStatusHistory->new_status = $page_status[0];
            $poStatusHistory->save();

            $draft_po_detail = DraftPurchaseOrderDetail::where('po_id', $draft_po->id)->get();

            foreach ($draft_po_detail as $dpo_detail) {
                $product = Product::where('id', $dpo_detail->product_id)->first();
                $new_purchase_order_detail = PurchaseOrderDetail::create([
                    'po_id'            => $purchaseOrder->id,
                    'order_id'         => NULL,
                    'customer_id'      => NULL,
                    'order_product_id' => NULL,
                    'product_id'       => $dpo_detail->product_id,
                    'pod_import_tax_book' => $dpo_detail->pod_import_tax_book,
                    'pod_unit_price'   => $dpo_detail->pod_unit_price,
                    'pod_gross_weight' => $dpo_detail->pod_gross_weight,
                    'quantity'         => $dpo_detail->quantity,
                    'pod_total_gross_weight' => $dpo_detail->pod_total_gross_weight,
                    'pod_total_unit_price' => $dpo_detail->pod_total_unit_price,
                    'discount' => $dpo_detail->discount,
                    'pod_import_tax_book_price' => $dpo_detail->pod_import_tax_book_price,
                    'warehouse_id'     => $draft_po->to_warehouse_id,
                    'temperature_c'    => $product->product_temprature_c,
                    'good_type'        => $product->type_id,
                    'supplier_packaging' => $dpo_detail->supplier_packaging,
                    'billed_unit_per_package' => $dpo_detail->billed_unit_per_package,
                    'supplier_invoice_number' => $dpo_detail->supplier_invoice_number,
                    'custom_invoice_number' => $dpo_detail->custom_invoice_number,
                    'custom_line_number' => $dpo_detail->custom_line_number,
                ]);

                if ($dpo_detail->get_td_reserved->count() > 0) {
                    foreach ($dpo_detail->get_td_reserved as $detail) {
                        if ($detail) {
                            $detail->po_id = $purchaseOrder->id;
                            $detail->pod_id = $new_purchase_order_detail->id;
                            $detail->draft_pod_id = null;
                            $detail->draft_po_id = null;
                            $detail->save();
                        }
                    }
                }
            }

            // getting documents of draft_Po
            $draft_po_docs = DraftPurchaseOrderDocument::where('po_id', $request->draft_po_id)->get();
            foreach ($draft_po_docs as $docs) {
                PurchaseOrderDocument::create([
                    'po_id'     => $purchaseOrder->id,
                    'file_name' => $docs->file_name
                ]);
            }

            $draft_notes = DraftPurchaseOrderNote::where('po_id', $request->draft_po_id)->get();
            if (@$draft_notes != null) {
                foreach ($draft_notes as $note) {
                    $order_note = new PurchaseOrderNote;
                    $order_note->po_id    = $purchaseOrder->id;
                    $order_note->note                = $note->note;
                    $order_note->created_by = @$note->created_by;
                    $order_note->save();
                }
            }

            $delete_draft_po = DraftPurchaseOrder::find($request->draft_po_id);
            if ($request->copy_and_update == 'yes') {
            } else {
                $delete_draft_po->draftPoDetail()->delete();
                $delete_draft_po->draft_po_notes()->delete();
                $delete_draft_po->delete();

                $delete_draft_po_docs = DraftPurchaseOrderDocument::where('po_id', $request->draft_po_id)->get();
                foreach ($delete_draft_po_docs as $del) {
                    $del->delete();
                }
            }
            // DB::commit();

            $errorMsg =  'Transfer Document Created Successfully.';
            $confirm_from_draft = QuotationConfig::where('section', 'warehouse_management_page')->first();
            if ($confirm_from_draft) {
                $globalaccessForWarehouse = unserialize($confirm_from_draft->print_prefrences);
                foreach ($globalaccessForWarehouse as $val) {
                    if ($val['slug'] === "has_warehouse_account") {
                        $has_warehouse_account = $val['status'];
                    }
                }
            } else {
                $has_warehouse_account = '';
            }
            if ($has_warehouse_account == 1) {
                $myRequest = new \Illuminate\Http\Request();
                $myRequest->setMethod('POST');
                $myRequest->request->add(['id' => $purchaseOrder->id]);

                // $done = app('App\Http\Controllers\Purchasing\PurchaseOrderController')->confirmTransferDocument($myRequest);
                $done = TransferDocumentHelper::confirmTransferDocument($myRequest);
                $enc = json_decode($done->getContent());
                // dd($enc->success);
                $warehouse_receiving = $enc->success;
                if ($warehouse_receiving) {
                    $myRequest2 = new \Illuminate\Http\Request();
                    $myRequest2->setMethod('POST');
                    $myRequest2->request->add(['po_id' => $purchaseOrder->id]);

                    // $done2 = app('App\Http\Controllers\Warehouse\HomeController')->confirmTransferPickInstruction($myRequest2);
                    $done2 = TransferDocumentHelper::confirmTransferPickInstruction($myRequest2);
                    $enc2 = json_decode($done2->getContent());
                    // dd($enc->success);
                    if ($enc2->success) {
                        $warehouse_pick_instruction_td = $enc2->success;
                        $po_group_of_td = PoGroupDetail::select('po_group_id')->where('purchase_order_id', $purchaseOrder->id)->first();
                        if ($po_group_of_td != null) {
                            $myRequest3 = new \Illuminate\Http\Request();
                            $myRequest3->setMethod('POST');
                            $myRequest3->request->add(['id' => $po_group_of_td->po_group_id]);

                            // $done3 = app('App\Http\Controllers\Warehouse\PurchaseOrderGroupsController')->confirmTransferGroup($myRequest3);
                            $done3 = TransferDocumentHelper::confirmTransferGroup($myRequest3);
                            $enc3 = json_decode($done2->getContent());
                            // dd($enc->success);
                            $warehouse_group_id_td = $enc3->success;
                        }
                    } else if ($enc2->success == false && $enc2->type == 'stock') {
                        if ($enc2->stock_qty == "less_than_order") {
                            $errorMsg = 'Current Stock is less than Order QTY for ' . $enc2->product;
                        } else {
                            $errorMsg = 'Current Stock is less than or equal to 0 for ' . $enc2->product;
                        }
                        DB::rollBack();
                        return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                    } else if ($enc2->success == false && $enc2->type == 'available') {
                        $error = true;
                        if ($enc2->available_qty == "less_than_order") {
                            $errorMsg = 'Available Stock is less than Order QTY for ' . $enc2->product;
                        } else {
                            $errorMsg = 'Available Stock is less than or equal to 0 for ' . $enc2->product;
                        }
                        DB::rollBack();
                        return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                    }
                } else {
                    if ($enc->res_error) {
                        $errorMsg = 'Current Stock is less than Order QTY for ' . $enc->item;
                    } else {
                        $errorMsg = $enc->errorMsg;
                    }
                    DB::rollBack();
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }
            }
            DB::commit();
            return response()->json(['success' => true, 'errorMsg' => $errorMsg]);
        }
    }

    public static function warehouseSaveInDraftPo($request)
    {
        $draft_po = DraftPurchaseOrder::find($request->draft_po_id);
        $draft_po->to_warehouse_id = $request->warehouse_id;
        $draft_po->save();

        $check_bonded = @$draft_po->getWarehoue->is_bonded;
        return response()->json([
            'success' => true,
            'is_bonded' => $check_bonded
        ]);
    }

    public static function confirmTransferDocument($request)
    {
        $total_import_tax_book_price = null;
        $total_vat_actual_price = null;
        $confirm_date = date("Y-m-d");
        $po = PurchaseOrder::with('PurchaseOrderDetail')->find($request->id);

        if ($po->status == 21 || $po->status == '21') {
            $errorMsg =  'This Transfer Document is already confirmed !!!';
            $status = "transfer-document-dashboard";
            return response()->json(['success' => false, 'errorMsg' => $errorMsg, 'status' => $status]);
        }

        $po_detail = PurchaseOrderDetail::where('po_id', $request->id)->get();
        if ($po_detail->count() > 0) {
            foreach ($po_detail as $value) {
                if ($value->quantity == null || $value->quantity == 0) {
                    $errorMsg =  'Quantity cannot be Null or Zero, please enter the quantity of the added items';
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }

                if ($value->get_td_reserved->count() > 0) {
                    $total_res = $value->get_td_reserved()->sum('reserved_quantity');
                    $quant = $value->quantity;

                    if ($total_res != $quant) {
                        return response()->json(['res_error' => true, 'item' => $value->product->refrence_code]);
                    }
                } else {
                    //Transfer Documnet Reserve Quantity
                    $stock_m_outs = StockManagementOut::where('warehouse_id', $po->from_warehouse_id)->where('product_id', $value->product_id)->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();
                    $res = null;
                    if($stock_m_outs->count() > 0){
                        foreach ($stock_m_outs as $stock_m_out) {
                            $quantity_out = $value->quantity;
                            $res = PurchaseOrderDetail::reserveQtyForTD($res != null ? $res : $quantity_out, $stock_m_out, $value);
                            if ($res == 0) {
                                break;
                            }
                        } 
                    }else{
                        $res = $value->quantity;
                    }
                    if (!($res == 0)) {
                        // return response()->json(['success' => false, 'stock_qty' => 'less_than_order', 'product' => $value->product->refrence_code]);
                        $res = PurchaseOrderDetail::reserveQtyForTDWithoutStock($res, $value);
                    }
                    // dd($res);
                }
            }
        }

        $po->confirm_date = $confirm_date;
        $po->status = 21;
        $po->save();

        // PO status history maintaining
        if (@$has_warehouse_account == 1) {
            $page_status = Status::select('title')->whereIn('id', [20, 22])->pluck('title')->toArray();
        } else {
            $page_status = Status::select('title')->whereIn('id', [20, 21])->pluck('title')->toArray();
        }
        $poStatusHistory = new PurchaseOrderStatusHistory;
        $poStatusHistory->user_id    = Auth::user()->id;
        $poStatusHistory->po_id      = $po->id;
        $poStatusHistory->status     = $page_status[0];
        $poStatusHistory->new_status = $page_status[1];
        $poStatusHistory->save();

        //  creating group of generated transfer document
        $total_quantity              = null;
        $total_price                 = null;
        $total_import_tax_book_price = null;
        $total_vat_actual_price      = null;
        $total_gross_weight          = null;
        $po_group                    = new PoGroup;

        // generating ref #
        $year2  = Carbon::now()->year;
        $month2 = Carbon::now()->month;

        $year2  = substr($year2, -2);
        $month2 = sprintf("%02d", $month2);
        $date  = $year2 . $month2;

        $c_p_ref2 = PoGroup::where('ref_id', 'LIKE', "$date%")->whereNotNull('from_warehouse_id')->orderby('id', 'DESC')->first();
        $str2 = @$c_p_ref2->ref_id;
        $onlyIncrementGet2 = substr($str2, 4);
        if ($str2 == NULL) {
            $onlyIncrementGet2 = 0;
        }
        $system_gen_no2 = $date . str_pad(@$onlyIncrementGet2 + 1, STR_PAD_LEFT);

        $po_group->ref_id                         = $system_gen_no2;
        $po_group->bill_of_landing_or_airway_bill = '';
        $po_group->bill_of_lading                 = '';
        $po_group->airway_bill                    = '';
        $po_group->courier                        = '';
        $po_group->target_receive_date            = $po->target_receive_date;
        $po_group->warehouse_id                   = $po->to_warehouse_id;
        $po_group->from_warehouse_id              = $po->from_warehouse_id;
        $po_group->save();

        $po_group_detail                    = new PoGroupDetail;
        $po_group_detail->po_group_id       = $po_group->id;
        $po_group_detail->purchase_order_id = $po->id;
        $po_group_detail->save();

        // $purchase_order = PurchaseOrder::find($po->id);
        foreach ($po->PurchaseOrderDetail as $p_o_d) {
            $total_quantity +=  $p_o_d->quantity;
            if ($p_o_d->order_product_id != null) {
                $order_product = $p_o_d->order_product;
                $order         = $order_product->get_order;
                if ($order->primary_status !== 3 && $order->primary_status !== 17) {
                    $order_product->status = 9;
                    $order_product->save();

                    $order_products_status_count = OrderProduct::where('order_id', $p_o_d->order_id)->where('is_billed', '=', 'Product')->where('status', '!=', 9)->count();
                    if ($order_products_status_count == 0) {
                        $order->status = 9;
                        $order->save();
                        $order_history             = new OrderStatusHistory();
                        $order_history->user_id    = Auth::user()->id;
                        $order_history->order_id   = @$order->id;
                        $order_history->status     = 'DI(Purchasing)';
                        $order_history->new_status = 'DI(Importing)';
                        $order_history->save();
                    }
                }
            }

            if ($po != null) {
                $new_his = new QuantityReservedHistory;
                $re      = $new_his->updateTDReservedQuantity($po, $p_o_d, $p_o_d->quantity, $p_o_d->quantity, 'Reserved Quantity by confirming TD', 'add');

                //   DB::beginTransaction();
                //   try
                //   {
                //     DB::commit();
                //   }
                //   catch(\Excepion $e)
                //   {
                //     DB::rollBack();
                //   }
            }
        }

        $total_import_tax_book_price += $po->total_import_tax_book_price;
        $total_vat_actual_price += $po->total_vat_actual_price;
        $total_gross_weight += $po->total_gross_weight;
        $po->status = 21;
        $po->save();
        // dd($purchase_order);

        $po_group->total_quantity              = $total_quantity;
        $po_group->po_group_import_tax_book    = $total_import_tax_book_price;
        $po_group->po_group_vat_actual         = $total_vat_actual_price;
        $po_group->po_group_total_gross_weight = $total_gross_weight;
        $po_group->save();

        $group_status_history              = new PoGroupStatusHistory();
        $group_status_history->user_id     = Auth::user()->id;
        $group_status_history->po_group_id = @$po_group->id;
        $group_status_history->status      = 'Created';
        $group_status_history->new_status  = 'Open Product Receiving Queue';
        $group_status_history->save();

        $status = "dfs";
        session(['td_status' => 21]);
        return response()->json(['success' => true, 'status' => $status]);
    }

    public static function confirmTransferPickInstruction($request)
    {
        $redirect_response = '';
        $confirm_from_draft = QuotationConfig::where('section', 'warehouse_management_page')->first();
        if ($confirm_from_draft) {
            $globalaccessForWarehouse = unserialize($confirm_from_draft->print_prefrences);
            foreach ($globalaccessForWarehouse as $val) {
                if ($val['slug'] === "has_warehouse_account") {
                    $has_warehouse_account = $val['status'];
                }
            }
        } else {
            $has_warehouse_account = '';
        }
        $purchaseOrder = PurchaseOrder::find($request->po_id);

        if ($purchaseOrder->status == 22) {
            $redirect_response = 'dashboard';
            $errorMsg = "Pick instruction is already confirmed.";
            return response()->json(['success' => false, 'errorMsg' => $errorMsg, 'redirect' => $redirect_response]);
        }

        if ($has_warehouse_account == 1) {
            $query = PurchaseOrderDetail::with('PurchaseOrder')->where('po_id', $request->po_id)->whereNotNull('product_id')->orderBy('product_id', 'ASC')->get();
            foreach ($query as $item) {
                $item->trasnfer_qty_shipped = $item->quantity;
                $item->save();
            }
        }

        $po_detail_checks = PurchaseOrderDetail::with('PurchaseOrder', 'product')->where('po_id', $purchaseOrder->id)->where('is_billed', '=', 'Product')->get();
        foreach ($po_detail_checks as $pod) {
            if ($pod->trasnfer_qty_shipped === null) {
                $errorMsg = "Quantity Shipped Cannot Be Null, Please Enter The Quantity Shipped Of All Items.";
                return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
            } else {
                $pi_config = [];
                $pids = null;
                $pi_config = QuotationConfig::where('section', 'pick_instruction')->first();
                if ($pi_config != null) {
                    $pi_config = unserialize($pi_config->print_prefrences);
                }

                $pids = PurchaseOrder::where('status', 21)->where('id', '!=', $pod->po_id)->WhereNotNull('from_warehouse_id')->whereHas('PoWarehouse', function ($qq) use ($pod) {
                    $qq->where('from_warehouse_id', $pod->PurchaseOrder->from_warehouse_id);
                })->where('id', '!=', $pod->po_id)->pluck('id')->toArray();

                $order_ids = Order::where('primary_status', 2)->whereHas('order_products', function ($q) use ($pod) {
                    $q->where('from_warehouse_id', $pod->PurchaseOrder->from_warehouse_id);
                })->pluck('id')->toArray();

                $warehouse_product = null;
                if ($pi_config['pi_confirming_condition'] == 2) {
                    $warehouse_product = WarehouseProduct::where('product_id', $pod->product_id)->where('warehouse_id', $pod->PurchaseOrder->from_warehouse_id)->first();
                    $stock_qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity : ' 0';
                    if ($pod->quantity > $stock_qty) {
                        return response()->json(['success' => false, 'type' => 'stock', 'stock_qty' => 'less_than_order', 'product' => $pod->product->refrence_code]);
                    } else {
                        if ($stock_qty <= 0) {
                            if ($stock_qty < 0) {
                                return response()->json(['success' => false, 'type' => 'stock', 'stock_qty' => 'less_than_zero', 'product' => $pod->product->refrence_code]);
                            } else if ($stock_qty == 0) {
                                return response()->json(['success' => false, 'type' => 'stock', 'stock_qty' => 'equals_to_zero', 'product' => $pod->product->refrence_code]);
                            }
                        }
                    }
                } elseif ($pi_config['pi_confirming_condition'] == 3) {
                    $warehouse_product = WarehouseProduct::where('product_id', $pod->product_id)->where('warehouse_id', $pod->PurchaseOrder->from_warehouse_id)->first();
                    $stock_qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity : ' 0';
                    $order_rsv_qty = OrderProduct::whereIn('order_id', $order_ids)->where('product_id', $pod->product_id)->sum('quantity');
                    $pick_rsv_qty = PurchaseOrderDetail::whereIn('po_id', $pids)->where('product_id', $pod->product_id)->sum('quantity');
                    $available_qty = $stock_qty - ($order_rsv_qty + $pick_rsv_qty);
                    if ($pod->quantity > $available_qty) {
                        return response()->json(['success' => false, 'type' => 'available', 'available_qty' => 'less_than_order', 'product' => $pod->product->refrence_code]);
                    } else {
                        if ($available_qty <= 0) {
                            if ($available_qty < 0) {
                                return response()->json(['success' => false, 'type' => 'available', 'available_qty' => 'less_than_zero', 'product' => $pod->product->refrence_code]);
                            } else if ($available_qty == 0) {
                                return response()->json(['success' => false, 'type' => 'available', 'available_qty' => 'equals_to_zero', 'product' => $pod->product->refrence_code]);
                            }
                        }
                    }
                }
            }
        }

        $po_detail = PurchaseOrderDetail::with('PurchaseOrder', 'product.sellingUnits', 'get_td_reserved', 'order_product.get_order')->where('po_id', $purchaseOrder->id)->where('is_billed', '=', 'Product')->get();
        foreach ($po_detail as $order_product) {
            $supply_from_id = $order_product->PurchaseOrder->from_warehouse_id;

            $decimal_places = $order_product->product->sellingUnits->decimal_places;
            if ($decimal_places == 0) {
                $quantity_inv   = round($order_product->trasnfer_qty_shipped, 0);
            } elseif ($decimal_places == 1) {
                $quantity_inv   = round($order_product->trasnfer_qty_shipped, 1);
            } elseif ($decimal_places == 2) {
                $quantity_inv   = round($order_product->trasnfer_qty_shipped, 2);
            } elseif ($decimal_places == 3) {
                $quantity_inv   = round($order_product->trasnfer_qty_shipped, 3);
            } else {
                $quantity_inv   = round($order_product->trasnfer_qty_shipped, 4);
            }
            if ($quantity_inv !== null) {
                $quantity_full_transferred = $quantity_inv;
                if ($order_product->trasnfer_expiration_date != null) {
                    $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', @$supply_from_id)->where('expiration_date', $order_product->trasnfer_expiration_date)->whereNotNull('expiration_date')->first();
                    if ($stock == null) {
                        $stock = new StockManagementIn;
                        $stock->title = 'Adjustment';
                        $stock->product_id = $order_product->product_id;
                        $stock->created_by = Auth::user()->id;
                        $stock->warehouse_id = @$supply_from_id;
                        $stock->expiration_date = $order_product->trasnfer_expiration_date;
                        $stock->save();
                    }
                    if ($stock != null) {
                        $stock_out = TransferDocumentHelper::stockManagement($stock, $order_product, -$quantity_inv, null, null, null, @$supply_from_id);

                        if ($order_product->get_td_reserved->count() > 0) {
                            foreach ($order_product->get_td_reserved as $prod) {
                                $reserved_quantity = round($prod->reserved_quantity, 4);
                                $stock_out->parent_id_in .= $prod->stock_id . ',';
                                $stock_out->save();
                                if($prod->stock_id != null){
                                    $stock_out->parent_id_in .= $prod->stock_id . ',';
                                    if(abs($reserved_quantity) < abs($quantity_inv)){
                                        $stock_out->available_stock += $prod->reserved_quantity;  
                                        $stock_out->save();
                                        $quantity_inv -= round(abs($reserved_quantity), 4);              
                                    }else{
                                        $stock_out->available_stock = 0;
                                        $stock_out->save();
                                        break;                
                                    }
                                }
                            }
                        } else {

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
                                            $new_stock_out_history = (new StockOutHistory())->setHistoryForPO($out, $stock_out, $order_product, round(abs($history_quantity), 4));
                                        } else {
                                            $history_quantity = $out->available_stock;
                                            $stock_out->parent_id_in .= $out->id . ',';
                                            $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                            $out->available_stock = 0;
                                            $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out, $stock_out, $order_product, round(abs($history_quantity), 4));
                                        }
                                        $out->save();
                                        $stock_out->save();
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', @$supply_from_id)->whereNotNull('expiration_date')->orderBy('expiration_date', "ASC")->get();
                    $shipped = $quantity_inv;
                    foreach ($stock as $st) {
                        $stock_out_in = StockManagementOut::where('smi_id', $st->id)->sum('quantity_in');
                        $stock_out_out = StockManagementOut::where('smi_id', $st->id)->sum('quantity_out');
                        $balance = ($stock_out_in) + ($stock_out_out);
                        $balance = round($balance, 3);
                        if ($balance > 0) {
                            $inStock = $balance - $shipped;
                            if ($inStock >= 0) {
                                $stock_out = TransferDocumentHelper::stockManagement($st, $order_product, -$shipped, null, null, null, @$supply_from_id);

                                if ($order_product->get_td_reserved->count() > 0) {
                                    foreach ($order_product->get_td_reserved as $prod) {

                                        // $stock_out->parent_id_in .= $prod->stock_id . ',';
                                        // $stock_out->save();
                                        // if($prod->stock_id != null){
                                        //     $stock_out->parent_id_in .= $prod->stock_id . ',';
                                        //     $stock_out->available_stock += $prod->reserved_quantity;                
                                        //     $stock_out->save();
                                        // }

                                        $reserved_quantity = round($prod->reserved_quantity, 4);
                                        $stock_out->parent_id_in .= $prod->stock_id . ',';
                                        $stock_out->save();
                                        if($prod->stock_id != null){
                                            $stock_out->parent_id_in .= $prod->stock_id . ',';
                                            if(abs($reserved_quantity) < abs($shipped)){
                                                $stock_out->available_stock += $prod->reserved_quantity;  
                                                $stock_out->save();
                                                $shipped -= round(abs($reserved_quantity), 4);              
                                            }else{
                                                $stock_out->available_stock = 0;
                                                $stock_out->save();
                                                break;                
                                            }
                                        }

                                    }
                                } else {
                                    //To find from which stock the order will be deducted
                                    $find_stock = $st->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();
                                    if ($find_stock->count() > 0) {
                                        foreach ($find_stock as $out) {

                                            if ($shipped > 0) {
                                                if ($out->available_stock >= $shipped) {
                                                    $history_quantity = $stock_out->available_stock;
                                                    $stock_out->parent_id_in .= $out->id . ',';
                                                    $out->available_stock = $out->available_stock - $shipped;
                                                    $stock_out->available_stock = 0;
                                                    $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out, $stock_out, $order_product, round(abs($history_quantity), 4));
                                                } else {
                                                    $history_quantity = $out->available_stock;
                                                    $stock_out->parent_id_in .= $out->id . ',';
                                                    $stock_out->available_stock = $out->available_stock - $shipped;
                                                    $out->available_stock = 0;
                                                    $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out, $stock_out, $order_product, round(abs($history_quantity), 4));
                                                }
                                                $out->save();
                                                $stock_out->save();
                                                $shipped = abs($stock_out->available_stock);
                                            }
                                        }
                                    }
                                }
                                $shipped = 0;
                                break;
                            } else {
                                $stock_out = TransferDocumentHelper::stockManagement($st, $order_product, -$balance, null, null, null, @$supply_from_id);

                                if ($order_product->get_td_reserved->count() > 0) {
                                    foreach ($order_product->get_td_reserved as $prod) {

                                        // $stock_out->parent_id_in .= $prod->stock_id . ',';
                                        // $stock_out->save();
                                        // if($prod->stock_id != null){
                                        //     $stock_out->parent_id_in .= $prod->stock_id . ',';
                                        //     $stock_out->available_stock += $prod->reserved_quantity;                
                                        //     $stock_out->save();
                                        // }
                                        $reserved_quantity = round($prod->reserved_quantity, 4);
                                        $stock_out->parent_id_in .= $prod->stock_id . ',';
                                        $stock_out->save();
                                        if($prod->stock_id != null){
                                            $stock_out->parent_id_in .= $prod->stock_id . ',';
                                            if(abs($reserved_quantity) < abs($balance)){
                                                $stock_out->available_stock += $prod->reserved_quantity;  
                                                $stock_out->save();
                                                $balance -= round(abs($reserved_quantity), 4);              
                                            }else{
                                                $stock_out->available_stock = 0;
                                                $stock_out->save();
                                                break;                
                                            }
                                        }

                                    }
                                } else {
                                    //To find from which stock the order will be deducted
                                    $find_stock = $st->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();

                                    // $find_available_stock = $find_stock->sum('available_stock');
                                    if ($find_stock->count() > 0) {
                                        foreach ($find_stock as $out) {

                                            if ($balance > 0) {
                                                if ($out->available_stock >= $balance) {
                                                    $history_quantity = $stock_out->available_stock;
                                                    $stock_out->parent_id_in .= $out->id . ',';
                                                    $out->available_stock = $out->available_stock - $balance;
                                                    $stock_out->available_stock = 0;
                                                    $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out, $stock_out, $order_product, round(abs($history_quantity), 4));
                                                } else {
                                                    $history_quantity = $out->available_stock;
                                                    $stock_out->parent_id_in .= $out->id . ',';
                                                    $stock_out->available_stock = $out->available_stock - $balance;
                                                    $out->available_stock = 0;
                                                    $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out, $stock_out, $order_product, round(abs($history_quantity), 4));
                                                }
                                                $out->save();
                                                $stock_out->save();
                                                // $shipped = abs($stock_out->available_stock);
                                                $balance = abs($stock_out->available_stock);
                                            }
                                        }
                                        // $shipped = abs($stock_out->available_stock);

                                        // $stock_out->available_stock = 0;
                                        // $stock_out->save();
                                    }
                                }
                                // else
                                // {
                                //     $shipped = abs($inStock);
                                // }
                                $shipped = abs($inStock);
                            }
                        }
                    }
                    if ($shipped != 0) {
                        $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', @$supply_from_id)->whereNull('expiration_date')->first();
                        if ($stock == null) {
                            $stock = new StockManagementIn;
                            $stock->title = 'Adjustment';
                            $stock->product_id = $order_product->product_id;
                            $stock->created_by = Auth::user()->id;
                            $stock->warehouse_id = @$supply_from_id;
                            $stock->expiration_date = $order_product->trasnfer_expiration_date;
                            $stock->save();
                        }

                        //To find from which stock the order will be deducted
                        $find_stock = $stock->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();

                        $stock_out = TransferDocumentHelper::stockManagement($stock, $order_product, -$shipped, null, null, null, @$supply_from_id);

                        if ($order_product->get_td_reserved->count() > 0) {
                            foreach ($order_product->get_td_reserved as $prod) {

                                // $stock_out->parent_id_in .= $prod->stock_id . ',';
                                // $stock_out->save();
                                // if($prod->stock_id != null){
                                //     $stock_out->parent_id_in .= $prod->stock_id . ',';
                                //     $stock_out->available_stock += $prod->reserved_quantity;                
                                //     $stock_out->save();
                                // }
                                
                                $reserved_quantity = round($prod->reserved_quantity, 4);
                                $stock_out->parent_id_in .= $prod->stock_id . ',';
                                $stock_out->save();
                                if($prod->stock_id != null){
                                    $stock_out->parent_id_in .= $prod->stock_id . ',';
                                    if(abs($reserved_quantity) < abs($shipped)){
                                        $stock_out->available_stock += $prod->reserved_quantity;  
                                        $stock_out->save();
                                        $shipped -= round(abs($reserved_quantity), 4);              
                                    }else{
                                        $stock_out->available_stock = 0;
                                        $stock_out->save();
                                        break;                
                                    }
                                }
                            }
                        } else {
                            if ($find_stock->count() > 0) {
                                foreach ($find_stock as $out) {

                                    if (abs($stock_out->available_stock) > 0) {
                                        if ($out->available_stock >= abs($stock_out->available_stock)) {
                                            $history_quantity = $stock_out->available_stock;
                                            $stock_out->parent_id_in .= $out->id . ',';
                                            $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                            $stock_out->available_stock = 0;
                                            $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out, $stock_out, $order_product, round(abs($history_quantity), 4));
                                        } else {
                                            $history_quantity = $out->available_stock;
                                            $stock_out->parent_id_in .= $out->id . ',';
                                            $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                            $out->available_stock = 0;
                                            $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out, $stock_out, $order_product, round(abs($history_quantity), 4));
                                        }
                                        $out->save();
                                        $stock_out->save();
                                    }
                                }
                            } else {
                                $stock_out->available_stock = '-' . @$shipped;
                                $stock_out->save();
                            }
                        }
                    }
                }

                // $warehouse_product = WarehouseProduct::where('warehouse_id', @$supply_from_id)->where('product_id', $order_product->product_id)->first();
                // $warehouse_product->current_quantity -= $quantity_inv;
                // $warehouse_product->reserved_quantity = $warehouse_product->reserved_quantity - $order_product->quantity;
                // $warehouse_product->available_quantity = $warehouse_product->current_quantity - ($warehouse_product->reserved_quantity + $warehouse_product->ecommerce_reserved_quantity);

                // $warehouse_product->save();

                $new_his = new QuantityReservedHistory;
                $re      = $new_his->updateTDCurrentReservedQuantity($order_product->PurchaseOrder, $order_product, $quantity_full_transferred, 'TD Confirmed By Warehouse Reserved Subtracted ', 'subtract', null);
            }

            if ($order_product->order_product_id != null) {
                $order_product = $order_product->order_product;
                $order = $order_product->get_order;
                if ($order->primary_status !== 3 && $order->primary_status !== 17) {
                    $order_product->status = 9;
                    $order_product->save();

                    $order_products_status_count = OrderProduct::where('order_id', $order_product->order_id)->where('is_billed', '=', 'Product')->where('status', '!=', 9)->count();
                    if ($order_products_status_count == 0) {
                        $order->status = 9;
                        $order->save();
                        $order_history = new OrderStatusHistory;
                        $order_history->user_id = Auth::user()->id;
                        $order_history->order_id = @$order->id;
                        $order_history->status = 'DI(Purchasing)';
                        $order_history->new_status = 'DI(Importing)';
                        $order_history->save();
                    }
                }
            }
        }

        $purchaseOrder->status = 22;
        $purchaseOrder->save();

        $page_status = Status::select('title')->whereIn('id', [21, 22])->pluck('title')->toArray();

        $poStatusHistory = new PurchaseOrderStatusHistory;
        $poStatusHistory->user_id = Auth::user()->id;
        $poStatusHistory->po_id = $purchaseOrder->id;
        $poStatusHistory->status = $page_status[0];
        $poStatusHistory->new_status = $page_status[1];
        $poStatusHistory->save();
        session(['td_status' => 22]);
        return response()->json(['success' => true]);
    }

    public static function confirmTransferGroup($request)
    {
        // dd($request->all());
        $confirm_from_draft = QuotationConfig::where('section', 'warehouse_management_page')->first();
        if ($confirm_from_draft) {
            $globalaccessForWarehouse = unserialize($confirm_from_draft->print_prefrences);
            foreach ($globalaccessForWarehouse as $val) {
                if ($val['slug'] === "has_warehouse_account") {
                    $has_warehouse_account = $val['status'];
                }
            }
        } else {
            $has_warehouse_account = '';
        }

        $po_group = PoGroup::find($request->id);
        if ($po_group->is_confirm == 1) {
            return response()->json(['success' => false]);
        }

        if ($has_warehouse_account == 1) {
            $po_group_details = PoGroupDetail::select('purchase_order_id')->where('po_group_id', $request->id)->get();
            foreach ($po_group_details as $po_group_detail) {
                $po_details = PurchaseOrderDetail::where('po_id', $po_group_detail->purchase_order_id)->get();
                foreach ($po_details as $po_detail) {
                    $po_detail->quantity_received = $po_detail->quantity;
                    $po_detail->save();
                }
            }
        }
        $po_group->is_confirm = 1;
        $po_group->save();
        $purchase_orders = PurchaseOrder::whereIn('id', PoGroupDetail::where('po_group_id', $request->id)->pluck('purchase_order_id'))->get();
        foreach ($purchase_orders as $PO) {

            if ($PO->from_warehouse_id != null && $PO->supplier_id == null) {
            } else {
                $PO->status = 15;
                $PO->save();
                // PO status history maintaining
                $page_status = Status::select('title')->whereIn('id', [14, 15])->pluck('title')->toArray();
                $poStatusHistory = new PurchaseOrderStatusHistory;
                $poStatusHistory->user_id    = Auth::user()->id;
                $poStatusHistory->po_id      = $PO->id;
                $poStatusHistory->status     = $page_status[0];
                $poStatusHistory->new_status = $page_status[1];
                $poStatusHistory->save();
            }


            $supplier_id = $PO->supplier_id;
            $purchase_order_details = PurchaseOrderDetail::with('product.sellingUnits', 'order_product.get_order')->where('po_id', $PO->id)->whereNotNull('purchase_order_details.product_id')->get();
            $manual_sup = Supplier::where('manual_supplier', 1)->first();
            $manual_supplier_id = $manual_sup != null ? $manual_sup->id : null;
            foreach ($purchase_order_details as $p_o_d) {
                // /$p_o_d->product->unit_conversion_rate
                $quantity_inv = $p_o_d->quantity_received;
                $quantity_inv_2 = $p_o_d->quantity_received_2;
                $decimal_places = $p_o_d->product->sellingUnits->decimal_places;
                if ($decimal_places == 0) {
                    $quantity_inv = round($quantity_inv, 0);
                    $quantity_inv_2 = round($quantity_inv_2, 0);
                } elseif ($decimal_places == 1) {
                    $quantity_inv = round($quantity_inv, 1);
                    $quantity_inv_2 = round($quantity_inv_2, 1);
                } elseif ($decimal_places == 2) {
                    $quantity_inv = round($quantity_inv, 2);
                    $quantity_inv_2 = round($quantity_inv_2, 2);
                } elseif ($decimal_places == 3) {
                    $quantity_inv = round($quantity_inv, 3);
                    $quantity_inv_2 = round($quantity_inv_2, 3);
                } else {
                    $quantity_inv = round($quantity_inv, 4);
                    $quantity_inv_2 = round($quantity_inv_2, 4);
                }

                if ($p_o_d->quantity == $quantity_inv) {
                    $p_o_d->is_completed = 1;
                    $p_o_d->save();
                }

                $total_quantity_inv = $quantity_inv;
                $total_quantity_inv_2 = $quantity_inv_2;
                if ($p_o_d->order_product_id != null) {
                    $order_product = $p_o_d->order_product;
                    $order         = $order_product->get_order;
                    if ($order->primary_status !== 3 && $order->primary_status !== 17) {
                        $order_product->status = 10;
                        $order_product->save();

                        $order_products_status_count = OrderProduct::where('order_id', $p_o_d->order_id)->where('is_billed', '=', 'Product')->where('quantity', '!=', 0)->where('status', '!=', 10)->count();
                        if ($order_products_status_count == 0) {
                            $order->status = 10;
                            $order->save();
                            // dd('here');
                            $order_history = new OrderStatusHistory;
                            $order_history->user_id = Auth::user()->id;
                            $order_history->order_id = @$order->id;
                            $order_history->status = 'DI(Importing)';
                            $order_history->new_status = 'DI(Waiting To Pick)';
                            $order_history->save();
                        }
                    }
                }
                if ($quantity_inv != null && $quantity_inv != 0) {
                    $stock = StockManagementIn::where('expiration_date', $p_o_d->expiration_date)->where('product_id', $p_o_d->product_id)->where('warehouse_id', $PO->to_warehouse_id)->first();
                    if ($stock != null) {
                        // new logic for TD to trace back to supplier
                        $res_stocks = TransferDocumentReservedQuantity::with('stock_m_out')->where('pod_id', $p_o_d->id)->get();
                        if ($res_stocks->count() > 0) {
                            foreach ($res_stocks as $res_stock) {
                                $reserved_quantity = round($res_stock->reserved_quantity,4);
                                if(abs($reserved_quantity) < abs($quantity_inv)){
                                    $stock_out = TransferDocumentHelper::stockManagement($stock, $p_o_d, $reserved_quantity, $PO, $res_stock, $manual_supplier_id);
                                    $quantity_inv -= round(abs($reserved_quantity), 4);

                                    $fullfill_old_data = TransferDocumentHelper::fullOldRecord($stock_out, $p_o_d);
                                }else{
                                    $stock_out = TransferDocumentHelper::stockManagement($stock, $p_o_d, $quantity_inv, $PO, $res_stock, $manual_supplier_id);
                                    $fullfill_old_data = TransferDocumentHelper::fullOldRecord($stock_out, $p_o_d);
                                    $quantity_inv = 0;
                                }
                                if($quantity_inv == 0){
                                    break;
                                }
                            }
                            if ($quantity_inv != 0) {
                                    $stock_out = TransferDocumentHelper::stockManagement($stock, $p_o_d, $quantity_inv, $PO, null, $manual_supplier_id);
                                    $quantity_inv = 0;
                                    $fullfill_old_data = TransferDocumentHelper::fullOldRecord($stock_out, $p_o_d);
                            }
                            // new logic end
                        } else {
                            // old logic
                            $stock_out = TransferDocumentHelper::stockManagement($stock, $p_o_d, $quantity_inv, $PO, null, $manual_supplier_id);
                            $fullfill_old_data = TransferDocumentHelper::fullOldRecord($stock_out, $p_o_d);
                        }
                    } else {
                        if ($p_o_d->expiration_date == null) {
                            $stock = StockManagementIn::where('product_id', $p_o_d->product_id)->where('warehouse_id', $PO->to_warehouse_id)->whereNull('expiration_date')->first();
                        }

                        if ($stock == null) {
                            $stock = new StockManagementIn;
                        }

                        $stock->title           = 'Adjustment';
                        $stock->product_id      = $p_o_d->product_id;
                        $stock->quantity_in     = $quantity_inv;
                        $stock->created_by      = Auth::user()->id;
                        $stock->warehouse_id    = $PO->to_warehouse_id;
                        $stock->expiration_date = $p_o_d->expiration_date;
                        $stock->save();

                        // new logic for TD to trace back to supplier
                        $res_stocks = TransferDocumentReservedQuantity::with('stock_m_out')->where('pod_id', $p_o_d->id)->get();
                        if ($res_stocks->count() > 0) {
                            foreach ($res_stocks as $res_stock) {
                                $reserved_quantity = round($res_stock->reserved_quantity,4);
                                if(abs($reserved_quantity) < abs($quantity_inv)){
                                    $stock_out = TransferDocumentHelper::stockManagement($stock, $p_o_d, $reserved_quantity, $PO, $res_stock, $manual_supplier_id);
                                    $fullfill_old_data = TransferDocumentHelper::fullOldRecord($stock_out, $p_o_d);

                                    $quantity_inv -= round(abs($reserved_quantity), 4);
                                }else{
                                    $stock_out = TransferDocumentHelper::stockManagement($stock, $p_o_d, $quantity_inv, $PO, $res_stock, $manual_supplier_id);
                                    $fullfill_old_data = TransferDocumentHelper::fullOldRecord($stock_out, $p_o_d);

                                    $quantity_inv = 0;
                                }
                                if($quantity_inv == 0){
                                    break;
                                }
                            }
                            if ($quantity_inv != 0) {
                                    $stock_out = TransferDocumentHelper::stockManagement($stock, $p_o_d, $quantity_inv, $PO, null, $manual_supplier_id);
                                    $quantity_inv = 0;
                                    $fullfill_old_data = TransferDocumentHelper::fullOldRecord($stock_out, $p_o_d);
                            }
                        } else {
                            // old logic
                            $stock_out = TransferDocumentHelper::stockManagement($stock, $p_o_d, $quantity_inv, $PO, null, $manual_supplier_id);
                            $fullfill_old_data = TransferDocumentHelper::fullOldRecord($stock_out, $p_o_d);
                        }
                    }

                    $new_his = new QuantityReservedHistory;
                    $re      = $new_his->updateTDCurrentQuantity($PO, $p_o_d, $total_quantity_inv, 'add');
                }
                if ($quantity_inv_2 != null && $quantity_inv_2 != 0) {
                    $stock = StockManagementIn::where('expiration_date', $p_o_d->expiration_date_2)->where('product_id', $p_o_d->product_id)->where('warehouse_id', $PO->to_warehouse_id)->first();
                    if ($stock != null) {
                        $stock_out = TransferDocumentHelper::stockManagement($stock, $p_o_d, $quantity_inv_2, $PO, null, $manual_supplier_id);
                        $fullfill_old_data = TransferDocumentHelper::fullOldRecord($stock_out, $p_o_d);
                    } else {
                        if ($p_o_d->expiration_date_2 == null) {
                            $stock = StockManagementIn::where('product_id', $p_o_d->product_id)->where('warehouse_id', $PO->to_warehouse_id)->whereNull('expiration_date')->first();
                        }

                        if ($stock == null) {
                            $stock = new StockManagementIn;
                        }

                        $stock->title           = 'Adjustment';
                        $stock->product_id      = $p_o_d->product_id;
                        $stock->quantity_in     = $quantity_inv_2;
                        $stock->created_by      = Auth::user()->id;
                        $stock->warehouse_id    = $PO->to_warehouse_id;
                        $stock->expiration_date = $p_o_d->expiration_date_2;
                        $stock->save();

                        $stock_out = TransferDocumentHelper::stockManagement($stock, $p_o_d, $quantity_inv_2, $PO, null, $manual_supplier_id);
                        $fullfill_old_data = TransferDocumentHelper::fullOldRecord($stock_out, $p_o_d);
                    }

                    $new_his = new QuantityReservedHistory;
                    $re      = $new_his->updateTDCurrentQuantity($PO, $p_o_d, $total_quantity_inv_2, 'add');
                }
            }
        }

        $group_status_history = new PoGroupStatusHistory;
        $group_status_history->user_id = Auth::user()->id;
        $group_status_history->po_group_id = @$po_group->id;
        $group_status_history->status = 'Confirmed';
        $group_status_history->new_status = 'Closed Product Receiving Queue';
        $group_status_history->save();

        return response()->json(['success' => true]);
    }

    public static function stockManagement($stock, $p_o_d, $reserved_quantity, $PO, $res_stock, $manual_supplier_id, $warehouse_id = null, $cost = false, $product_id = null, $msg = null){
        $stock_out               = new StockManagementOut;
        $stock_out->title        = $msg ?? 'TD';
        $stock_out->smi_id       = $stock->id;
        $stock_out->po_id        = @$PO->id;
        $stock_out->p_o_d_id     = @$p_o_d->id;
        $stock_out->product_id   = @$product_id != null ? $product_id : @$p_o_d->product_id;
        if ($reserved_quantity < 0) {
            $stock_out->quantity_out = $reserved_quantity;
            $stock_out->available_stock = $reserved_quantity;
        } else {
            $stock_out->quantity_in  = $reserved_quantity;
            $stock_out->available_stock  = $reserved_quantity;
        }
        $stock_out->created_by   = Auth::user()->id;
        $stock_out->warehouse_id = $warehouse_id != null ? @$warehouse_id : $PO->to_warehouse_id;
        $stock_out->supplier_id = @$res_stock->stock_m_out->supplier_id != null ? @$res_stock->stock_m_out->supplier_id : $manual_supplier_id;
        $stock_out->cost = $cost ?? @$p_o_d->proudct->selling_price;
        $stock_out->save();

        return $stock_out;
    }

    public static function fullOldRecord($stock_out, $p_o_d){
        $find_stock = StockManagementOut::where('smi_id', $stock_out->smi_id)->whereNotNull('quantity_out')->where('available_stock', '<', 0)->get();
        if ($find_stock->count() > 0) {
            foreach ($find_stock as $out) {

                if ($stock_out->quantity_in > 0 && abs($stock_out->available_stock) > 0) {
                    if ($stock_out->available_stock >= abs($out->available_stock)) {
                        $history_quantity = $out->available_stock;
                        $out->parent_id_in .= $stock_out->id . ',';
                        $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                        $out->available_stock = 0;
                        $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out, $out, $p_o_d, round(abs($history_quantity), 4));
                    } else {
                        $history_quantity = $stock_out->available_stock;
                        $out->parent_id_in .= $out->id . ',';
                        $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                        $stock_out->available_stock = 0;
                        $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out, $out, $p_o_d, round(abs($history_quantity), 4));
                    }
                    $out->save();
                    $stock_out->save();
                }
            }
        }
        return true;
    }
}
