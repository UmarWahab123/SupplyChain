<?php

namespace App\Jobs\Importing;

use App\ExportStatus;
use App\FailedJobException;
use App\Jobs\UpdateOldRecord;
use App\Models\Common\ColumnDisplayPreference;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\PoGroup;
use App\Models\Common\PoGroupDetail;
use App\Models\Common\PoGroupProductDetail;
use App\Models\Common\Product;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PurchaseOrdersHistory;
use App\Models\Common\StockManagementOut;
use App\Models\Common\SupplierProducts;
use App\Models\Common\TableHideColumn;
use App\Models\Common\Warehouse;
use App\Models\Common\WarehouseProduct;
use App\Models\ProductReceivingImportTemp;
use App\ProductHistory;
use App\User;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;

class ConfirmGroupImportData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $user_id;
    protected $query;
    public $tries = 2;
    public $timeout = 1800; //30 minutes
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($query, $user_id)
    {
        $this->query = $query;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $query = $this->query;
        $user_id = $this->user_id;
        // dd($query);
        try {
            foreach ($query as $row) {
                $pod_id = intval($row->pod_id);
                $pogpd_id = intval($row->p_c_id);
                $po_id = intval($row->po_id);
                $group_id = intval($row->group_id);

                if($row->discount !== null && $row->discount >= 0)
                {
                    $po = PurchaseOrderDetail::where('id',$pod_id)->where('po_id',$po_id)->first();
                    $old_value_discount = $po->discount;
                    // $checkSameProduct = PurchaseOrderDetail::where('po_id',$po->po_id)->where('product_id',$po->product_id)->get();

                    if($old_value_discount != $row->discount)
                    {
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['rowId' => $po->id,'user_id' => $user_id,'pogpd__id' => $pogpd_id, 'po_id' => $po->po_id,'from_bulk' => 'yes','discount' => $row->discount, 'old_value' => $old_value_discount]);
                        $this->SavePoProductDiscount($request);
                    }
                }

                if($row->qty_inv !== null && $row->qty_inv >= 0)
                {
                    $po = PurchaseOrderDetail::where('id',$pod_id)->where('po_id',$po_id)->first();
                    $old_value_quantity = $po->quantity;
                    if($old_value_quantity != $row->qty_inv)
                    {
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['rowId' => $po->id,'user_id' => $user_id,'pogpd__id' => $pogpd_id, 'po_id' => $po->po_id,'from_bulk' => 'yes','quantity' => $row->qty_inv, 'old_value' => $old_value_quantity]);
                        $this->SavePoProductQuantity($request);
                    }
                }
                if(($row->purchasing_price_euro !== null && $row->purchasing_price_euro !== '' && $row->purchasing_price_euro >= 0) || $row->qty_inv_updated == 1)
                {
                    $old_value = round($row->purchasing_price_euro_old,3);
                    if($old_value != $row->purchasing_price_euro)
                    {
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['rowId' => $pod_id, 'po_id' => $po_id,'unit_price' => $row->purchasing_price_euro,'from_bulk' => 'yes', 'old_value' => $old_value,'user_id' => $user_id]);
                        $this->UpdateUnitPrice($request);
                    }
                    
                }

                if($row->gross_weight !== null && $row->gross_weight >= 0)
                {
                    $old_g_w = round($row->gross_weight_old,3);
                    if(round($row->gross_weight_old,4) != round($row->gross_weight,4) && $row->gross_weight_old !== $row->gross_weight || $row->qty_inv_updated == 1)
                    {
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['pogd_id' => $pogpd_id, 'unit_gross_weight' => $row->gross_weight,'po_group_id' => $row->group_id, 'old_value' => $old_g_w,'user_id' => $user_id,'pod_id' => $pod_id]);

                        $this->updateGrossWeight($request);
                    }
                }
                if($row->total_gross_weight !== null && $row->total_gross_weight >= 0 && $row->gross_weight == null)
                {
                    $old_g_w = $row->total_gross_weight_old;
                    if($row->total_gross_weight_old != $row->total_gross_weight && $row->total_gross_weight_old !== $row->total_gross_weight)
                    {
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['pod_id' => $pogpd_id, 'total_gross_weight' => $row->total_gross_weight,'po_group_id' => $row->group_id, 'old_value' => $old_g_w,'user_id' => $user_id]);
                        app('App\Http\Controllers\Importing\PoGroupsController')->editPoGroupProductDetails($request);
                    }
                }
                if($row->extra_cost !== null && $row->extra_cost !== '--' && $row->extra_cost >= 0)
                {
                    $popd = PoGroupProductDetail::where('status',1)->find($pogpd_id);
                    if($popd != null)
                    {
                        if($popd->occurrence > 1)
                        {
                            $pod_detail = PurchaseOrderDetail::find($pod_id);
                            $old_extra_cost = round($pod_detail->unit_extra_cost,4);
                            if(($old_extra_cost != round($row->extra_cost,4)) || $row->qty_inv_updated == 1)
                            {
                                //to find total extra cost from unit extra cost
                                if($pod_detail->quantity == 0)
                                {
                                    $t_e_c = 0;
                                }
                                else
                                {
                                    $t_e_c = ($row->extra_cost * $pod_detail->quantity);
                                } 

                                $pod_detail->unit_extra_cost = $row->extra_cost;
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
                            if((round($row->extra_cost,4) != round($popd->unit_extra_cost,4)) || $row->qty_inv_updated == 1)
                            {
                                $popd->unit_extra_cost = $row->extra_cost;
                                $popd->total_extra_cost = $row->extra_cost * $popd->quantity_inv;
                                $popd->save();
                            }
                        }
                        
                    }
                }
                if($row->total_extra_cost !== null && $row->total_extra_cost !== '--' && $row->total_extra_cost >= 0 && $row->extra_cost == null)
                {
                    $popd = PoGroupProductDetail::where('status',1)->find($pogpd_id);
                    if($popd != null)
                    {
                        if($popd->occurrence > 1)
                        {
                            $pod_detail = PurchaseOrderDetail::find($pod_id);
                            $old_total_extra_cost = round($pod_detail->total_extra_cost,4);
                            if(($old_total_extra_cost != round($row->total_extra_cost,4)) || $row->qty_inv_updated == 1)
                            {
                                //to find unit extra cost from total extra cost
                                if($pod_detail->quantity == 0 || $pod_detail->quantity === 0)
                                {
                                    $u_e_c = 0;
                                }
                                else
                                {
                                    $u_e_c = ($row->total_extra_cost / $pod_detail->quantity);
                                }

                                $pod_detail->total_extra_cost = $row->total_extra_cost;
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
                            if((round($row->total_extra_cost,4) != round($popd->total_extra_cost,4)) || $row->qty_inv_updated == 1)
                            {
                                if($popd->quantity_inv != 0 && $popd->quantity_inv !== 0)
                                {
                                    $popd->total_extra_cost = $row->total_extra_cost;
                                    $popd->unit_extra_cost = $row->total_extra_cost/$popd->quantity_inv;
                                }
                                else
                                {
                                    $popd->total_extra_cost = 0;
                                }
                                $popd->save();
                            }
                        }
                        
                    }
                }

                if($row->extra_tax !== null && $row->extra_tax !== '--' && $row->extra_tax >= 0)
                {
                    $popd = PoGroupProductDetail::where('status',1)->find($pogpd_id);
                    if($popd != null)
                    {
                        if($popd->occurrence > 1)
                        {
                            $pod_detail = PurchaseOrderDetail::find($pod_id);
                            $old_extra_tax = round($pod_detail->unit_extra_tax,4);
                            if(($old_extra_tax != round($row->extra_tax,4)) || $row->qty_inv_updated == 1)
                            {
                                //to find total extra tax unit total extra tax
                                if($pod_detail->quantity == 0 || $pod_detail->quantity === 0)
                                {
                                    $t_e_t = 0;
                                }
                                else
                                {
                                    $t_e_t = ($row->extra_tax * $pod_detail->quantity);
                                }

                                $pod_detail->unit_extra_tax = $row->extra_tax;
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
                        }
                        else
                        {
                            if((round($row->extra_tax,4) != round($popd->unit_extra_tax,4)) || $row->qty_inv_updated == 1)
                            {
                                $popd->unit_extra_tax = $row->extra_tax;
                                $popd->total_extra_tax = $row->extra_tax * $popd->quantity_inv;
                                $popd->save();
                            }
                        }
                        
                    }
                }
                if($row->total_extra_tax !== null && $row->total_extra_tax !== '--' && $row->total_extra_tax >= 0 && $row->extra_tax == null)
                {
                    $popd = PoGroupProductDetail::where('status',1)->find($pogpd_id);
                    if($popd != null)
                    {
                        if($popd->occurrence > 1)
                        {
                            $pod_detail = PurchaseOrderDetail::find($pod_id);
                            $old_total_extra_tax = round($pod_detail->total_extra_tax,4);
                            if(($old_total_extra_tax != round($row->total_extra_tax,4)) || $row->qty_inv_updated == 1)
                            {
                                //to find unit extra tax from total extra tax
                                if($pod_detail->quantity == 0 || $pod_detail->quantity === 0)
                                {
                                    $u_e_t = 0;
                                }
                                else
                                {
                                    $u_e_t = ($row->total_extra_tax / $pod_detail->quantity);
                                }

                                $pod_detail->total_extra_tax = $row->total_extra_tax;
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
                            if((round($row->total_extra_tax,4) != round($popd->total_extra_tax,4)) || $row->qty_inv_updated == 1)
                            {
                                if($popd->quantity_inv != 0 && $popd->quantity_inv !== 0)
                                {
                                    $popd->total_extra_tax = $row->total_extra_tax;
                                    $popd->unit_extra_tax = $row->total_extra_tax/$popd->quantity_inv;
                                }
                                else
                                {
                                    $popd->total_extra_tax = 0;
                                }
                                $popd->save();
                            }
                        }
                        
                    }
                }

                if($row->currency_conversion_rate != null)
                {
                    //currency conversion rate
                    $popd = PoGroupProductDetail::where('status',1)->find($pogpd_id);
                    $po = PurchaseOrderDetail::where('id',$pod_id)->where('po_id',$po_id)->first();
                    $old_value_ccr = round($popd->currency_conversion_rate,5);
                    $new_ccr = 1;

                    if($row->currency_conversion_rate != 0 && $row->currency_conversion_rate !== 0)
                    {
                        $new_ccr = round(1 / $row->currency_conversion_rate,5);
                    }

                    if($popd->occurrence > 1)
                    {
                        $value_to_compare = round($row->currency_conversion_rate,5);
                    }
                    else
                    {
                        $value_to_compare = $new_ccr;
                    }

                    if($old_value_ccr != $value_to_compare)
                    {
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['po_id' => $po->po_id,'from_bulk' => 'yes','exchange_rate' => $row->currency_conversion_rate]);
                        app('App\Http\Controllers\Purchasing\PurchaseOrderController')->SavePoNote($request);
                    }
                }
            }

            $modified_pos = ProductReceivingImportTemp::where('user_id',$user_id)->where('group_id',$query[0]->group_id)->where('row_updated',1)->pluck('po_id')->toArray();

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

            app('App\Http\Controllers\Purchasing\PurchaseOrderController')->updateGroupViaPo($query[0]->po_id);

            ExportStatus::where('type','products_receiving_bulk_preview_confirm')->where('user_id',$user_id)->update(['status'=>0,'last_downloaded'=>date('Y-m-d'),'exception'=> null]);

            return response()->json(['msg'=>'File Saved']);

        } catch(\Exception $e) {
            $this->failed($e);
        }
        catch(MaxAttemptsExceededException $e) {
              $this->failed($e);
        }
    }
    public function failed( $exception)
    {
        ExportStatus::where('type','products_receiving_bulk_preview_confirm')->where('user_id',$this->user_id)->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException=new FailedJobException();
        $failedJobException->type="Products Receiving Temp Data Confirmation";
        $failedJobException->exception=$exception->getMessage();
        $failedJobException->save();
    }
    public function SavePoProductDiscount(Request $request)
    {
        // dd($request->all());
        $user = User::find($request->user_id);
        $po = PurchaseOrderDetail::with('PurchaseOrder','product.supplier_products')->where('id',$request->rowId)->where('po_id',$request->po_id)->first();
        $checkSameProduct = PurchaseOrderDetail::where('po_id',$po->po_id)->where('product_id',$po->product_id)->get();
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
        }

        return response()->json([
            'success'   => true, 
            // 'sub_total' => $grandCalculations['sub_total'], 
            // 'total_qty' => $grandCalculations['total_qty'],
            // 'vat_amout' => $grandCalculations['vat_amout'],
            // 'total_w_v' => $grandCalculations['total_w_v']
        ]);
    }

    public function SavePoProductQuantity(Request $request)
    {
        // dd($request->all());
        $po = PurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->po_id)->first();
        $field = null;
        $user = User::find($request->user_id);
        foreach($request->except('rowId','po_id','from_bulk','old_value') as $key => $value)
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
            if($po_totoal_change->status >= 14)
            {
                if($request->pogpd__id != null)
                {
                    $po_group_p_detail = PoGroupProductDetail::where('status',1)->find($request->pogpd__id);
                    if($po_group_p_detail->occurrence == 1)
                    {
                        $po_group_p_detail->quantity_inv = $updateRow->quantity;
                        $po_group_p_detail->save();
                    }

                    if($po_group_p_detail->occurrence > 1)
                    {
                        $all_ids = PurchaseOrder::where('po_group_id',$po_totoal_change->po_group_id)->where('supplier_id',$po_totoal_change->supplier_id)->pluck('id'); 

                        $all_record = PurchaseOrderDetail::whereIn('po_id',$all_ids)->where('product_id',$po_group_p_detail->product_id)->sum('quantity');

                        // $po_group_p_detail->quantity_inv -= $request->old_value;
                        // $po_group_p_detail->save();
                        $po_group_p_detail->quantity_inv = $all_record;
                        $po_group_p_detail->save();
                    }
                }
            }
        }

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
    public function UpdateUnitPrice(Request $request)
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

        }
        else
        {
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
    public function updateGrossWeight(Request $request)
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
}
