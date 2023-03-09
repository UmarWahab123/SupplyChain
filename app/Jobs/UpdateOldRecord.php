<?php

namespace App\Jobs;

use App\ExportStatus;
use App\FailedJobException;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\PoGroup;
use App\Models\Common\PoGroupDetail;
use App\Models\Common\PoGroupProductDetail;
use App\Models\Common\Product;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\StockManagementOut;
use App\Models\Common\SupplierProducts;
use App\Models\Common\Warehouse;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\Order\Order;
use App\ProductHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class UpdateOldRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $request;
    protected $user_id;
    protected $role_id;
    protected $all_pos;
    protected $product_id;
    protected $c_c_r;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,$user_id,$role_id,$all_pos,$product_id,$c_c_r)
    {
        $this->request=$data;
        $this->user_id=$user_id;
        $this->role_id=$role_id;
        $this->all_pos=$all_pos;
        $this->product_id=$product_id;
        $this->c_c_r=$c_c_r;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // try
        // {
        //     $request=$this->request;
        //     $user_id=$this->user_id;
        //     $role_id=$this->role_id;
        //     $st_id=$this->st_id;

        //     $product = Product::all();
        //     $warehouses = Warehouse::where('id',$st_id)->get();
        //     foreach ($warehouses as $warehouse) {
        //     foreach ($product as $prod) {

        //     $pids = PurchaseOrder::where('status',21)->whereHas('PoWarehouse',function($qq)use($warehouse){
        //         $qq->where('from_warehouse_id',$warehouse->id);
        //       })->pluck('id')->toArray();
        //       $pqty =  PurchaseOrderDetail::whereIn('po_id',$pids)->where('product_id',$prod->id)->sum('quantity');

        //       $warehouse_product = WarehouseProduct::where('product_id',$prod->id)->where('warehouse_id',$warehouse->id)->first();
        //       $stock_qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity:' 0';

        //       $ids =  Order::where('primary_status',2)->whereHas('order_products',function($qq) use($prod,$warehouse){
        //         $qq->where('product_id',$prod->id);
        //         $qq->where('from_warehouse_id',$warehouse->id);
        //       })->pluck('id')->toArray();

        //       $ids1 =  Order::where('primary_status',2)->whereHas('order_products',function($qq) use($prod,$warehouse){
        //         $qq->where('product_id',$prod->id);
        //         $qq->whereNull('from_warehouse_id');
        //       })->whereHas('user_created',function($query) use($warehouse){
        //           $query->where('warehouse_id',$warehouse->id);
        //         })
        //       ->pluck('id')->toArray();

        //       $ordered_qty0 =  OrderProduct::whereIn('order_id',$ids)->where('product_id',$prod->id)->sum('quantity');

        //       $ordered_qty1 =  OrderProduct::whereIn('order_id',$ids1)->where('product_id',$prod->id)->sum('quantity');

        //       $ordered_qty = $ordered_qty0 + $ordered_qty1;
        //       // $order_products=$stock_qty-($ordered_qty+$pqty);
        //       // dd($stock_qty,$ordered_qty,$pqty);
        //       $warehouse_product->reserved_quantity = number_format($ordered_qty,3,'.','');
        //       $warehouse_product->available_quantity = number_format($warehouse_product->current_quantity - $warehouse_product->reserved_quantity,3,'.','');
        //       $warehouse_product->save();

        //       }
        //     }

        //       $done = true;
        //       $filename = 'done';
        //       if($done)
        //         {
        //             ExportStatus::where('user_id',$user_id)->where('type','update_old_record')->update(['status'=>0,'last_downloaded'=>'28-12-2020','file_name'=>$filename]);
        //            return response()->json(['msg'=>'File Saved']);
        //         }
        // }
        // catch(Exception $e) {
        //     $this->failed($e);
        //     }
        //     catch(MaxAttemptsExceededException $e) {
        //         $this->failed($e);
        //     }

        //To update currency conversion rate po group

        try
        {
            $all_pos=$this->all_pos;
            $user_id=$this->user_id;
            $product_id=$this->product_id;
            $c_c_r=$this->c_c_r;

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
                                $po_group_product->total_unit_price_in_thb         =  $total_buying_price / $po_group_product->occurrence;

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
                        $supplier_product->currency_conversion_rate = 1/$supplier_conv_rate_thb;
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
                        $product_history->column_name = 'COGS Updated through from shipment - '.@$po_group->ref_id.' by updating currency conversion rate.';
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

                    $done = true;
                    $filename = 'done';
                    if($done)
                      {
                          ExportStatus::where('user_id',$user_id)->where('type','update_old_record')->update(['status'=>0,'last_downloaded'=>'28-12-2020','file_name'=>$filename]);
                         return response()->json(['msg'=>'File Saved']);
                      }
                }

        }
        catch(Exception $e) {
        $this->failed($e);
        }
        catch(MaxAttemptsExceededException $e) {
            $this->failed($e);
        }
    }

    public function failed($exception)
    {

        ExportStatus::where('type','update_old_record')->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException=new FailedJobException();
        $failedJobException->type="Update Old Data";
        $failedJobException->exception=$exception->getMessage();
        $failedJobException->save();

    }
}
