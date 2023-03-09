<?php

namespace App\Jobs;

use App\ExportStatus;
use App\FailedJobException;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Product;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\Warehouse;
use App\Models\Common\PoGroupProductDetail;
use App\Models\Common\StockManagementOut;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\StockOutHistory;
use App\Models\Common\Order\Order;
use App\Models\Common\PoGroup;
use App\Models\Common\SupplierProducts;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use App\ProductHistory;

class UpdateOldRecordReservedQuantity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 6000;
    protected $request;
    protected $user_id;
    protected $role_id;
    protected $st_id;
    protected $tries = 2;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,$user_id,$role_id,$st_id)
    {
        $this->request=$data;
        $this->user_id=$user_id;
        $this->role_id=$role_id;
        $this->st_id=$st_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try
        {
            $request=$this->request;
            $user_id=$this->user_id;
            $role_id=$this->role_id;
            $st_id=$this->st_id;
            //update old record in stock out histories table
            // $old_records = StockOutHistory::all();
            // foreach($old_records as $rec)
            // {
            //     $check_record = StockManagementOut::find($rec->stock_out_from_id);
            //     if($check_record)
            //     {
            //         $rec->total_cost = $rec->quantity * $check_record->cost;
            //         $rec->save();
            //     }
            // }
            // $filename = 'done';
            // ExportStatus::where('user_id',$user_id)->where('type','update_old_cq_rq')->update(['status'=>0,'last_downloaded'=>'28-12-2020','file_name'=>$filename]);
            //     return response()->json(['msg'=>'File Saved']);
            //end
            // $history = ProductHistory::where('column_name','LIKE','COGS Updated through%')->get();
            //     foreach ($history as $value) {
            //         $str = $value->column_name;
            //         $res = preg_replace('/[^0-9]/', '', $str);
            //         $po_group = PoGroup::where('ref_id',$res)->first();

            //         if($po_group)
            //         {
            //             $value->group_id = $po_group->id;
            //             $value->save();
            //         }
            //     }
            // updating po group product detail table po_id column starts
            // $all_groups = PoGroup::all();
            // foreach ($all_groups as $group) {
            //     // foreach ($group->po_group_product_details as $po_group_product) {
            //     //     $occurrence = $po_group_product->occurrence;
            //     //     if($occurrence == 1)
            //     //     {
            //     //         $id = $po_group_product->po_group_id;
            //     //         $pod = PurchaseOrderDetail::select('po_id','order_id')->where('product_id',$po_group_product->product_id)->whereHas('PurchaseOrder',function($q) use ($id,$po_group_product){
            //     //             $q->where('po_group_id',$id);
            //     //             $q->where('supplier_id',$po_group_product->supplier_id);
            //     //         })->get();
            //     //         if($pod->count() > 0)
            //     //         {
            //     //             $po = $pod[0]->PurchaseOrder;

            //     //             $po_group_product->po_id = $po->id;
            //     //             $po_group_product->pod_id = $pod[0]->id;
            //     //             $po_group_product->save();

            //     //             $order = Order::find($pod[0]->order_id);
            //     //             if($order != null)
            //     //             {
            //     //                 $po_group_product->order_id = $order->id;
            //     //                 $po_group_product->save();
            //     //             }
            //     //         }
            //     //     }

            //     //     $ccr = $po_group_product->po_group->purchase_orders()->where('supplier_id',$po_group_product->supplier_id)->pluck('id')->toArray();

            //     //     $buying_price = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'buying_price');
            //     //     // $po_group_product->unit_price_in_thb_with_vat =  $buying_price / $po_group_product->occurrence;
            //     //     if($po_group_product->occurrence > 1)
            //     //     {
            //     //         $po_group_product->unit_price_with_vat =  $buying_price / $po_group_product->occurrence;
            //     //     }
            //     //     else
            //     //     {
            //     //         $po_group_product->unit_price_with_vat =  $buying_price;
            //     //     }

            //     //     $buying_price_wo_vat = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'buying_price_wo_vat');
            //     //     // $po_group_product->unit_price_in_thb          =  $buying_price_wo_vat / $po_group_product->occurrence;
            //     //     if($po_group_product->occurrence > 1)
            //     //     {
            //     //         $po_group_product->unit_price          =  $buying_price_wo_vat / $po_group_product->occurrence;
            //     //     }
            //     //     else
            //     //     {
            //     //         $po_group_product->unit_price          =  $buying_price_wo_vat;
            //     //     }

            //     //     $total_buying_price = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'total_buying_price');
            //     //     $po_group_product->total_unit_price_with_vat    =  $total_buying_price;

            //     //     $total_buying_price_wo_vat = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'total_buying_price_wo_vat');
            //     //     $po_group_product->total_unit_price    =  $total_buying_price_wo_vat;


            //     //     /**/
            //     //     $buying_price = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'buying_price_in_thb');
            //     //     // $po_group_product->unit_price_in_thb_with_vat =  $buying_price / $po_group_product->occurrence;
            //     //     if($po_group_product->occurrence > 1)
            //     //     {
            //     //         $po_group_product->unit_price_in_thb_with_vat =  $buying_price / $po_group_product->occurrence;
            //     //     }
            //     //     else
            //     //     {
            //     //         $po_group_product->unit_price_in_thb_with_vat =  $buying_price;
            //     //     }

            //     //     $buying_price_wo_vat = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'buying_price_in_thb_wo_vat');
            //     //     // $po_group_product->unit_price_in_thb          =  $buying_price_wo_vat / $po_group_product->occurrence;
            //     //     if($po_group_product->occurrence > 1)
            //     //     {
            //     //         $po_group_product->unit_price_in_thb          =  $buying_price_wo_vat / $po_group_product->occurrence;
            //     //     }
            //     //     else
            //     //     {
            //     //         $po_group_product->unit_price_in_thb          =  $buying_price_wo_vat;
            //     //     }

            //     //     $total_buying_price = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'total_buying_price_in_thb');
            //     //     $po_group_product->total_unit_price_in_thb_with_vat    =  $total_buying_price;

            //     //     $total_buying_price_wo_vat = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'total_buying_price_in_thb_wo_vat');
            //     //     $po_group_product->total_unit_price_in_thb    =  $total_buying_price_wo_vat;

            //     //     $po_group_product->save();
            //     // }
            //     foreach ($group->po_group_product_details as $po_group_product) {
            //         $po_group_product->is_review = $po_group_product->po_group->is_review;
            //         $po_group_product->save();
            //     }

            // }
            // $groups = PoGroup::whereNull('is_cancel')->whereNull('po_group_total')->orderBy('id','desc')->get();
            // foreach($groups as $id)
            // {
            //   $group = PoGroup::find($id->id);
            //   $group->tax = round($group->tax,2);
            //   $group->save();
            //   $detail = PoGroupProductDetail::where('po_group_id',$id->id)->get();
            //   $total_import_tax_book = 0;
            //   foreach($detail as $d)
            //   {
            //     // $d->unit_price_in_thb = round($d->unit_price_in_thb,2);
            //     // $d->save();
            //     $d->import_tax_book_price = round(round(($d->import_tax_book / 100)*$d->unit_price_in_thb,2) * $d->quantity_inv,2);
            //     $d->save();
            //     $total_import_tax_book += $d->import_tax_book_price;
            //   }
            //   foreach($detail as $d)
            //   {
            //     if($total_import_tax_book > 0)
            //     {
            //       $d->weighted_percent = ($d->import_tax_book_price / $total_import_tax_book) * 100;
            //     }
            //     else
            //     {
            //       $d->weighted_percent = 0;
            //     }
            //     $d->save();
            //     $total_import_tax = round($group->tax * ($d->weighted_percent / 100) , 2);
            //     if($d->quantity_inv > 0)
            //     {
            //       $d->actual_tax_price = round($total_import_tax / $d->quantity_inv,2);
            //     }
            //     $d->save();
            //     if($d->unit_price_in_thb > 0)
            //     {
            //       $d->actual_tax_percent = round(($d->actual_tax_price / $d->unit_price_in_thb)*100,2);
            //     }
            //     $d->save();

            //     $check_history = ProductHistory::where('product_id',$d->product_id)->where(function($q){
            //       $q->where('column_name','Import Tax Actual')->orWhere('column_name','import_tax_actual');
            //     })->orderBy('id','desc')->first();

            //     if($check_history != null)
            //     {
            //       if($check_history->group_id == $d->po_group_id)
            //       {
            //         $c_h = ProductHistory::where('product_id',$d->product_id)->where('column_name','unit_import_tax')->where('id','>',$check_history->id)->first();
            //         if($c_h == null)
            //         {
            //           if($d->supplier_id != null)
            //                   {
            //                       $supplier_product                    = SupplierProducts::where('supplier_id',$d->supplier_id)->where('product_id',$d->product_id)->first();
            //                   }
            //                   else
            //                   {
            //                       $check_product = Product::find($d->product_id);
            //                       if($check_product)
            //                       {
            //                           $supplier_product = SupplierProducts::where('supplier_id',$check_product->supplier_id)->where('product_id',$check_product->id)->first();
            //                       }
            //                   }
            //                   $supplier_conv_rate_thb = @$p_g_pd->currency_conversion_rate != 0 ? $p_g_pd->currency_conversion_rate : 1 ;
            //                   $buying_price_in_thb    = $d->unit_price_in_thb;
            //                   $product = Product::find($d->product_id);
            //                   $supplier_product->unit_import_tax     = $d->actual_tax_price;
            //                   $supplier_product->import_tax_actual   = $d->actual_tax_percent;
            //                   $supplier_product->save();

            //                   // this is the price of after conversion for THB
            //                   $importTax              = $supplier_product->import_tax_actual;
            //                   $total_buying_price     = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

            //                   $total_buying_price     = ($supplier_product->freight)+($supplier_product->landing)+($supplier_product->extra_cost)+($supplier_product->extra_tax)+($total_buying_price);

            //                   $product->total_buy_unit_cost_price = $total_buying_price;
            //                   $product->t_b_u_c_p_of_supplier     = $total_buying_price * $supplier_conv_rate_thb;
            //                   $total_selling_price = $product->total_buy_unit_cost_price * $product->unit_conversion_rate;

            //                   $product->selling_price           = $total_selling_price;
            //                   $product->save();

            //                   $d->product_cost = $total_selling_price;
            //                   $d->save();

            //                   $po__ids = $group->po_group_detail != null ? $group->po_group_detail()->pluck('purchase_order_id')->toArray() : [];
            //                   $po_detail_products = PurchaseOrderDetail::where('product_id',$product->id)->whereNotNull('order_product_id')->whereIn('po_id',$po__ids)->get();
            //                   if($po_detail_products->count() > 0)
            //                   {
            //                       foreach ($po_detail_products as $pod) {
            //                           if($pod->order_product)
            //                           {
            //                               $pod->order_product->actual_cost = $product->selling_price;
            //                               $pod->order_product->save();
            //                           }
            //                       }
            //                   }

            //                   $stock_out = StockManagementOut::where('po_group_id',$d->po_group_id)->where('product_id',$d->product_id)->where('warehouse_id',$d->to_warehouse_id)->update(['cost' => $d->product_cost]);
            //         }
            //       }
            //     }
            //   }

            //   $group->po_group_total = 1;
            //   $group->save();
            // }
            // $filename = 'done';
            // ExportStatus::where('user_id',$user_id)->where('type','update_old_cq_rq')->update(['status'=>0,'last_downloaded'=>'28-12-2020','file_name'=>$filename]);
                // return response()->json(['msg'=>'File Saved']);
            // updating po group product detail table po_id column End

            $product = Product::all();
            $warehouses = Warehouse::where('id',$st_id)->get();
            foreach ($warehouses as $warehouse) {
            foreach ($product->chunk(300) as $prods) {

            foreach ($prods as $prod) {
            $pids = PurchaseOrder::where('status',21)->whereHas('PoWarehouse',function($qq)use($warehouse){
                $qq->where('from_warehouse_id',$warehouse->id);
              })->pluck('id')->toArray();
              $pqty =  PurchaseOrderDetail::whereIn('po_id',$pids)->where('product_id',$prod->id)->sum('quantity');

              $warehouse_product = WarehouseProduct::where('product_id',$prod->id)->where('warehouse_id',$warehouse->id)->first();
                  if($warehouse_product != null)
                  {
                  $stock_qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity:' 0';

                  $stck_out = StockManagementOut::select('quantity_out,warehouse_id')->where('product_id',$prod->id)->where('warehouse_id',$warehouse->id)->sum('quantity_out');
                  $stck_in = StockManagementOut::select('warehouse_id,quantity_in')->where('product_id',$prod->id)->where('warehouse_id',$warehouse->id)->sum('quantity_in');

                  $current_stock_all = round($stck_in,3) - abs(round($stck_out,3));
                  $warehouse_product->current_quantity = round($current_stock_all,3);
                  $warehouse_product->save();

                  $ids =  Order::where('primary_status',2)->whereHas('order_products',function($qq) use($prod,$warehouse){
                    $qq->where('product_id',$prod->id);
                    $qq->where('from_warehouse_id',$warehouse->id);
                  })->whereNull('ecommerce_order')->pluck('id')->toArray();

                  $ids1 =  Order::where('primary_status',2)->whereHas('order_products',function($qq) use($prod,$warehouse){
                    $qq->where('product_id',$prod->id);
                    $qq->whereNull('from_warehouse_id');
                  })->whereHas('user_created',function($query) use($warehouse){
                      $query->where('warehouse_id',$warehouse->id);
                    })->whereNull('ecommerce_order')
                  ->pluck('id')->toArray();

                  $ordered_qty0 =  OrderProduct::whereIn('order_id',$ids)->where('product_id',$prod->id)->sum('quantity');

                  $ordered_qty1 =  OrderProduct::whereIn('order_id',$ids1)->where('product_id',$prod->id)->sum('quantity');

                  $ordered_qty = $ordered_qty0 + $ordered_qty1 + $pqty;

                  //To Update ECOM orders
                  $ecom_ids =  Order::where('primary_status',2)->whereHas('order_products',function($qq) use($prod,$warehouse){
                    $qq->where('product_id',$prod->id);
                    $qq->where('from_warehouse_id',$warehouse->id);
                  })->where('ecommerce_order',1)->pluck('id')->toArray();

                  $ecom_ids1 =  Order::where('primary_status',2)->whereHas('order_products',function($qq) use($prod,$warehouse){
                    $qq->where('product_id',$prod->id);
                    $qq->whereNull('from_warehouse_id');
                  })->whereHas('user_created',function($query) use($warehouse){
                      $query->where('warehouse_id',$warehouse->id);
                    })->where('ecommerce_order',1)
                  ->pluck('id')->toArray();

                  $ecom_ordered_qty0 =  OrderProduct::whereIn('order_id',$ecom_ids)->where('product_id',$prod->id)->sum('quantity');

                  $ecom_ordered_qty1 =  OrderProduct::whereIn('order_id',$ecom_ids1)->where('product_id',$prod->id)->sum('quantity');

                  $ecom_ordered_qty = $ecom_ordered_qty0 + $ecom_ordered_qty1;
                  $warehouse_product->reserved_quantity = number_format($ordered_qty,3,'.','');
                  $warehouse_product->ecommerce_reserved_quantity = number_format($ecom_ordered_qty,3,'.','');
                  $warehouse_product->available_quantity = number_format($warehouse_product->current_quantity - ($warehouse_product->reserved_quantity + $warehouse_product->ecommerce_reserved_quantity),3,'.','');
                  $warehouse_product->save();
              }
                }

              }
            }

            // $orders = Order::whereIn('primary_status',[2,3])->get();
            //     $ref = '';
            //     foreach ($orders->chunk(500) as $orderss) {
            //       foreach ($orderss as $ord) {
            //         $count = OrderProduct::select('id','order_id','is_billed','user_warehouse_id')->where('order_id',$ord->id)->where('is_billed','Product')->whereNotNull('user_warehouse_id')->first();

            //         // if($count->count() > 1)
            //         // {
            //         //   $ref .= $ord->id.'--';
            //         // }
            //         if($count != null)
            //         {
            //           $ord->from_warehouse_id = $count->user_warehouse_id;
            //           $ord->save();
            //         }

            //       }
            //     }

          // $groups = PoGroup::all();
          // foreach ($groups as $group) {
          //   foreach ($group->po_group_product_details as $detail) {
          //     if($detail->quantity_inv != null && $detail->total_gross_weight != null)
          //     {
          //       // $detail->total_gross_weight = $detail->unit_gross_weight * $detail->quantity_inv;
          //       if($detail->quantity_inv != 0 && $detail->total_gross_weight != null)
          //       {
          //         $detail->unit_gross_weight = $detail->total_gross_weight / $detail->quantity_inv;
          //         $detail->save();
          //       }
          //     }
          //   }
          // }

        //   $groups = PoGroup::whereNull('from_warehouse_id')->get();
        //   foreach ($groups as $group) {
        //     $all_record = PoGroupProductDetail::where('status',1)->where('po_group_id',$group->id)->get();
        //     foreach ($all_record as $record) {
        //         $all_pos = PurchaseOrder::where('supplier_id',$record->supplier_id)->where('po_group_id',$record->po_group_id)->pluck('id')->toArray();

        //         $check_prod = PurchaseOrderDetail::whereIn('po_id',$all_pos)->where('product_id',$record->product_id)->get();
        //         foreach ($check_prod as $pd) {
        //             $pd->pod_total_gross_weight = $pd->quantity * $pd->pod_gross_weight;
        //             $pd->save();
        //         }
        //         if($check_prod->count() > 0)
        //         {
        //             $unit_gross_weight = $check_prod->sum('pod_gross_weight') / $check_prod->count();

        //             if($unit_gross_weight)
        //             {
        //                 $record->unit_gross_weight = $unit_gross_weight;
        //                 $record->save();
        //                 $record->total_gross_weight = $record->quantity_inv * $record->unit_gross_weight;
        //                 $record->save();
        //             }
        //         }
        //     }
        //     $group->po_group_total_gross_weight = $group->po_group_product_details()->sum('total_gross_weight');
        // }


            $done = true;
            $filename = 'done';
            if($done)
            {
                ExportStatus::where('user_id',$user_id)->where('type','update_old_cq_rq')->update(['status'=>0,'last_downloaded'=>'28-12-2020','file_name'=>$filename]);
                return response()->json(['msg'=>'File Saved']);
            }
        }
        // try
        // {
        //     $request=$this->request;
        //     $user_id=$this->user_id;
        //     $role_id=$this->role_id;
        //     $st_id=$this->st_id;

        //     $productss = OrderProduct::with('get_order','get_order.user_created')->select('id','from_warehouse_id','status','user_warehouse_id','order_id')->whereNull('user_warehouse_id')->where('status','>','6')->chunk(500, function($products){
        //             foreach ($products as $prod)
        //             {
        //                 if($prod->from_warehouse_id != NULL)
        //                 {
        //                   $prod->user_warehouse_id = $prod->from_warehouse_id;
        //                 }
        //                 else
        //                 {
        //                   $prod->user_warehouse_id = @$prod->get_order->user_created->warehouse_id;
        //                 }
        //                 $prod->save();
        //             }
        //     });

        //     $done = true;
        //     $filename = 'done';
        //     if($done)
        //     {
        //       ExportStatus::where('user_id',$user_id)->where('type','update_old_cq_rq')->update(['status'=>0,'last_downloaded'=>'28-12-2020','file_name'=>$filename]);
        //       return response()->json(['msg'=>'File Saved']);
        //     }
        // }
        catch(Exception $e) {
            $this->failed($e);
            }
            catch(MaxAttemptsExceededException $e) {
                $this->failed($e);
            }
    }

    public function failed($exception)
    {

        ExportStatus::where('type','update_old_cq_rq')->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException=new FailedJobException();
        $failedJobException->type="Update Old Data";
        $failedJobException->exception=$exception->getMessage();
        $failedJobException->save();

    }
}
