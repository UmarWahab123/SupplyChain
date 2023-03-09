<?php

namespace App\Jobs;

use App\ExportStatus;
use App\FailedJobException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Exception;
use App\Models\Common\PoGroup;
use App\Models\Common\PoGroupDetail;
use App\Models\Common\PoGroupProductDetail;
use App\Models\Common\ColumnDisplayPreference;
use App\Models\Common\TableHideColumn;
use App\Models\Common\Product;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PurchaseOrdersHistory;
use App\Models\Common\SupplierProducts;
use App\ProductHistory;
use Carbon\Carbon;
use App\Jobs\UpdateOldRecord;
use App\PoGroupProductHistory;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\StockManagementOut;
use App\Models\Common\Warehouse;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\Order\Order;
use Auth;
use App\User;
use Illuminate\Http\Request;

class ProductsReceivingImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $rows;
    protected $user_id;
    protected $group_id;
    public $tries = 2;
    public $timeout = 1800;  //30 minutes

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($rows,$user_id,$group_id)
    {
        $this->rows=$rows;
        $this->user_id=$user_id;
        $this->group_id=$group_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{

            $rows=$this->rows;
            $user_id=$this->user_id;
            $group_id=$this->group_id;
            $display_prods = ColumnDisplayPreference::where('type', 'importing_open_product_receiving')->where('user_id', $user_id)->first();
            $not_visible_columns = TableHideColumn::select('hide_columns')->where('type','importing_open_product_receiving')->where('user_id',$user_id)->first();
            $html_string = '';



            if($not_visible_columns != null)
            {
              $not_visible_arr = explode(',',$not_visible_columns->hide_columns);

              $hide_columns_count = count($not_visible_arr);
            }
            else
            {
                $hide_columns_count = 0;
            }

            if($rows->count() > 1)
            {
                $row1 = $rows->toArray();
                $remove = array_shift($row1);
                $remove = array_shift($row1);

                $html_string = '<ol>';
                $increment = 1;
                $occurrence = 1;
                $old_pogpd_id = null;

                $modified_pos = [];
                $previous_occ = 0;
                foreach ($row1 as $row) {
                    try {


                if($row['needed_ids'] != null && $row['needed_ids'] != '')
                {
                    $row_16 = 'purchasing_price_euro';
                    $row_17 = 'discount';
                    $row_12 = 'qty_inv';
                    $row_13 = 'total_gross_weight';
                    $row_14 = 'total_extra_cost';
                    $row_15 = 'total_extra_tax';
                    $row_19 = 'currency_conversion_rate';
                    $row_6 = 'prod_ref_no';
                    $g_weight = 'gross_weight';
                    $extra_cost_row = 'extra_cost';
                    $extra_tax_row = 'extra_tax';

                    // dd($row['gross_weight']);

                    if(array_key_exists("prod_ref_no", $row))
                    {
                        $row_6 = 'prod_ref_no';
                    }
                    else
                    {
                        $row['prod_ref_no'] = 'Product';
                    }


                    $ids = explode(',', $row['needed_ids']);
                    $pod_id = intval($ids[0]);
                    $pogpd_id = intval($ids[1]);
                    $po_id = intval($ids[2]);
                    if($old_pogpd_id != $pogpd_id)
                    {
                        $occurrence = 1;
                    }
                    $old_pogpd_id = $pogpd_id;
                    $qty_inv_updated = false;

                    $po_details = PoGroupProductDetail::where('id', $pogpd_id)->first();



                if(array_key_exists("discount", $row))
                {
                    //Discount Column
                    if($row[$row_17] != null)
                    {
                        if(!is_numeric($row[$row_17]))
                        {
                            $row[$row_17] = null;
                            $error = 1;
                            $html_string .= '<li>Enter Valid Discount For Product <b>'.$row[$row_6].'</b></li>';
                        }
                    }

                    if($row[$row_17] !== null && $row[$row_17] >= 0)
                    {
                        $po = PurchaseOrderDetail::where('id',$pod_id)->where('po_id',$po_id)->first();
                        $old_value_discount = $po->discount;
                        $checkSameProduct = PurchaseOrderDetail::where('po_id',$po->po_id)->where('product_id',$po->product_id)->get();

                        if($old_value_discount != $row[$row_17])
                        {
                            array_push($modified_pos, $po_id);

                            $request = new \Illuminate\Http\Request();
                            $request->replace(['rowId' => $po->id,'user_id' => $user_id,'pogpd__id' => $pogpd_id, 'po_id' => $po->po_id,'from_bulk' => 'yes','discount' => $row[$row_17], 'old_value' => $old_value_discount]);
                            // app('App\Http\Controllers\Purchasing\PurchaseOrderController')->SavePoProductDiscount($request);

                            $this->SavePoProductDiscount($request, $old_value_discount);
                        }
                    }
                }

                if(array_key_exists("qty_inv", $row))
                {
                    //Qty Inv Column
                    if($row[$row_12] !== null)
                    {
                        if(!is_numeric($row[$row_12]))
                        {
                            $row[$row_12] = null;
                            $error = 1;
                            $html_string .= '<li>Enter Valid QTY Inv For Product <b>'.$row[$row_6].'</b></li>';
                        }
                    }

                    if($row[$row_12] !== null && $row[$row_12] >= 0)
                    {
                        $po = PurchaseOrderDetail::where('id',$pod_id)->where('po_id',$po_id)->first();
                        $old_value_quantity = $po->quantity;
                        $checkSameProduct = PurchaseOrderDetail::where('po_id',$po->po_id)->where('product_id',$po->product_id)->get();

                        if($old_value_quantity != $row[$row_12])
                        {
                            if($previous_occ != $pogpd_id)
                            PoGroupProductDetail::where('id',$po_details->id)->update(['quantity_inv_old' => \DB::raw('quantity_inv')]);

                            $qty_inv_old_on_pogpd = $po_details->quantity_inv;
                            $qty_inv_updated = true;
                            array_push($modified_pos, $po_id);

                            $request = new \Illuminate\Http\Request();
                            $request->replace(['rowId' => $po->id,'user_id' => $user_id,'pogpd__id' => $pogpd_id, 'po_id' => $po->po_id,'from_bulk' => 'yes','quantity' => $row[$row_12], 'old_value' => $old_value_quantity]);
                            // app('App\Http\Controllers\Purchasing\PurchaseOrderController')->SavePoProductQuantity($request);

                            $previous_occ = $pogpd_id;
                            $this->SavePoProductQuantity($request,@$po_details->quantity_inv);
                        }
                    }
                }

                if(array_key_exists("purchasing_price_euro", $row))
                {
                    if($row[$row_16] != null)
                    {
                        if(!is_numeric($row[$row_16]))
                        {
                            $row[$row_16] = null;
                            $error = 1;
                            $html_string .= '<li>Enter Valid Unit Price For Product <b>'.$row[$row_6].'</b></li>';
                        }
                    }

                    if(($row[$row_16] !== null && $row[$row_16] !== '' && $row[$row_16] >= 0) || $qty_inv_updated == true)
                    {
                        $checkSameProduct = PurchaseOrderDetail::find($pod_id);
                        $old_value = round($checkSameProduct->pod_unit_price,3);
                        if($old_value != $row[$row_16])
                        {
                            // dd($old_value,$row[$row_16]);
                            array_push($modified_pos, $po_id);
                            $request = new \Illuminate\Http\Request();
                            $request->replace(['rowId' => $checkSameProduct->id, 'po_id' => $checkSameProduct->po_id,'unit_price' => $row[$row_16],'from_bulk' => 'yes', 'old_value' => $old_value,'user_id' => $user_id]);
                            // app('App\Http\Controllers\Purchasing\PurchaseOrderController')->UpdateUnitPrice($request);

                            $this->UpdateUnitPrice($request, $old_value, $group_id);
                        }

                    }
                }

                if(array_key_exists("gross_weight", $row))
                {
                    // //total Gross Weight
                    if($row[$g_weight] !== null)
                    {
                        if(!is_numeric($row[$g_weight]))
                        {
                            $row[$g_weight] = null;
                            $error = 1;
                            $html_string .= '<li>Enter Valid Gross Weight For Product <b>'.$row[$row_6].'</b></li>';
                        }
                    }

                    if($row[$g_weight] !== null && $row[$g_weight] >= 0)
                    {
                        $pod = PurchaseOrderDetail::where('id',$pod_id)->where('po_id',$po_id)->first();
                        $group_product_detail = PoGroupProductDetail::where('status',1)->find($pogpd_id);
                        $old_g_w = round($pod->pod_gross_weight,3);
                        if($group_product_detail)
                        {
                            if(round($pod->pod_gross_weight,4) != round($row[$g_weight],4) && $pod->pod_gross_weight !== $row[$g_weight] || $qty_inv_updated == true)
                            {
                                // dd('unit_g_w',$pod_id);
                                $request = new \Illuminate\Http\Request();
                                $request->replace(['pogd_id' => $pogpd_id, 'unit_gross_weight' => $row[$g_weight],'po_group_id' => $group_product_detail->po_group_id, 'old_value' => $old_g_w,'user_id' => $user_id,'pod_id' => $pod_id]);

                                $this->updateGrossWeight($request, $old_g_w);

                                // $this->UpdateUnitPrice($request, $old_value, $group_id);

                                // app('App\Http\Controllers\Importing\PoGroupsController')->editPoGroupProductDetails($request);

                                // $group_product_detail->save();
                            }
                        }
                    }
                }

                if(array_key_exists("total_gross_weight", $row))
                {
                    // dd('here');
                    // //total Gross Weight
                    if($row[$row_13] !== null)
                    {
                        if(!is_numeric($row[$row_13]))
                        {
                            $row[$row_13] = null;
                            $error = 1;
                            $html_string .= '<li>Enter Valid Total Gross Weight For Product <b>'.$row[$row_6].'</b></li>';
                        }
                    }

                    if($row[$row_13] !== null && $row[$row_13] >= 0 && $row[$g_weight] == null)
                    {
                        // dd('here');
                        $pod = PurchaseOrderDetail::where('id',$pod_id)->where('po_id',$po_id)->first();
                        $group_product_detail = PoGroupProductDetail::where('status',1)->find($pogpd_id);
                        $old_g_w = round($pod->pod_total_gross_weight,3);
                        if($group_product_detail)
                        {
                            if($pod->pod_total_gross_weight != $row[$row_13])
                            {
                                $request = new \Illuminate\Http\Request();
                                $request->replace(['pod_id' => $pogpd_id, 'total_gross_weight' => $row[$row_13],'po_group_id' => $group_product_detail->po_group_id, 'old_value' => $old_g_w,'user_id' => $user_id]);
                                if($pod->product_id == 398)
                                {
                                    dd($request);
                                }
                                // dd('here');
                                app('App\Http\Controllers\Importing\PoGroupsController')->editPoGroupProductDetails($request);

                                $group_product_detail->save();
                            }
                        }

                    }
                    // dd('there');
                }

                if(array_key_exists("extra_cost", $row))
                {
                    //total Extra Cost
                    if($row[$extra_cost_row] !== null)
                    {
                        if(!is_numeric($row[$extra_cost_row]) && $row[$extra_cost_row] !== '--')
                        {
                            $row[$extra_cost_row] = null;
                            $error = 1;
                            $html_string .= '<li>Enter Valid Extra Cost For Product <b>'.$row[$row_6].'</b></li>';
                        }
                    }

                    if($row[$extra_cost_row] !== null && $row[$extra_cost_row] !== '--' && $row[$extra_cost_row] >= 0)
                    {
                        $popd = PoGroupProductDetail::where('status',1)->find($pogpd_id);
                        $pod_detail = PurchaseOrderDetail::find($pod_id);
                        $old_extra_cost = round($pod_detail->unit_extra_cost,4);
                        if($popd != null)
                        {
                            if($popd->occurrence > 1)
                            {

                                if(($old_extra_cost != round($row[$extra_cost_row],4)) || $qty_inv_updated == true)
                                {
                                    //to find total extra cost from unit extra cost
                                    if($pod_detail->quantity == 0)
                                    {
                                        $t_e_c = 0;
                                    }
                                    else
                                    {
                                        $t_e_c = ($row[$extra_cost_row] * $pod_detail->quantity);
                                    }

                                    $pod_detail->unit_extra_cost = $row[$extra_cost_row];
                                    $pod_detail->total_extra_cost = $t_e_c;
                                    $pod_detail->save();

                                    $all_ids = PurchaseOrder::where('po_group_id',$popd->po_group_id)->where('supplier_id',$popd->supplier_id)->pluck('id');
                                    $all_record = PurchaseOrderDetail::whereIn('po_id',$all_ids)->where('product_id',$popd->product_id)->with('product','PurchaseOrder','getOrder','product.units','getOrder.user','getOrder.customer')->get();

                                    if($all_record->count() > 0)
                                    {
                                        //to update total extra tax column in po group product detail
                                        $popd->total_extra_cost = $all_record->sum('total_extra_cost');
                                        //to update unit extra tax column in po group product detail
                                        $popd->unit_extra_cost = $all_record->sum('unit_extra_cost') / $all_record->count();
                                        $popd->save();
                                    }

                                }
                            }
                            else
                            {
                                if((round($row[$extra_cost_row],4) != round($popd->unit_extra_cost,4)) || $qty_inv_updated == true)
                                {
                                    $popd->unit_extra_cost = $row[$extra_cost_row];
                                    $popd->total_extra_cost = $row[$extra_cost_row] * $popd->quantity_inv;
                                    $popd->save();
                                }
                            }

                            // if(round($row[$extra_cost_row],4) != $old_extra_cost){
                            //     $this->setHistory($this->user_id,$popd,$popd->unit_extra_cost,'Extra Cost',round($row[$extra_cost_row],4));
                            // }

                        }
                    }
                }
                if(array_key_exists("total_extra_cost", $row))
                {
                    //total Extra Cost
                    if($row[$row_14] !== null)
                    {
                        if(!is_numeric($row[$row_14]) && $row[$row_14] !== '--')
                        {
                            $row[$row_14] = null;
                            $error = 1;
                            $html_string .= '<li>Enter Valid Total Extra Cost For Product <b>'.$row[$row_6].'</b></li>';
                        }
                    }

                    $pod_detail = PurchaseOrderDetail::find($pod_id);
                    $old_total_extra_cost = round($pod_detail->total_extra_cost,4);

                    if($row[$row_14] !== null && $row[$row_14] !== '--' && $row[$row_14] >= 0 && $row[$extra_cost_row] == null)
                    {
                        $popd = PoGroupProductDetail::where('status',1)->find($pogpd_id);
                        if($popd != null)
                        {
                            if($popd->occurrence > 1)
                            {

                                if(($old_total_extra_cost != round($row[$row_14],4)) || $qty_inv_updated == true)
                                {
                                    //to find unit extra cost from total extra cost
                                    if($pod_detail->quantity == 0 || $pod_detail->quantity === 0)
                                    {
                                        $u_e_c = 0;
                                    }
                                    else
                                    {
                                        $u_e_c = ($row[$row_14] / $pod_detail->quantity);
                                    }

                                    $pod_detail->total_extra_cost = $row[$row_14];
                                    $pod_detail->unit_extra_cost = $u_e_c;
                                    $pod_detail->save();

                                    $all_ids = PurchaseOrder::where('po_group_id',$popd->po_group_id)->where('supplier_id',$popd->supplier_id)->pluck('id');
                                    $all_record = PurchaseOrderDetail::whereIn('po_id',$all_ids)->where('product_id',$popd->product_id)->with('product','PurchaseOrder','getOrder','product.units','getOrder.user','getOrder.customer')->get();

                                    if($all_record->count() > 0)
                                    {
                                        //to update total extra tax column in po group product detail
                                        $popd->total_extra_cost = $all_record->sum('total_extra_cost');
                                        //to update unit extra tax column in po group product detail
                                        $popd->unit_extra_cost = $all_record->sum('unit_extra_cost') / $all_record->count();
                                        $popd->save();
                                    }
                                }
                            }
                            else
                            {
                                if((round($row[$row_14],4) != round($popd->total_extra_cost,4)) || $qty_inv_updated == true)
                                {
                                    if($popd->quantity_inv != 0 && $popd->quantity_inv !== 0)
                                    {
                                        $popd->total_extra_cost = $row[$row_14];
                                        $popd->unit_extra_cost = $row[$row_14]/$popd->quantity_inv;
                                    }
                                    else
                                    {
                                        $popd->total_extra_cost = 0;
                                    }
                                    $popd->save();
                                }
                            }

                            // if(round($row[$row_14],4) != $old_total_extra_cost){
                            //     $this->setHistory($this->user_id,$popd,$popd->total_extra_cost,'Total Extra Cost',round($row[$row_14],4));
                            // }

                        }
                    }
                }


                if(array_key_exists("extra_tax", $row))
                {
                    //total Import Tax
                    if($row[$extra_tax_row] !== null)
                    {
                        if(!is_numeric($row[$extra_tax_row]) && $row[$extra_tax_row] !== '--')
                        {
                            $row[$extra_tax_row] = null;
                            $error = 1;
                            $html_string .= '<li>Enter Valid Extra Tax For Product <b>'.$row[$row_6].'</b></li>';
                        }
                    }

                    $pod_detail = PurchaseOrderDetail::find($pod_id);
                    $old_extra_tax = round($pod_detail->unit_extra_tax,4);

                    if($row[$extra_tax_row] !== null && $row[$extra_tax_row] !== '--' && $row[$extra_tax_row] >= 0)
                    {
                        $popd = PoGroupProductDetail::where('status',1)->find($pogpd_id);
                        // $old_extra_tax_value = $popd
                        if($popd != null)
                        {
                            if($popd->occurrence > 1)
                            {

                                if(($old_extra_tax != round($row[$extra_tax_row],4)) || $qty_inv_updated == true)
                                {
                                    //to find total extra tax unit total extra tax
                                    if($pod_detail->quantity == 0 || $pod_detail->quantity === 0)
                                    {
                                        $t_e_t = 0;
                                    }
                                    else
                                    {
                                        $t_e_t = ($row[$extra_tax_row] * $pod_detail->quantity);
                                    }

                                    $pod_detail->unit_extra_tax = $row[$extra_tax_row];
                                    $pod_detail->total_extra_tax = $t_e_t;
                                    $pod_detail->save();

                                    $all_ids = PurchaseOrder::where('po_group_id',$popd->po_group_id)->where('supplier_id',$popd->supplier_id)->pluck('id');
                                    $all_record = PurchaseOrderDetail::whereIn('po_id',$all_ids)->where('product_id',$popd->product_id)->with('product','PurchaseOrder','getOrder','product.units','getOrder.user','getOrder.customer')->get();

                                    if($all_record->count() > 0)
                                    {
                                        //to update total extra tax column in po group product detail
                                        $popd->total_extra_tax = $all_record->sum('total_extra_tax');
                                        //to update unit extra tax column in po group product detail
                                        $popd->unit_extra_tax = $all_record->sum('unit_extra_tax') / $all_record->count();
                                        $popd->save();
                                    }
                                }
                                // if(round($row[$extra_tax_row],4) != $old_extra_tax){
                                //     $this->setHistory($this->user_id,$popd,$popd->unit_extra_tax,'Extra Tax',round($row[$extra_tax_row],4));
                                // }
                            }
                            else
                            {
                                if((round($row[$extra_tax_row],4) != round($popd->unit_extra_tax,4)) || $qty_inv_updated == true)
                                {
                                    // if(round($row[$extra_tax_row],4) != $old_extra_tax){
                                    //     $this->setHistory($this->user_id,$popd,$popd->unit_extra_tax,'Extra Tax',round($row[$extra_tax_row],4));
                                    // }

                                    $popd->unit_extra_tax = $row[$extra_tax_row];
                                    $popd->total_extra_tax = $row[$extra_tax_row] * $popd->quantity_inv;
                                    $popd->save();
                                }
                            }



                        }
                    }
                }

                if(array_key_exists("total_extra_tax", $row))
                {
                    //total Import Tax
                    if($row[$row_15] !== null)
                    {
                        if(!is_numeric($row[$row_15]) && $row[$row_15] !== '--')
                        {
                            $row[$row_15] = null;
                            $error = 1;
                            $html_string .= '<li>Enter Valid Total Extra Tax For Product <b>'.$row[$row_6].'</b></li>';
                        }
                    }

                    if($row[$row_15] !== null && $row[$row_15] !== '--' && $row[$row_15] >= 0 && $row[$extra_tax_row] == null)
                    {
                        $pod_detail = PurchaseOrderDetail::find($pod_id);
                        $old_total_extra_tax = round($pod_detail->total_extra_tax,4);
                        $popd = PoGroupProductDetail::where('status',1)->find($pogpd_id);
                        if($popd != null)
                        {
                            if($popd->occurrence > 1)
                            {

                                if(($old_total_extra_tax != round($row[$row_15],4)) || $qty_inv_updated == true)
                                {
                                    //to find unit extra tax from total extra tax
                                    if($pod_detail->quantity == 0 || $pod_detail->quantity === 0)
                                    {
                                        $u_e_t = 0;
                                    }
                                    else
                                    {
                                        $u_e_t = ($row[$row_15] / $pod_detail->quantity);
                                    }

                                    $pod_detail->total_extra_tax = $row[$row_15];
                                    $pod_detail->unit_extra_tax = $u_e_t;
                                    $pod_detail->save();

                                    $all_ids = PurchaseOrder::where('po_group_id',$popd->po_group_id)->where('supplier_id',$popd->supplier_id)->pluck('id');
                                    $all_record = PurchaseOrderDetail::whereIn('po_id',$all_ids)->where('product_id',$popd->product_id)->with('product','PurchaseOrder','getOrder','product.units','getOrder.user','getOrder.customer')->get();

                                    if($all_record->count() > 0)
                                    {
                                        //to update total extra tax column in po group product detail
                                        $popd->total_extra_tax = $all_record->sum('total_extra_tax');
                                        //to update unit extra tax column in po group product detail
                                        $popd->unit_extra_tax = $all_record->sum('unit_extra_tax') / $all_record->count();
                                        $popd->save();
                                    }
                                }
                            }
                            else
                            {
                                if((round($row[$row_15],4) != round($popd->total_extra_tax,4)) || $qty_inv_updated == true)
                                {
                                    if($popd->quantity_inv != 0 && $popd->quantity_inv !== 0)
                                    {
                                        $popd->total_extra_tax = $row[$row_15];
                                        $popd->unit_extra_tax = $row[$row_15]/$popd->quantity_inv;
                                    }
                                    else
                                    {
                                        $popd->total_extra_tax = 0;
                                    }
                                    $popd->save();
                                }
                            }

                            // if(round($row[$row_15],4) != $old_total_extra_tax){
                            //     $this->setHistory($this->user_id,$popd,$popd->total_extra_tax,'Total Extra Tax',round($row[$row_15],4));
                            // }

                        }
                    }
                }

                if(array_key_exists("currency_conversion_rate", $row))
                {
                    $popd = PoGroupProductDetail::where('status',1)->find($pogpd_id);
                    $po = PurchaseOrderDetail::where('id',$pod_id)->where('po_id',$po_id)->first();
                    $old_value_ccr = round($popd->currency_conversion_rate,5);

                    // dd();

                    if($row[$row_19] != 0 && $row[$row_19] !== 0)
                    {
                        $new_ccr = round(1 / $row[$row_19],5);
                    }

                    if($popd->occurrence > 1)
                    {
                        $value_to_compare = round($row[$row_19],5);
                    }
                    else
                    {
                        $value_to_compare = $new_ccr;
                    }

                    if($old_value_ccr != $value_to_compare)
                    {
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['po_id' => $po->po_id,'from_bulk' => 'yes','exchange_rate' => $row[$row_19]]);

                        app('App\Http\Controllers\Purchasing\PurchaseOrderController')->SavePoNote($request);
                    }

                }

                // dd($old_record, $new_record, $column_name);
            }
            } catch (Exception $e) {
                ExportStatus::where('type','products_receiving_importings_bulk_job')->update(['status'=>2,'last_downloaded'=>date('Y-m-d'),'exception'=>$e->getMessage()]);
                // dd($e->getMessage());
                    return response()->json(['success' => false,'msg' => 'Something is wrong with file']);
                    }

        }

                    $ids = explode(',', $row1[0]['needed_ids']);
                    $po_id = intval($ids[2]);

                    $pos_modified = PurchaseOrder::whereIn('id',$modified_pos)->get();

                    if($pos_modified->count() > 0)
                    {
                        foreach($pos_modified as $po)
                        {
                            /*calulation through a function*/
                            $objectCreated = new PurchaseOrderDetail;
                            $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($po->id);
                        }
                    }

                app('App\Http\Controllers\Purchasing\PurchaseOrderController')->updateGroupViaPo($po_id);

                // $this->updateGroup($po_id,$pogpd_id,$group_id);

        }

            ExportStatus::where('type','products_receiving_importings_bulk_job')->update(['status'=>0,'last_downloaded'=>date('Y-m-d'),'exception'=>$html_string]);

          return response()->json(['msg'=>'File Saved']);
        }
        catch(Exception $e) {
        $this->failed($e);
        }
        catch(MaxAttemptsExceededException $e) {
              $this->failed($e);
        }
    }

    public function failed( $exception)
    {
        ExportStatus::where('type','products_receiving_importings_bulk_job')->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException=new FailedJobException();
        $failedJobException->type="Products Receiving Importings";
        $failedJobException->exception=$exception->getMessage();
        $failedJobException->save();
    }

     public function filterFunction($success = null)
    {
        if($success == 'fail')
        {
            $this->response = "File is Empty Please Upload Valid File !!!";
            $this->result   = "true";
        }
        elseif($success == 'pass')
        {
            $this->response = "Products Imported Successfully !!!";
            $this->result   = "false";
        }
        elseif($success == 'hasError')
        {
            $this->response = "Products Imported Successfully, But Some Of Them Has Issues !!!";
            $this->result   = "withissues";
        }
        elseif($success == 'redirect')
        {
            $this->response = "Import File Dosen\"t have PF# column, please Import valid file !!!";
            $this->result   = "true";
        }
    }

    public function updateGroup($po_id,$pogpd_id = null,$po_g_id)
    {
        $total_import_tax_book_price = 0;
        $po_totoal_change = PurchaseOrder::find($po_id);
        // if($po_totoal_change->exchange_rate == null)
        // {
        //     $supplier_conv_rate_thb = $po_totoal_change->PoSupplier->getCurrency->conversion_rate;
        // }
        // else
        // {
        //     $supplier_conv_rate_thb = $po_totoal_change->exchange_rate;
        // }

        // foreach ($po_totoal_change->PurchaseOrderDetail as $p_o_d)
        // {
        //     // $p_o_d->currency_conversion_rate  = $supplier_conv_rate_thb;
        //     $p_o_d->unit_price_in_thb         = $p_o_d->pod_unit_price/$supplier_conv_rate_thb;
        //     $p_o_d->total_unit_price_in_thb   = $p_o_d->pod_total_unit_price/$supplier_conv_rate_thb;
        //     $p_o_d->pod_import_tax_book_price = ($p_o_d->pod_import_tax_book/100)*$p_o_d->total_unit_price_in_thb;
        //     $p_o_d->save();
        // }


        // $po_totoal_change->total_in_thb = $po_totoal_change->total/$supplier_conv_rate_thb;
        // $po_totoal_change->save();

        $total_import_tax_book_price2           = null;
        $total_buying_price_in_thb2             = null;
        $total_buying_price_in_thb2_with_vat    = null;

        // getting all po's with this po group
        $gettingAllPos = PoGroupDetail::select('purchase_order_id')->where('po_group_id', $po_g_id)->get();
        $po_group = PoGroup::find($po_g_id);

        if($po_group->is_review == 0)
        {
            if($gettingAllPos->count() > 0)
            {
                foreach ($gettingAllPos as $allPos)
                {
                    $purchase_order = PurchaseOrder::find($allPos->purchase_order_id);
                    $total_import_tax_book_price2 += $purchase_order->total_import_tax_book_price;
                    $total_buying_price_in_thb2   += $purchase_order->total_in_thb;
                    $total_buying_price_in_thb2_with_vat   += $purchase_order->total_with_vat_in_thb;
                }
            }

            $po_group->po_group_import_tax_book    = $total_import_tax_book_price2;
            $po_group->total_buying_price_in_thb   = $total_buying_price_in_thb2;
            $po_group->total_buying_price_in_thb_with_vat   = $total_buying_price_in_thb2_with_vat;
            $po_group->save();

            #average unit price
            $average_unit_price = 0;
            $average_count = 0;
            foreach ($gettingAllPos as $po_id)
            {
                $average_count++;
                $purchase_order = PurchaseOrder::find($po_id->purchase_order_id);

                $purchase_order_details = PurchaseOrderDetail::where('po_id',$purchase_order->id)->whereNotNull('product_id')->get();
                foreach ($purchase_order_details as $p_o_d) {

                    $po_group_product =  PoGroupProductDetail::where('status',1)->where('product_id',$p_o_d->product_id)->where('po_group_id',$po_group->id)->where('supplier_id',$purchase_order->supplier_id)->first();

                    if($po_group_product != null)
                    {
                        if($po_group_product->occurrence > 1)
                        {
                            $ccr = $po_group_product->po_group->purchase_orders()->pluck('id')->toArray();
                            $po_group_product->unit_price                = ($p_o_d->pod_unit_price)/$average_count;
                            $average_currency = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'currency_conversion_rate');
                            $po_group_product->currency_conversion_rate = $average_currency/$po_group_product->occurrence;

                            $buying_price = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'buying_price_in_thb');
                            $po_group_product->unit_price_in_thb         =  $buying_price / $po_group_product->occurrence;

                            $total_buying_price = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'total_buying_price_in_thb');
                            $po_group_product->total_unit_price_in_thb         =  $total_buying_price;

                        }
                        else
                        {
                            $po_group_product->unit_price                = $p_o_d->pod_unit_price;
                            $po_group_product->currency_conversion_rate  = $p_o_d->currency_conversion_rate;

                            $po_group_product->unit_price_in_thb         =  $p_o_d->unit_price_in_thb;
                            if($po_group_product->discount > 0)
                            {
                                $discount_value = ($p_o_d->unit_price_in_thb * $po_group_product->quantity_inv) * ($po_group_product->discount / 100);
                            }
                            else
                            {
                                $discount_value = 0;
                            }
                            $po_group_product->total_unit_price_in_thb   = $p_o_d->unit_price_in_thb * $po_group_product->quantity_inv - $discount_value;
                            $po_group_product->import_tax_book_price     = ($po_group_product->import_tax_book/100)*$po_group_product->total_unit_price_in_thb;
                        }


                        $po_group_product->save();
                    }
                }
            }

            $po_group = PoGroup::where('id',$po_group->id)->first();

            $total_import_tax_book_price = 0;
            $po_group_details = $po_group->po_group_product_details;
            foreach ($po_group_details as $po_group_detail)
            {
                $total_import_tax_book_price += ($po_group_detail->import_tax_book_price);
            }
            if($total_import_tax_book_price == 0)
            {
                foreach ($po_group_details as $po_group_detail)
                {
                    $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
                    $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
                    $total_import_tax_book_price += $book_tax;
                }
            }

            $po_group->po_group_import_tax_book      = $total_import_tax_book_price;
            $po_group->save();

            if($po_group->tax !== null)
            {
                $group_tax = $po_group->tax;
                // $group_detail = PoGroupProductDetail::where('status',1)->find($pogpd_id);
                $all_record = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group->id);
                $all_record = $all_record->with('product','po_group','get_supplier','product.units','product.sellingUnits')->get();
                 $final_book_percent = 0;
                 foreach ($all_record as $value)
                 {
                     if($value->import_tax_book != null && $value->import_tax_book != 0)
                     {
                         $final_book_percent = $final_book_percent +(($value->import_tax_book/100) * $value->total_unit_price_in_thb);
                     }
                 }
                 foreach($all_record as $group_detail)
                 {
                    $find_item_tax_value = $group_detail->import_tax_book/100 * $group_detail->total_unit_price_in_thb;
                    if($final_book_percent != 0 && $group_tax != 0)
                    {
                        $find_item_tax = $find_item_tax_value / $final_book_percent;

                        $cost = $find_item_tax * $group_tax;
                        if($group_tax != 0)
                        {
                            $group_detail->weighted_percent = number_format(($cost/$group_tax)*100,4,'.','');
                        }
                        else
                        {
                            $group_detail->weighted_percent = 0;
                        }
                            $group_detail->save();

                            $weighted_percent = ($group_detail->weighted_percent/100) * $group_tax;

                        if($group_detail->quantity_inv != 0)
                        {
                            $group_detail->actual_tax_price = number_format(round($find_item_tax*$group_tax,2) / $group_detail->quantity_inv,2,'.','');
                        }
                        else
                        {
                            $group_detail->actual_tax_price = 0;
                        }
                            $group_detail->save();

                        if($group_detail->unit_price_in_thb != 0)
                        {
                            $group_detail->actual_tax_percent = number_format(($group_detail->actual_tax_price/$group_detail->unit_price_in_thb)* 100,2,'.','');
                        }
                        else
                        {
                            $group_detail->actual_tax_percent = 0;
                        }
                            $group_detail->save();
                    }
                 }


                // tO UPDATE WEIGHT
                // $tax = $po_group->tax;
                // $total_import_tax = $po_group->po_group_import_tax_book;
                // $import_tax = $group_detail->import_tax_book;
                // $actual_tax_percent = ($tax/$total_import_tax*$import_tax);
                // $group_detail->actual_tax_percent = $actual_tax_percent;
            }

            if($po_group->freight !== NULL)
            {
                $po_group_details = $po_group->po_group_product_details;
                foreach ($po_group_details as $po_group_detail)
                {
                    $item_gross_weight     = $po_group_detail->total_gross_weight;
                    $total_gross_weight    = $po_group->po_group_total_gross_weight;
                    $total_freight         = $po_group->freight;
                    $total_quantity        = $po_group_detail->quantity_inv;
                    if($total_quantity != 0)
                    {
                        $freight               = (($total_freight*($item_gross_weight/$total_gross_weight))/$total_quantity);
                    }
                    else
                    {
                        $freight = 0;
                    }
                    $po_group_detail->freight = $freight;

                    $po_group_detail->save();
                }
            }

            if($po_group->landing !== NULL)
            {
                $po_group_details = $po_group->po_group_product_details;
                foreach ($po_group_details as $po_group_detail)
                {
                    $item_gross_weight     = $po_group_detail->total_gross_weight;
                    $total_gross_weight    = $po_group->po_group_total_gross_weight;
                    $total_quantity        = $po_group_detail->quantity_inv;
                    $total_landing         = $po_group->landing;
                    if($total_quantity != 0)
                    {
                        $landing               = (($total_landing*($item_gross_weight/$total_gross_weight))/$total_quantity);
                    }
                    else
                    {
                        $landing = 0;
                    }
                    $po_group_detail->landing = $landing;

                    $po_group_detail->save();
                }
            }
        }
    }

    public function updateCCR($data,$user_id,$role_id,$all_pos,$product_id,$c_c_r){
        try{
        $all_pos=$all_pos;
        $user_id=$user_id;
        $product_id=$product_id;
        $c_c_r=$c_c_r;

        if($all_pos != null)
        {
        $total_import_tax_book_price = 0;
        foreach ($all_pos as $find_po) {

           $find_po->exchange_rate = (1/$c_c_r);
            $find_po->save();

            // dd($find_po);

        // $find_po = PurchaseOrder::find($st_id);

        $po_old_import_tax_book_price = $find_po->total_import_tax_book_price;
        $po_old_total_in_thb = $find_po->total_in_thb;
        if($find_po->exchange_rate == null)
        {
            $supplier_conv_rate_thb = $find_po->PoSupplier->getCurrency->conversion_rate;
        }
        else
        {
            $supplier_conv_rate_thb = $find_po->exchange_rate;
        }

        foreach ($find_po->PurchaseOrderDetail as $p_o_d)
        {
            $old_total_import_tax_book_price = $p_o_d->pod_import_tax_book_price;
            $p_o_d->currency_conversion_rate  = $supplier_conv_rate_thb;
            $p_o_d->unit_price_in_thb         = $p_o_d->pod_unit_price/$supplier_conv_rate_thb;
            $p_o_d->total_unit_price_in_thb   = $p_o_d->pod_total_unit_price/$supplier_conv_rate_thb;
            $p_o_d->pod_import_tax_book_price = ($p_o_d->pod_import_tax_book/100)*$p_o_d->total_unit_price_in_thb;
            $total_import_tax_book_price      += ($p_o_d->pod_import_tax_book_price - $old_total_import_tax_book_price);
            $p_o_d->save();
        }

        $find_po->total_import_tax_book_price  += $total_import_tax_book_price;
        $find_po->total_in_thb = $find_po->total/$supplier_conv_rate_thb;
        $find_po->save();

        $total_import_tax_book_price2 = null;
        $total_buying_price_in_thb2   = null;

        // getting all po's with this po group
        $gettingAllPos = PoGroupDetail::select('purchase_order_id')->where('po_group_id', $find_po->po_group_id)->get();
        $po_group = PoGroup::find($find_po->po_group_id);

        if($po_group->is_review == 0 || $po_group->is_review == 1)
        {
            $po_group->po_group_import_tax_book    += ($find_po->total_import_tax_book_price - $po_old_import_tax_book_price);
            $po_group->total_buying_price_in_thb   += ($find_po->total_in_thb - $po_old_total_in_thb);
            $po_group->save();

            #average unit price
            $average_unit_price = 0;
            $average_count = 0;

            foreach ($find_po->PurchaseOrderDetail as $p_o_d) {

                    $po_group_product =  PoGroupProductDetail::where('status',1)->where('product_id',$p_o_d->product_id)->where('po_group_id',$po_group->id)->where('supplier_id',$find_po->supplier_id)->first();

                    if($po_group_product != null)
                    {
                        if($po_group_product->occurrence > 1)
                        {
                            $ccr = $po_group_product->po_group->purchase_orders()->where('supplier_id',$po_group_product->supplier_id)->pluck('id')->toArray();
                            $find_all_purchase_order_detail = PurchaseOrderDetail::whereIn('po_id',$ccr)->where('product_id',$po_group_product->product_id)->get();
                            $po_group_product->unit_price   = ($find_all_purchase_order_detail->sum('pod_unit_price')/$po_group_product->occurrence);
                            $average_currency = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'currency_conversion_rate');
                            $po_group_product->currency_conversion_rate = 1/($average_currency/$po_group_product->occurrence);

                            $buying_price = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'buying_price_in_thb');
                            $po_group_product->unit_price_in_thb         =  $buying_price / $po_group_product->occurrence;

                            $total_buying_price = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'total_buying_price_in_thb');
                            $po_group_product->total_unit_price_in_thb         =  $total_buying_price;

                        }
                        else
                        {
                            $po_group_product->unit_price                = $p_o_d->pod_unit_price;
                            $po_group_product->currency_conversion_rate  = $p_o_d->currency_conversion_rate;

                            $po_group_product->unit_price_in_thb         =  $p_o_d->unit_price_in_thb;
                            if($po_group_product->discount > 0 && $po_group_product->discount != null)
                            {
                                $discount_value = ($p_o_d->unit_price_in_thb * $po_group_product->quantity_inv) * ($po_group_product->discount / 100);
                            }
                            else
                            {
                                $discount_value = 0;
                            }
                            $po_group_product->total_unit_price_in_thb   = $p_o_d->unit_price_in_thb * $po_group_product->quantity_inv - $discount_value;
                            $po_group_product->import_tax_book_price     = ($po_group_product->import_tax_book/100)*$po_group_product->total_unit_price_in_thb;
                        }


                        $po_group_product->save();
                    }
                }
        }
      }

            $po_group = PoGroup::where('id',$po_group->id)->first();

            $total_import_tax_book_price = 0;
            $po_group_details = $po_group->po_group_product_details;
            foreach ($po_group_details as $po_group_detail)
            {
                $total_import_tax_book_price += ($po_group_detail->import_tax_book_price);
            }
            if($total_import_tax_book_price == 0)
            {
                foreach ($po_group_details as $po_group_detail)
                {
                    $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
                    if($count != 0)
                    {
                        $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
                    }
                    else
                    {
                        $book_tax = 0;
                    }
                    $total_import_tax_book_price += $book_tax;
                }
            }

            $po_group->po_group_import_tax_book      = $total_import_tax_book_price;
            $po_group->save();

            if($po_group->tax !== NULL)
            {
                $total_import_tax_book_price = 0;
                $total_import_tax_book_percent = 0;
                $po_group_details = $po_group->po_group_product_details;
                foreach ($po_group_details as $po_group_detail)
                {
                    $total_import_tax_book_price += ($po_group_detail->import_tax_book_price);
                    $total_import_tax_book_percent += ($po_group_detail->import_tax_book);

                    // To Recalculate the Actual tax
                    $tax_value                        = $po_group->tax;
                    $total_import_tax                 = $po_group->po_group_import_tax_book;
                    $import_tax                       = $po_group_detail->import_tax_book;
                    if($total_import_tax != 0 && $import_tax != 0)
                    {
                        $actual_tax_percent               = ($tax_value/$total_import_tax*$import_tax);
                    }
                    else
                    {
                        $actual_tax_percent = null;
                    }
                    $po_group_detail->actual_tax_percent = $actual_tax_percent;

                    $po_group_detail->save();
                }
                if($total_import_tax_book_price == 0)
                {
                    foreach ($po_group_details as $po_group_detail)
                    {
                        $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
                        if($count != 0)
                        {
                            $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
                        }
                        else
                        {
                            $book_tax = 0;
                        }
                        $total_import_tax_book_price += $book_tax;
                    }
                }
                $po_group->po_group_import_tax_book = $total_import_tax_book_price;
                $po_group->total_import_tax_book_percent = $total_import_tax_book_percent;
                $po_group->save();

                foreach ($po_group->po_group_product_details as $group_detail)
                {
                    $tax = $po_group->tax;
                    $total_import_tax = $po_group->po_group_import_tax_book;
                    $import_tax = $group_detail->import_tax_book;
                    if($total_import_tax != 0 && $import_tax != 0)
                    {
                        $actual_tax_percent = ($tax/$total_import_tax*$import_tax);
                        $group_detail->actual_tax_percent = $actual_tax_percent;
                    }

                    $group_detail->save();
                }
            }

            if($po_group->freight !== NULL)
            {
                $po_group_details = $po_group->po_group_product_details;
                foreach ($po_group_details as $po_group_detail)
                {
                    $item_gross_weight     = $po_group_detail->total_gross_weight;
                    $total_gross_weight    = $po_group->po_group_total_gross_weight;
                    $total_freight         = $po_group->freight;
                    $total_quantity        = $po_group_detail->quantity_inv;
                    if($total_quantity != 0 && $total_gross_weight)
                    {
                        $freight               = (($total_freight*($item_gross_weight/$total_gross_weight))/$total_quantity);
                    }
                    else
                    {
                        $freight = 0;
                    }
                    $po_group_detail->freight = $freight;

                    $po_group_detail->save();
                }
            }

            if($po_group->landing !== NULL)
            {
                $po_group_details = $po_group->po_group_product_details;
                foreach ($po_group_details as $po_group_detail)
                {
                    $item_gross_weight     = $po_group_detail->total_gross_weight;
                    $total_gross_weight    = $po_group->po_group_total_gross_weight;
                    $total_quantity        = $po_group_detail->quantity_inv;
                    $total_landing         = $po_group->landing;
                    if($total_quantity != 0 && $total_gross_weight != 0)
                    {
                        $landing               = (($total_landing*($item_gross_weight/$total_gross_weight))/$total_quantity);
                    }
                    else
                    {
                        $landing = 0;
                    }
                    $po_group_detail->landing = $landing;

                    $po_group_detail->save();
                }
            }

            $find_po = $all_pos[0];

            if($po_group->is_review == 1)
            {
                $po_group_product_details = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group->id)->where('supplier_id',$find_po->supplier_id)->where('quantity_inv','!=',0)->get();
                foreach ($po_group_product_details as $p_g_pd)
                {
                    $supplier_product                    = SupplierProducts::where('supplier_id',$p_g_pd->supplier_id)->where('product_id',$p_g_pd->product_id)->first();
                    $supplier_conv_rate_thb = @$p_g_pd->currency_conversion_rate != 0 ? $p_g_pd->currency_conversion_rate : 1 ;
                    $buying_price_in_thb    = $p_g_pd->unit_price_in_thb;

                    $supplier_product->buying_price_in_thb = $buying_price_in_thb;
                    $supplier_product->freight           = $p_g_pd->freight;
                    $supplier_product->landing           = $p_g_pd->landing;
                    if($p_g_pd->quantity_inv != 0)
                    {
                        $supplier_product->extra_cost        = $p_g_pd->total_extra_cost/$p_g_pd->quantity_inv;
                        $supplier_product->extra_tax         = $p_g_pd->total_extra_tax/$p_g_pd->quantity_inv;
                        $supplier_product->gross_weight      = $p_g_pd->total_gross_weight/$p_g_pd->quantity_inv;
                    }
                    else
                    {
                        $supplier_product->extra_cost        = 0;
                        $supplier_product->extra_tax         = 0;
                        $supplier_product->gross_weight      = 0;
                    }
                    $supplier_product->import_tax_actual = $p_g_pd->actual_tax_percent;
                    $supplier_product->save();

                    $product = Product::find($p_g_pd->product_id);
                    // this is the price of after conversion for THB

                    $importTax              = $supplier_product->import_tax_actual;
                    $total_buying_price     = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

                    $total_buying_price     = ($supplier_product->freight)+($supplier_product->landing)+($supplier_product->extra_cost)+($supplier_product->extra_tax)+($total_buying_price);
                    $product->total_buy_unit_cost_price = $total_buying_price;
                    $product->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;
                    $total_selling_price = $product->total_buy_unit_cost_price * $product->unit_conversion_rate;

                    // creating a history on a product detail page which shipment updated the product COGS
                    $product_history              = new ProductHistory;
                    $product_history->user_id     = $user_id;
                    $product_history->product_id  = $product->id;
                    $product_history->group_id    = @$po_group->id;
                    $product_history->column_name = 'COGS Updated through from shipment (Import Job) - '.@$po_group->ref_id.' by updating currency conversion rate.';
                    $product_history->old_value   = @$product->selling_price;
                    $product_history->new_value   = @$total_selling_price;
                    $product_history->save();

                    $product->selling_price = $total_selling_price;
                    $product->supplier_id = $supplier_product->supplier_id;
                    $product->last_price_updated_date = Carbon::now();
                    $product->last_date_import = Carbon::now();
                    $product->save();

                    $p_g_pd->product_cost = $total_selling_price;
                    $p_g_pd->save();

                    $stock_out = StockManagementOut::where('po_group_id',$p_g_pd->po_group_id)->where('product_id',$p_g_pd->product_id)->where('warehouse_id',$p_g_pd->to_warehouse_id)->update(['cost' => $p_g_pd->product_cost,'cost_date' => Carbon::now()]);
                }


                $purchase_orders = PurchaseOrder::whereIn('id',$all_pos->pluck('id')->toArray())->get();
                foreach ($purchase_orders as $PO)
                {
                    $purchase_order_details = PurchaseOrderDetail::where('po_id',$PO->id)->whereNotNull('purchase_order_details.product_id')->where('quantity','!=',0)->get();
                    foreach ($purchase_order_details as $p_o_d)
                    {
                        $product_id = $p_o_d->product_id;
                        if($p_o_d->order_product_id != null)
                        {
                            $product                = Product::find($p_o_d->product_id);
                            $p_o_d->order_product->actual_cost = $product != null ? $product->selling_price : 0;
                            $p_o_d->order_product->save();
                        }
                    }
                }
            }

            }
        }
        catch(Exception $e)
        {
            ExportStatus::where('type','products_receiving_importings_bulk_job')->update(['status'=>2,'last_downloaded'=>date('Y-m-d'),'exception'=>$e->getMessage()]);
            dd($e);
        }
    }

    public function UpdateUnitPrice(Request $request, $old_value, $group_id)
    {
        // dd($request->from_bulk);
        $user = User::find($request->user_id);
        $checkSameProduct = PurchaseOrderDetail::find($request->rowId);
        $po = PurchaseOrder::with('PoSupplier.getCurrency')->find($request->po_id);

        $pod_unit_price = $request->unit_price;
        $pod_vat_value  = $checkSameProduct->pod_vat_actual;
        $vat_amount     = $pod_unit_price * ( $pod_vat_value / 100 );
        $vat_amount     = number_format($vat_amount,4,'.','');

        if($checkSameProduct->is_billed == "Product")
        {
            if($po->exchange_rate == null)
            {
                $supplier_conv_rate_thb = $po->PoSupplier->getCurrency->conversion_rate;
            }
            else
            {
                $supplier_conv_rate_thb = $po->exchange_rate;
            }

            $checkSameProduct->pod_unit_price        = $request->unit_price;
            $checkSameProduct->last_updated_price_on = date('Y-m-d');
            $checkSameProduct->pod_total_unit_price  = ($request->unit_price * $checkSameProduct->quantity);

            $checkSameProduct->pod_unit_price_with_vat       = number_format($request->unit_price + $vat_amount,3,'.','');
            $checkSameProduct->pod_total_unit_price_with_vat = number_format($checkSameProduct->pod_unit_price_with_vat * $checkSameProduct->quantity,3,'.','');
            $checkSameProduct->pod_vat_actual_price          = $vat_amount;
            $checkSameProduct->pod_vat_actual_total_price    = number_format($vat_amount * $checkSameProduct->quantity,3,'.','');

            $calculations = $checkSameProduct->total_unit_price_in_thb * ($checkSameProduct->pod_import_tax_book / 100);
            $checkSameProduct->pod_import_tax_book_price = $calculations;

            $checkSameProduct->save();

            $checkSameProduct->pod_import_tax_book_price = ($checkSameProduct->pod_import_tax_book/100)*$checkSameProduct->total_unit_price_in_thb;
            $checkSameProduct->save();

            if($po->status == 13 || $po->status == 14)
            {
                $checkSameProduct = PurchaseOrderDetail::with('PurchaseOrder')->find($request->rowId);
                if($checkSameProduct->product_id != null)
                {
                    if($checkSameProduct->PurchaseOrder->supplier_id != null && $checkSameProduct->PurchaseOrder->from_warehouse_id == null)
                    {
                        $supplier_id = $checkSameProduct->PurchaseOrder->supplier_id;
                    }
                    else
                    {
                        $supplier_id = $checkSameProduct->product->supplier_id;
                    }

                    if($checkSameProduct->PurchaseOrder->exchange_rate != NULL)
                    {
                        $supplier_conv_rate_thb = $checkSameProduct->PurchaseOrder->exchange_rate;
                    }
                    else
                    {
                        $supplier_conv_rate_thb = $checkSameProduct->PurchaseOrder->PoSupplier->getCurrency->conversion_rate;
                    }

                    if($checkSameProduct->discount !== null)
                    {
                        $discount_price = $checkSameProduct->quantity * $request->unit_price - (($checkSameProduct->quantity * $request->unit_price) * ($checkSameProduct->discount / 100));
                        if($checkSameProduct->quantity != 0 && $checkSameProduct->quantity != null)
                        {
                            $after_discount_price = ($discount_price / $checkSameProduct->quantity);
                        }
                        else
                        {
                            $after_discount_price = $discount_price;
                        }
                        $unit_price = $after_discount_price;
                    }
                    else
                    {
                        $unit_price = $request->unit_price;
                    }

                    if($checkSameProduct->discount < 100 || $checkSameProduct->discount == null)
                    {
                        $getProductSupplier = SupplierProducts::where('product_id',$checkSameProduct->product_id)->where('supplier_id',$supplier_id)->first();
                        $old_price_value = $getProductSupplier->buying_price;

                        $getProductSupplier->buying_price = $unit_price;
                        $getProductSupplier->buying_price_in_thb = ($unit_price / $supplier_conv_rate_thb);
                        $getProductSupplier->save();

                        $product_detail = Product::find($checkSameProduct->product_id);
                        if($getProductSupplier !== null && $product_detail->supplier_id == $supplier_id)
                        {
                            $buying_price_in_thb = ($unit_price / $supplier_conv_rate_thb);

                            $importTax = $getProductSupplier->import_tax_actual !== null ? $getProductSupplier->import_tax_actual : $product_detail->import_tax_book;

                            $total_buying_price = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

                            $total_buying_price = ($getProductSupplier->freight)+($getProductSupplier->landing)+($getProductSupplier->extra_cost)+($total_buying_price);

                            $product_detail->total_buy_unit_cost_price = $total_buying_price;

                            // this is supplier buying unit cost price
                            $product_detail->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;

                            // this is selling price
                            $total_selling_price = $product_detail->total_buy_unit_cost_price * $product_detail->unit_conversion_rate;

                            $product_detail->selling_price = $total_selling_price;
                            $product_detail->last_price_updated_date = Carbon::now();
                            $product_detail->save();

                            $product_history              = new ProductHistory;
                            $product_history->user_id     = $user != null ? $user->id : Auth::user()->id;
                            $product_history->product_id  = $checkSameProduct->product_id;
                            $product_history->column_name = "Purchasing Price (From PO - ".$checkSameProduct->PurchaseOrder->ref_id.")"." Ref ID#. ".$checkSameProduct->id;
                            $product_history->old_value   = $old_price_value;
                            $product_history->new_value   = $unit_price;
                            $product_history->save();
                        }
                    }
                }
            }

            $order_history                   = new PurchaseOrdersHistory;
            $order_history->user_id          = $user != null ? $user->id : Auth::user()->id;
            $order_history->order_id         = $checkSameProduct->order_id;
            $order_history->reference_number = @$checkSameProduct->product->refrence_code;
            $order_history->old_value        = @$request->old_value;
            $order_history->column_name      = "Unit Price";
            $order_history->new_value        = @$request->unit_price;
            $order_history->po_id            = @$checkSameProduct->po_id;
            $order_history->pod_id           = @$checkSameProduct->id;
            $order_history->save();

            $p_o_p_d = PoGroupProductDetail::where('status',1)->where('po_group_id',$group_id)->where('product_id',$checkSameProduct->product_id)->first();

            if($request->unit_price != $old_value){
                $this->setHistory($this->user_id,$p_o_p_d,$old_value,'Unit Price',$request->unit_price);
            }

        }
        else
        {
            // dd('here');
            $checkSameProduct->pod_unit_price = $request->unit_price;
            $checkSameProduct->pod_total_unit_price = ($request->unit_price * $checkSameProduct->quantity);

            $checkSameProduct->pod_unit_price_with_vat       = number_format($request->unit_price + $vat_amount,3,'.','');
            $checkSameProduct->pod_total_unit_price_with_vat = number_format($checkSameProduct->pod_unit_price_with_vat * $checkSameProduct->quantity,3,'.','');
            $checkSameProduct->pod_vat_actual_price          = $vat_amount;
            $checkSameProduct->pod_vat_actual_total_price    = number_format($vat_amount * $checkSameProduct->quantity,3,'.','');

            $calculations = $checkSameProduct->total_unit_price_in_thb * ($checkSameProduct->pod_import_tax_book / 100);
            $checkSameProduct->pod_import_tax_book_price = $calculations;

            // $vat_calculations = $checkSameProduct->total_unit_price_in_thb * ($checkSameProduct->pod_vat_actual / 100);
            // $checkSameProduct->pod_vat_actual_price = $vat_calculations;
            $checkSameProduct->save();

            $order_history = new PurchaseOrdersHistory;
            $order_history->user_id = $user != null ? $user->id : Auth::user()->id;
            $order_history->order_id = $checkSameProduct->order_id;
            $order_history->reference_number = "Billed Item";
            $order_history->old_value = @$request->old_value;
            $order_history->column_name = "Unit Price";
            $order_history->new_value = @$request->unit_price;
            $order_history->po_id = @$checkSameProduct->po_id;
            $order_history->pod_id = @$checkSameProduct->id;
            $order_history->save();
        }

        return response()->json([
            'success'   => true,
            // 'sub_total' => $grandCalculations['sub_total'],
            // 'vat_amout' => $grandCalculations['vat_amout'],
            // 'total_w_v' => $grandCalculations['total_w_v'],
        ]);
    }

    public function SavePoProductDiscount(Request $request, $old_value_discount)
    {
        // dd($request->all());
        $user = User::find($request->user_id);
        $po = PurchaseOrderDetail::with('PurchaseOrder','product.supplier_products')->where('id',$request->rowId)->where('po_id',$request->po_id)->first();
        $checkSameProduct = PurchaseOrderDetail::where('po_id',$po->po_id)->where('product_id',$po->product_id)->get();
        // $po_group_p_detail = PoGroupProductDetail::where('status',1)->find($request->pogpd__id);
        foreach($request->except('rowId','po_id','from_bulk','old_value') as $key => $value)
        {
            if($key == 'discount')
            {
                $po->$key = $value;
                $po->save();

                $order_history = new PurchaseOrdersHistory;
                $order_history->user_id = $user != null ? $user->id : Auth::user()->id;
                $order_history->order_id = @$po->order_id;
                if($po->is_billed == "Billed")
                {
                    $order_history->reference_number = "Billed Item";
                }
                else
                {
                    $order_history->reference_number = @$po->product->refrence_code;
                }
                $order_history->old_value = @$request->old_value;

                $order_history->column_name = "Discount";

                $order_history->new_value = @$value;
                $order_history->po_id = @$po->po_id;
                $order_history->save();
            }
        }

        if($po->PurchaseOrder->status == 13 || $po->PurchaseOrder->status == 14 || $po->PurchaseOrder->status > 14)
        {
            // $checkSameProduct2 = PurchaseOrderDetail::find($request->rowId);
            $checkSameProduct2 = $po;
            if ($checkSameProduct2)
            {
                $total = ($checkSameProduct2->pod_unit_price * $checkSameProduct2->quantity) * ($checkSameProduct2->discount / 100);
                $total_thb = ($checkSameProduct2->unit_price_in_thb * $checkSameProduct2->quantity) * ($checkSameProduct2->discount / 100);
                $po->pod_total_unit_price = $total;
                $po->total_unit_price_in_thb = $total_thb;
                $po->save();

                if($checkSameProduct2->product_id != null)
                {
                    if($checkSameProduct2->PurchaseOrder->supplier_id == NULL && $checkSameProduct2->PurchaseOrder->from_warehouse_id != NULL)
                    {
                        $supplier_id = $checkSameProduct2->product->supplier_id;
                    }
                    else
                    {
                        $supplier_id = $checkSameProduct2->PurchaseOrder->supplier_id;
                    }

                    // this is the price of after conversion for THB
                    if($checkSameProduct2->PurchaseOrder->exchange_rate != NULL)
                    {
                        $supplier_conv_rate_thb = $checkSameProduct2->PurchaseOrder->exchange_rate;
                    }
                    else
                    {
                        $supplier_conv_rate_thb = $checkSameProduct2->PurchaseOrder->PoSupplier->getCurrency->conversion_rate;
                    }

                    if($checkSameProduct2->pod_unit_price !== NULL)
                    {
                        $discount_price = $checkSameProduct2->quantity * $checkSameProduct2->pod_unit_price - (($checkSameProduct2->quantity * $checkSameProduct2->pod_unit_price) * ($checkSameProduct2->discount / 100));

                        if($checkSameProduct2->quantity !== NULL && $checkSameProduct2->quantity !== 0 && $checkSameProduct2->quantity != 0 )
                        {
                            $after_discount_price = ($discount_price / $checkSameProduct2->quantity);
                        }
                        else
                        {
                            $after_discount_price = ($discount_price);
                        }
                        $unit_price = $after_discount_price;
                    }
                    else
                    {
                        $unit_price = $checkSameProduct2->pod_unit_price;
                    }

                    if($checkSameProduct2->discount < 100 || $checkSameProduct2->discount == null)
                    {
                        // $getProductSupplier = SupplierProducts::where('product_id',@$checkSameProduct2->product_id)->where('supplier_id',@$supplier_id)->first();
                        $getProductSupplier = $checkSameProduct2->product->supplier_products->where('supplier_id',@$supplier_id)->first();
                        $old_price_value    = $getProductSupplier->buying_price;

                        $getProductSupplier->buying_price_in_thb = ($unit_price / $supplier_conv_rate_thb);
                        $getProductSupplier->buying_price = $unit_price;
                        $getProductSupplier->save();

                        // $product_detail = Product::find($checkSameProduct2->product_id);
                        $product_detail = $checkSameProduct2->product;

                        if($getProductSupplier !== null && $product_detail->supplier_id == $supplier_id)
                        {
                            $buying_price_in_thb = ($getProductSupplier->buying_price / $supplier_conv_rate_thb);

                            $importTax = $getProductSupplier->import_tax_actual !== null ? $getProductSupplier->import_tax_actual : $product_detail->import_tax_book;

                            $total_buying_price = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

                            $total_buying_price = ($getProductSupplier->freight)+($getProductSupplier->landing)+($getProductSupplier->extra_cost)+($getProductSupplier->extra_tax)+($total_buying_price);

                            $product_detail->total_buy_unit_cost_price = $total_buying_price;

                            // this is supplier buying unit cost price
                            $product_detail->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;

                            // this is selling price
                            $total_selling_price = $product_detail->total_buy_unit_cost_price * $product_detail->unit_conversion_rate;

                            $product_detail->selling_price = $total_selling_price;
                            $product_detail->save();

                            $product_history = new ProductHistory;
                            $product_history->user_id = $user != null ? $user->id : Auth::user()->id;
                            $product_history->product_id = $product_detail->id;
                            $product_history->column_name = "Purchasing Price (From PO - ".$checkSameProduct2->PurchaseOrder->ref_id.")"." Ref ID#. ".$checkSameProduct2->id;
                            $product_history->old_value = $old_price_value;
                            $product_history->new_value = $unit_price;
                            $product_history->save();
                        }
                    }
                }
            }
        }

        /*calulation through a function*/
        // $objectCreated = new PurchaseOrderDetail;
        // $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($request->po_id);

        $po_modifications = PurchaseOrder::find($request->po_id);
        // $po_modifications->total = $grandCalculations['sub_total'];
        // $po_modifications->save();

        if($po_modifications->status > 13)
        {
            $p_o_p_d = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_modifications->po_group_id)->where('product_id',$po->product_id)->first();
            $updated_pod = PurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->po_id)->first();
            if($p_o_p_d->occurrence == 1)
            {
                $p_o_p_d->discount = $updated_pod->discount;
                $p_o_p_d->total_unit_price = $updated_pod->pod_total_unit_price;
                $p_o_p_d->total_unit_price_in_thb = $updated_pod->total_unit_price_in_thb;
                $p_o_p_d->save();
            }
            else
            {
                $po_ids = $p_o_p_d->po_group->po_group_detail->pluck('purchase_order_id')->toArray();
                $pods = PurchaseOrderDetail::with('PurchaseOrder')->whereIn('po_id',$po_ids)->whereHas('PurchaseOrder',function($po) use ($p_o_p_d){
                            $po->where('supplier_id',$p_o_p_d->supplier_id);
                        })->where('product_id',$p_o_p_d->product_id)->get();
                $p_o_p_d->discount = $pods->sum('discount') / $p_o_p_d->occurrence;
                $p_o_p_d->save();
            }
            // if($p_o_p_d->discount != $old_value_discount){
            //     $this->setHistory($this->user_id,$p_o_p_d,$old_value_discount,'Discount',$p_o_p_d->discount);
            // }
        }

        return response()->json([
            'success'   => true,
            // 'sub_total' => $grandCalculations['sub_total'],
            // 'total_qty' => $grandCalculations['total_qty'],
            // 'vat_amout' => $grandCalculations['vat_amout'],
            // 'total_w_v' => $grandCalculations['total_w_v']
        ]);
    }

    public function SavePoProductQuantity(Request $request,$old_value_pogpd)
    {
        // dd($request->all());
        $po = PurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->po_id)->first();
        $field = null;
        $user = User::find($request->user_id);

        foreach($request->except('rowId','po_id','from_bulk','old_value','user_id','pogpd__id','from_bulk') as $key => $value)
        {
            $field = $key;
            if($key == 'quantity')
            {
                if($key == 'quantity' && $po->product != null)
                {
                    $decimal_places = $po->product->units->decimal_places;
                    $value = round($value,$decimal_places);
                }
                if($po->PurchaseOrder->status == 21)
                {
                    $stock_q = $po->update_stock_card($po,$value);
                }

                $po->$key = $value;
                $order_history = new PurchaseOrdersHistory;
                $order_history->user_id = $user != null ? $user->id : Auth::user()->id;
                $order_history->order_id = $po->order_id;
                if($po->is_billed == "Billed")
                {
                    $order_history->reference_number = "Billed Item";
                }
                else
                {
                    $order_history->reference_number = @$po->product->refrence_code;
                }

                $order_history->old_value = @$request->old_value;
                $order_history->column_name = "Quantity";
                $order_history->new_value = @$value;
                $order_history->po_id = @$po->po_id;
                $order_history->pod_id = @$po->id;
                $order_history->save();

                if($po->get_td_reserved->count() > 0)
                {
                    foreach ($po->get_td_reserved as $res) {
                        $stock_out = StockManagementOut::find($res->stock_id);
                        if($stock_out)
                        {
                            $stock_out->available_stock += $res->reserved_quantity;
                            $stock_out->save();
                        }

                        $res->delete();
                    }
                }
            }
        }

        if($po->product_id != null && $po->billed_unit_per_package > 0)
        {
            $po->desired_qty = ($po->quantity / $po->billed_unit_per_package);
        }
        // updating total unit price and total gross weight of this item
        $po->pod_total_unit_price              = ($po->pod_unit_price * $po->quantity);
        $po->pod_vat_actual_total_price        = ($po->pod_vat_actual_price * $po->quantity);
        $po->pod_vat_actual_total_price_in_thb = ($po->pod_vat_actual_price_in_thb * $po->quantity);
        $po->pod_total_unit_price_with_vat     = ($po->pod_unit_price_with_vat * $po->quantity);
        $po->pod_total_gross_weight            = ($po->pod_gross_weight * $po->quantity);

        $calculations     = $po->total_unit_price_in_thb * ($po->pod_import_tax_book / 100);
        $po->pod_import_tax_book_price = $calculations;
        // $vat_calculations = $po->total_unit_price_in_thb * ($po->pod_vat_actual / 100);
        // $po->pod_vat_actual_price      = $vat_calculations;
        $po->save();

        // getting product total amount as quantity
        $amount = 0;
        $updateRow = PurchaseOrderDetail::find($po->id);

        $amount = $updateRow->quantity * $updateRow->pod_unit_price;
        $amount = number_format($amount, 3, '.', ',');
        // dd($field);

        /*calulation through a function*/
        // $objectCreated = new PurchaseOrderDetail;
        // $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($request->po_id);
        if($field == 'quantity')
        {
            $po_totoal_change = PurchaseOrder::find($request->po_id);
            // dd($po_totoal_change);
            if($po_totoal_change->status >= 14)
            {
                if($request->pogpd__id != null)
                {
                    $po_group_p_detail = PoGroupProductDetail::where('status',1)->find($request->pogpd__id);
                    // dd($po_group_p_detail);
                    if($po_group_p_detail->occurrence == 1)
                    {
                        $po_group_p_detail->quantity_inv = $updateRow->quantity;
                        $po_group_p_detail->save();
                    }

                    if($po_group_p_detail->occurrence > 1)
                    {

                        $all_ids = PurchaseOrder::where('po_group_id',$po_totoal_change->po_group_id)->where('supplier_id',$po_totoal_change->supplier_id)->pluck('id');

                        $all_record = PurchaseOrderDetail::whereIn('po_id',$all_ids)->where('product_id',$po_group_p_detail->product_id)->sum('quantity');

                        $desired_qty = PurchaseOrderDetail::whereIn('po_id',$all_ids)->where('product_id',$po_group_p_detail->product_id)->sum('desired_qty');

                        // $po_group_p_detail->quantity_inv -= $request->old_value;
                        // $po_group_p_detail->save();
                        $po_group_p_detail->quantity_inv = $all_record;
                        $po_group_p_detail->quantity_ordered = $desired_qty;
                        $po_group_p_detail->save();
                    }
                }
            }
        }

        $this->setHistory($this->user_id,$po_group_p_detail,$old_value_pogpd,'Quantity Inv',$po_group_p_detail->quantity_inv);

        return response()->json([
            'success'   => true,
            'updateRow' => $updateRow,
            'amount'    => $amount,
            // 'sub_total' => $grandCalculations['sub_total'],
            // 'total_qty' => $grandCalculations['total_qty'],
            // 'vat_amout' => $grandCalculations['vat_amout'],
            // 'total_w_v' => $grandCalculations['total_w_v']
        ]);
    }

    public function updateGrossWeight(Request $request, $old_g_w)
    {
        $checkSameProduct = PurchaseOrderDetail::with('product')->find($request->pod_id);
        if($checkSameProduct->is_billed == "Product")
        {
            $checkSameProduct->pod_gross_weight       = $request->unit_gross_weight;
            $checkSameProduct->pod_total_gross_weight = ($request->unit_gross_weight * $checkSameProduct->quantity);
            $checkSameProduct->save();
        }

        $po = PurchaseOrder::with('PurchaseOrderDetail')->find($checkSameProduct->po_id);
        $po->total_gross_weight = $po->PurchaseOrderDetail != null ? $po->PurchaseOrderDetail->sum('pod_total_gross_weight') : 0;
        $po->save();

        $order_history                   = new PurchaseOrdersHistory;
        $order_history->user_id          = $request->user_id;
        $order_history->order_id         = @$checkSameProduct->order_id;
        $order_history->reference_number = @$checkSameProduct->product->refrence_code;
        $order_history->old_value        = @$request->old_value;
        $order_history->column_name      = "Gross weight";
        $order_history->new_value        = @$request->unit_gross_weight;
        $order_history->po_id            = @$checkSameProduct->po_id;
        $order_history->pod_id           = @$checkSameProduct->id;
        $order_history->save();

        $po_detail = PoGroupProductDetail::where('status',1)->where('id',$request->pogd_id)->first();

        // if($po_detail->pod_gross_weight != $old_g_w){
        //     $this->setHistory($this->user_id,$po_detail,$old_g_w,'Gross Weight',$order_history->new_value);
        // }


        if($po_detail == null)
        {
            dd($request->pogd_id);
        }
        $po_ids = $po_detail->po_group->po_group_detail()->pluck('purchase_order_id')->toArray();
        $pods = PurchaseOrderDetail::whereIn('po_id',$po_ids)->whereHas('PurchaseOrder',function($po) use ($po_detail){
                $po->where('supplier_id',$po_detail->supplier_id);
            })->where('product_id',$po_detail->product_id)->get();

        $po_detail->total_gross_weight = $pods->sum('pod_total_gross_weight');
        $po_detail->unit_gross_weight = $pods->sum('pod_gross_weight');
        $po_detail->save();

        if($po_detail->occurrence > 1)
        {
            $po_detail->unit_gross_weight = $po_detail->unit_gross_weight / $po_detail->occurrence;
            $po_detail->save();
        }

        #Update Product gross weight
        $purchase_order_detail = $checkSameProduct;
        if($purchase_order_detail->PurchaseOrder->supplier_id == NULL && $purchase_order_detail->PurchaseOrder->from_warehouse_id != NULL)
        {
            $supplier_id = $purchase_order_detail->product->supplier_id;
        }
        else
        {
            $supplier_id = $purchase_order_detail->PurchaseOrder->PoSupplier->id;
        }

        $getProductSupplier = SupplierProducts::where('product_id',@$purchase_order_detail->product_id)->where('supplier_id',@$supplier_id)->first();

        $product_detail = Product::find($purchase_order_detail->product_id);
        $value = $checkSameProduct->pod_gross_weight;
        if($getProductSupplier !== null && $product_detail->supplier_id == $supplier_id)
        {
            if($value != 0)
            {
                $getProductSupplier->gross_weight = $value;
                $getProductSupplier->save();
            }

        }
        elseif($getProductSupplier !== null)
        {
            if($value != 0)
            {
                $getProductSupplier->gross_weight = $value;
                $getProductSupplier->save();
            }
        }
        #End Product update

        $po_group_total_gross_weight = 0;
        $po_group_total_gross_weight = $po_detail->po_group->po_group_product_details->sum('total_gross_weight');
        $po_detail->po_group->po_group_total_gross_weight = $po_group_total_gross_weight;
        $po_detail->po_group->save();

        return true;

    }

    public function setHistory($user_id,$po_details,$old_value,$column_name,$new_value){
        // dd('here');
        $PoGroupProduct_history = new PoGroupProductHistory;
        $PoGroupProduct_history->user_id = $user_id;
        $PoGroupProduct_history->ref_id = $po_details->po_group->ref_id;
        $PoGroupProduct_history->order_product_id = $po_details->product_id;
        $PoGroupProduct_history->po_group_id = $po_details->po_group_id;
        $PoGroupProduct_history->old_value = round($old_value,4);
        $PoGroupProduct_history->column_name = $column_name;
        $PoGroupProduct_history->new_value = round($new_value,4);
        $PoGroupProduct_history->save();
    }
}
