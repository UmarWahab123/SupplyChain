<?php

namespace App\Jobs;

use App\ExportStatus;
use App\FailedJobException;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use App\Models\Common\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\StockManagementIn;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Common\StockManagementOut;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\MaxAttemptsExceededException;

class StockCardJob implements ShouldQueue
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
        $custom_table_data = '';
        try {
            $wh = WarehouseProduct::where('warehouse_id', $request['warehouse_id'])->where('product_id', $request['product_id'])->first();
            $final_stock = StockManagementOut::select('quantity_out', 'warehouse_id', 'quantity_in')->where('product_id', $request['product_id'])->where('warehouse_id', $request['warehouse_id'])->get();
            $stock_card = StockManagementIn::where('product_id', $request['product_id'])->where('warehouse_id', $request['warehouse_id'])->orderBy('expiration_date', 'DESC')->get();
            $product = Product::find($request['product_id']);
            $decimal_places = $product->sellingUnits != null ? $product->sellingUnits->decimal_places : 3;

            $custom_table_data .= '<div class="bg-white table-responsive h-100" style="min-height: 235px;">
                    <div class="">
                      <div class="">';
            $stck_out = (clone $final_stock)->where('warehouse_id', $wh->warehouse_id)->sum('quantity_out');
            $stck_in = (clone $final_stock)->where('warehouse_id', $wh->warehouse_id)->sum('quantity_in');
            $current_stock_all = round($stck_in, $decimal_places) - abs(round($stck_out, $decimal_places));
            $custom_table_data .= '</div>
                    </div>
                    <table class="table headings-color table-po-pod-list mb-0" style="width: 100%;">
                      <tbody>
                        <tr>
                          <th style="width: 25%;border-left: 1px solid #eee;">
                            <span><b>Current Stock : </b> <span class="span-current-quantity-' . $wh->warehouse_id . '"> ' . ($current_stock_all != null ? $current_stock_all : 0) . ' </span><input type="hidden" class="current-quantity-' . $wh->warehouse_id . '" value="' . ($current_stock_all != null ? $current_stock_all : 0) . '"></span>
                          </th>
                          <th style="width: 15%;border-left: 1px solid #eee;">
                            <span><b>Selling Unit : </b> ' . (@$product->sellingUnits->title != null ? @$product->sellingUnits->title : @$product->sellingUnits->title) . '</span>
                          </th>
                          <th style="width: 15%;"></th>
                          <th style="width: 15%;"></th>
                          <th style="width: 10%;"></th>
                        </tr>
                      </tbody>
                    </table>';
            $custom_table_data .= '<table class="table headings-color table-po-pod-list mb-0" style="width: 100%;">
                <tbody>';
            if ($stock_card->count() > 0) {
                foreach ($stock_card as $card) {
                    if ($wh->getWarehouse->id == $card->warehouse_id) {
                        $stock_out_in = DB::table('stock_management_outs')->where('smi_id', $card->id)->sum('quantity_in');
                        $stock_out_out = DB::table('stock_management_outs')->where('smi_id', $card->id)->sum('quantity_out');
                        if (round(($stock_out_in + $stock_out_out), $decimal_places) != 0 || ($stock_out_in == 0 && $stock_out_out == 0)) {
                            $custom_table_data .= '<tr class="header">
                        <th style="width: 25%;border: 1px solid #eee;">EXP:
                          <span class="m-l-15 inputDoubleClickFirst" id="expiration_date"  data-fieldvalue="' . ($card->expiration_date != null ? Carbon::parse($card->expiration_date)->format('d/m/Y') : '') . '">' . ($card->expiration_date != null ? Carbon::parse($card->expiration_date)->format('d/m/Y') : '---') . '</span>
                          <input type="text" style="width:75%;" placeholder="Expiration Date" name="expiration_date" class="d-none expiration_date_sc" value="' . ($card->expiration_date != null ? Carbon::parse($card->expiration_date)->format('d/m/Y') : '') . '" data-id="' . $card->id . '">
                        </th>
                        <th style="width: 15%;border: 1px solid #eee;">IN: <span class="span-expiry-in-value-' . $card->id . '">' . ($stock_out_in != NULL ? number_format($stock_out_in, $decimal_places, '.', ',') : number_format(0, $decimal_places, '.', ',')) . '</span> <input  type="hidden" value="' . ($stock_out_in != NULL ? round($stock_out_in, $decimal_places) : 0) . '" class="expiry-in-value-' . $card->id . '"></th>
                        <th style="width: 15%;border: 1px solid #eee;">OUT: <span class="span-expiry-out-value-' . $card->id . '">' . ($stock_out_out != NULL ? number_format($stock_out_out, $decimal_places, '.', ',') : number_format(0, $decimal_places, '.', ',')) . '</span> <input type="hidden" value="' . ($stock_out_out != NULL ? round($stock_out_out, $decimal_places) : 0) . '" class="expiry-out-value-' . $card->id . '"></th>
                        <th style="width: 15%;border: 1px solid #eee;border-right: 0px;">Balance: <span class="span-expiry-balance-value-' . $card->id . '">' . number_format(($stock_out_in + $stock_out_out), $decimal_places, '.', ',') . '</span> <input type="hidden" value="' . round(($stock_out_in + $stock_out_out), $decimal_places) . '" class="expiry-balance-value-' . $card->id . '"></th>
                        <th style="width: 10%;border: 1px solid #eee;border-left: 0px;">
                          <a href="javascript:void(0)" data-id="' . $card->id . '" class="collapse_rows"><button class="btn recived-button view-supplier-btn toggle-btn1 pull-right" data-toggle="collapse" style="width: 20%;"><span id="sign' . @$card->id . '">+</span></button></a>
                        </th>
                      </tr>
                      <tr class="ddddd" id="ddddd' . $card->id . '" style="display: none;">
                    <td colspan="6" style="padding: 0">
                    <div style="max-height: 40vh;overflow-y:auto;" class="tableFix">
                    <table width="100%" class="dataTable stock_table table-theme-header" id="stock-detail-table' . $card->id . '" >
                      <thead>
                        <tr>
                          <th>Action</th>
                          <th>Date </th>
                          <th>Customer ref #</th>
                          <th>Title # </th>
                          <th>IN </th>
                          <th>Out </th>
                          <th>Balance </th>';
                            if ($user->role_id != 3 && $user->role_id != 4 && $user->role_id != 6) {
                                $custom_table_data .= '<th>COGS</th>';
                            }

                            $custom_table_data .= '<th>Note</th>
                        </tr>
                      </thead>
                      <tbody>';
                            $stock_out = \App\Models\Common\StockManagementOut::where('smi_id', $card->id)->with('stock_out_order.customer', 'stock_out_po')->orderBy('id', 'DESC')->limit(50)->get();
                            if ($stock_out->count() > 0) {
                                foreach ($stock_out as $out) {
                                    $stock_out_in = \App\Models\Common\StockManagementOut::where('smi_id', $card->id)->where('id', '<=', $out->id)->sum('quantity_in');
                                    $stock_out_out = \App\Models\Common\StockManagementOut::where('smi_id', $card->id)->where('id', '<=', $out->id)->sum('quantity_out');
                                    $custom_table_data .= '<tr>
                            <td>';
                                    if (($out->title == 'Manual Adjustment' || $out->title == 'Expired' || $out->title == 'Spoilage' || $out->title == 'Lost' || $out->title == 'Marketing' || $out->title == 'Return' || $out->title == 'Transfer')) {
                                        $custom_table_data .= '<a href="javascript:void(0)" class="actionicon deleteIcon text-center deleteStock" data-id="' . $out->id . '"><i class="fa fa-trash" title="Delete Stock"></i></a>';
                                    } else {
                                        $custom_table_data .= '<span>--</span>';
                                    }
                                    $custom_table_data .= '</td>
                            <td>' . Carbon::parse(@$out->created_at)->format('d/m/Y') . '</td>
                            <td width="10%">';
                                    if ($out->order_id !== null && @$out->stock_out_order && @$out->stock_out_order->customer) {
                                        $custom_table_data .= '<a href="' . route('get-customer-detail', @$out->stock_out_order->customer->id) . '" target="_blank">
                                ' . (@$out->stock_out_order != null ? $out->stock_out_order->customer->reference_name : "--") . '
                              </a>';
                                    } elseif ($out->po_group_id != null) {
                                        $groups = $out->get_po_group;
                                        if ($groups != null) {
                                            $customers = $groups->find_customers($groups, $request['product_id']);
                                        }
                                        if ($customers->count() > 0) {
                                            $i = 1;
                                            $customer_names = '';
                                            $j = 0;
                                            foreach ($customers as $cust) {
                                                if ($j < 3) {
                                                    $customer_names .= $cust->reference_name . '<br>';
                                                } else {
                                                    break;
                                                }
                                                $j++;
                                            }
                                            $custom_table_data .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#poNumberModal' . $out->id . '" title="Customers" class="font-weight-bold">
                                  ' . $customer_names . '
                                </a>
                              <div class="modal fade" id="poNumberModal' . $out->id . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                  <div class="modal-content">
                                    <div class="modal-header">
                                      <h5 class="modal-title" id="exampleModalLabel">Customers</h5>
                                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                      </button>
                                    </div>
                                    <div class="modal-body">
                                    <table class="bordered" style="width:100%;">
                                        <thead style="border:1px solid #eee;text-align:center;">
                                          <tr><th>S.No</th><th>Customer Ref #</th></tr>
                                        </thead>
                                        <tbody>';
                                            foreach ($customers as $cust) {
                                                $link = '<a target="_blank" href="' . route('get-customer-detail', $cust->id) . '" title="View Detail"><b>' . $cust->reference_name . '</b></a>';
                                                $custom_table_data .= '<tr>
                                          <td>
                                            ' . $i . '
                                          </td>
                                          <td>
                                          <a target="_blank" href="' . route('get-customer-detail', $cust->id) . '" title="View Detail"><b>' . $cust->reference_name . '</b></a>
                                          </td>
                                        </tr>';
                                                $i++;
                                            }
                                            $custom_table_data .= '</tbody>
                                    </table>
                                    </div>
                                  </div>
                                </div>
                              </div>';
                                        } else {
                                            $custom_table_data .= '<span>--</span>';
                                        }
                                    } else {
                                        $custom_table_data .= '<span>--</span>';
                                    }
                                    $custom_table_data .= '</td>
                            <td>';
                                    if ($out->order_id !== null && $out->title == null && $out->stock_out_order) {
                                        $ret = $out->stock_out_order->get_order_number_and_link($out->stock_out_order);
                                        $ref_no = $ret[0];
                                        $link = $ret[1];
                                        $custom_table_data .= '<a target="_blank" href="' . route($link, ['id' => $out->stock_out_order->id]) . '" title="View Detail" class="">ORDER: ' . $ref_no . '</a>';
                                    } elseif ($out->po_group_id != null) {
                                        $custom_table_data .= '<a target="_blank" href="' . url('warehouse/warehouse-completed-receiving-queue-detail', $out->po_group_id) . '" class="" title="View Detail">SHIPMENT: ' . $out->get_po_group->ref_id . '</a>';
                                    } elseif ($out->p_o_d_id != null && $out->stock_out_po != null && $out->stock_out_po->status != 40) {
                                        $custom_table_data .= '<a target="_blank" href="' . url('get-purchase-order-detail', $out->stock_out_purchase_order_detail->PurchaseOrder->id) . '" class="" title="View Detail">' . ($out->title != null ? $out->title : 'PO') . ' : ' . ($out->stock_out_purchase_order_detail->PurchaseOrder->ref_id) . '</a>';
                                    } elseif ($out->p_o_d_id != null && $out->stock_out_po != null && $out->stock_out_po->status != 40) {
                                        $custom_table_data .= '<a target="_blank" href="' . url('get-purchase-order-detail', $out->stock_out_purchase_order_detail->PurchaseOrder->id) . '" class="" title="View Detail">' . ($out->title != null ? $out->title : 'PO') . ' : ' . ($out->stock_out_purchase_order_detail->PurchaseOrder->ref_id) . '</a>';
                                    } elseif ($out->p_o_d_id != null && $out->title == 'TD') {
                                        $custom_table_data .= '<a target="_blank" href="' . url('get-purchase-order-detail', @$out->stock_out_purchase_order_detail->PurchaseOrder->id) . '" class="" title="View Detail">' . ($out->title != null ? $out->title : 'PO') . ' : ' . (@$out->stock_out_purchase_order_detail->PurchaseOrder->ref_id) . '</a>';
                                    } elseif ($out->p_o_d_id != null) {
                                        $custom_table_data .= '<b><span style="color:black;"><a target="_blank" href="' . url('get-purchase-order-detail', $out->stock_out_purchase_order_detail->PurchaseOrder->id) . '" class="" title="View Detail">' . ($out->title != null ? $out->title : (@$out->stock_out_purchase_order_detail->PurchaseOrder->supplier_id == null ? 'TD' : 'PO')) . ':' . $out->stock_out_purchase_order_detail->PurchaseOrder->ref_id . '</span></a></b>';
                                        // $title = "PO:".$out->stock_out_purchase_order_detail->PurchaseOrder->ref_id  ;
                                    } elseif ($out->title != null) {
                                        $custom_table_data .= '<span class="m-l-15 selectDoubleClick" id="title" data-fieldvalue="' . @$out->title . '">
                                  ' . (@$out->title != null ? $out->title : 'Select') . '
                                </span>
                                <select name="title" class="selectFocusStock form-control d-none" data-id="' . $out->id . '">
                                  <option>Choose Reason</option>
                                  <option ' . ($out->title == 'Manual Adjustment' ? 'selected' : '') . ' value="Manual Adjustment">Manual Adjustment</option>
                                  <option ' . ($out->title == 'Expired' ? 'selected' : '') . ' value="Expired">Expired</option>
                                  <option ' . ($out->title == 'Spoilage ' ? 'selected' : '') . ' value="Spoilage">Spoilage</option>
                                  <option ' . ($out->title == 'Lost ' ? 'selected' : '') . ' value="Lost">Lost</option>
                                  <option ' . ($out->title == 'Marketing ' ? 'selected' : '') . 'value="Marketing">Marketing</option>
                                  <option ' . ($out->title == 'Return ' ? 'selected' : '') . ' value="Return">Return</option>
                                  <option ' . ($out->title == 'Transfer ' ? 'selected' : '') . ' value="Transfer">Transfer</option>
                                </select>';
                                        if ($out->order_id != null) {
                                            if (@$out->stock_out_order->primary_status == 37) {
                                                $custom_table_data .= '<a target="_blank" href="' . route('get-completed-draft-invoices', ['id' => $out->stock_out_order->id]) . '" title="View Detail" class="font-weight-bold ml-3">ORDER# ' . @$out->stock_out_order->full_inv_no . '</a>';
                                            }
                                        }
                                        if ($out->po_id != null) {
                                            if (@$out->stock_out_po->status == 40) {
                                                $custom_table_data .= '<a target="_blank" href="' . url('get-purchase-order-detail', $out->po_id) . '" title="View Detail" class="font-weight-bold ml-3">PO# ' . @$out->stock_out_po->ref_id . '</a>';
                                            }
                                        }
                                    } else {
                                        $custom_table_data .= 'Adjustment';
                                    }
                                    $custom_table_data .= '</td>
                            <td>';
                                    if (($out->title == 'Manual Adjustment' || $out->title == 'Expired' || $out->title == 'Spoilage' || $out->title == 'Lost' || $out->title == 'Marketing' || $out->title == 'Return' || $out->title == 'Transfer') && ($out->quantity_out == null || $out->quantity_out == 0)) {
                                        $enable = 'inputDoubleClickFirst';
                                    } else {
                                        $enable = '';
                                    }
                                    $custom_table_data .= '<span class="m-l-15 ' . $enable . ' disableDoubleInClick-' . $out->id . '" id="quantity_in"  data-fieldvalue="' . @$out->quantity_in . '">' . (@$out->quantity_in != null ? number_format($out->quantity_in, $decimal_places, '.', ',') : number_format(0, $decimal_places, '.', ',')) . '</span>
                              <input type="number" min="0" style="width:100%;" name="quantity_in" class="fieldFocusStock d-none " data-type="in" value="' . @$out->quantity_in . '" data-warehouse_id="' . $wh->getWarehouse->id . '" data-smi="' . $card->id . '" data-id="' . $out->id . '"></td>
                            <td>';
                                    if (($out->title == 'Manual Adjustment' || $out->title == 'Expired' || $out->title == 'Spoilage' || $out->title == 'Lost' || $out->title == 'Marketing' || $out->title == 'Return' || $out->title == 'Transfer')  && ($out->quantity_in == null || $out->quantity_in == 0)) {
                                        $enable2 = 'inputDoubleClickFirst';
                                    } else {
                                        $enable2 = '';
                                    }
                                    $custom_table_data .= '<span class="m-l-15 ' . $enable2 . ' disableDoubleOutClick-' . $out->id . ' " id="quantity_out"   data-fieldvalue="' . @$out->quantity_out . '">' . (@$out->quantity_out != null ? number_format($out->quantity_out, $decimal_places, '.', ',') : number_format(0, $decimal_places, '.', ',')) . '</span>
                              <input type="number" min="0" style="width:100%;" name="quantity_out"  class="fieldFocusStock d-none " data-type="out" data-warehouse_id="' . $wh->getWarehouse->id . '" data-smi="' . $card->id . '"   value="' . @$out->quantity_out . '" data-id="' . $out->id . '">
                            </td>
                            <td>' . number_format(($stock_out_in + $stock_out_out), $decimal_places, '.', ',') . '</td>';
                                    if ($user->role_id != 3 && $user->role_id != 4 && $user->role_id != 6) {
                                        $custom_table_data .=  '<td>';
                                        if (($out->title == 'Manual Adjustment' || $out->title == 'Expired' || $out->title == 'Spoilage' || $out->title == 'Lost' || $out->title == 'Marketing' || $out->title == 'Return' || $out->title == 'Transfer')) {
                                            $custom_table_data .= '<span class="m-l-15 inputDoubleClick" id="cost"  data-fieldvalue="' . $out->cost . '">
                                ' . ($out->cost != null ? $out->cost : '--') . '
                              </span>
                              <input type="text" autocomplete="nope" name="cost" class="fieldFocusCost d-none form-control" data-id="' . $out->id . '" value="' . (@$out->cost != null) ? $out->cost : '' . '">';
                                        } else {
                                            if ($out->cost != null) {
                                                $custom_table_data .= $out->cost != null ? round(($out->cost), 3) : '--';
                                            } elseif ($out->order_product_id != null && $out->order_product) {
                                                $custom_table_data .= $out->order_product->actual_cost != null ? number_format($out->order_product->actual_cost, 2, '.', ',') : '--';
                                            } else {
                                                $custom_table_data .= '<span>--</span>';
                                            }
                                        }
                                        $custom_table_data .= '</td>';
                                    }
                                    $custom_table_data .= '<td>';
                                    if (($out->title == 'Manual Adjustment' || $out->title == 'Expired' || $out->title == 'Spoilage' || $out->title == 'Lost' || $out->title == 'Marketing' || $out->title == 'Return' || $out->title == 'Transfer')) {
                                        $enable2 = 'inputDoubleClickFirst';
                                    } else {
                                        $enable2 = '';
                                    }
                                    $custom_table_data .= '<span class="m-l-15 ' . $enable2 . '" id="note"  data-fieldvalue="' . @$out->note . '">' . (@$out->note != null ? $out->note : '--') . '</span>
                              <input type="text" style="width:100%;" name="note" class="fieldFocusStock d-none" value="' . @$out->note . '" data-id="' . $out->id . '">
                            </td>
                          </tr>';
                                }
                            } else {
                                $custom_table_data .= '<tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-center"></td>
                            <td class="text-center"></td>
                            <td></td>
                            <td></td>';
                                if ($user->role_id != 3 && $user->role_id != 4 && $user->role_id != 6) {
                                    $custom_table_data .= '<td></td>';
                                }
                                $custom_table_data .= '<td></td>
                          </tr>';
                            }
                            $custom_table_data .= '</tbody>
                    </table>
                    </div>';
                            if ($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 4 || $user->role_id == 9 || $user->role_id == 11) {
                                $custom_table_data .= '<tr><td><a href="javascript:void(0)" class="btn btn-sale recived-button add-new-stock-btn" id="add-new-stock-btn' . $card->id . '" style="width: 40%; display: none;" data-warehouse_id="' . $card->warehouse_id . '" data-id="' . $card->id . '" title="Add Manual Stock">+</a></td></tr>';
                            }
                            $custom_table_data .= '</td>
                  </tr>';
                        }
                    }
                }
                $custom_table_data .= '<tr class="header"></tr>';
            } else {
                $custom_table_data .= '<tr>
                      <td align="center">No Data Found!!!</td>
                    </tr>';
            }
            $custom_table_data .= '</tbody>
                </table></div>';

            $job_status = ExportStatus::where('type', 'stock_card_job')->where('user_id', $user->id)->first();
            $job_status->status = 0;
            $job_status->exception = $custom_table_data;
            $job_status->error_msgs = null;
            $job_status->save();
            // return response()->json(['success' => true, 'html' => $custom_table_data]);
        }
        catch (\Exception $e) {
            $this->failed($e);
        }
        catch(MaxAttemptsExceededException $e) {
            $this->failed($e);
        }
    }

    public function failed($exception)
    {
      ExportStatus::where('type', 'stock_card_job')->where('user_id', $this->user->id)->update(['status'=>2,'exception'=>$exception->getLine() . ', ' .$exception->getMessage()]);
      $failedJobException=new FailedJobException();
      $failedJobException->type="stock_card_job";
      $failedJobException->exception=$exception->getLine() . ', ' .$exception->getMessage();
      $failedJobException->save();
    }
}
