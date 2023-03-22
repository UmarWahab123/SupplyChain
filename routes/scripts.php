<?php
use App\CustomerSecondaryUser;
use App\ExportStatus;
use App\Jobs\ScriptHandlerJob;
use App\Jobs\UpdateOldRecord;
use App\Jobs\UpdateOldRecordReservedQuantity;
use App\Jobs\UpdateStockCard;
use App\Models\Common\Configuration;
use App\Models\Common\CustomerCategory;
use App\Models\Common\CustomerTypeCategoryMargin;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\OrderHistory;
use App\Models\Common\Order\CustomerBillingDetail;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Order\OrderStatusHistory;
use App\Models\Common\PoGroup;
use App\Models\Common\PoGroupDetail;
use App\Models\Common\PoGroupProductDetail;
use App\Models\Common\Product;
use App\Models\Common\ProductFixedPrice;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\State;
use App\Models\Common\StockManagementIn;
use App\Models\Common\StockManagementOut;
use App\Models\Common\SupplierProducts;
use App\Models\Common\TempCustomers;
use App\Models\Common\Warehouse;
use App\Models\Common\WarehouseProduct;
use App\Models\Sales\Customer;
use App\ProductHistory;
use App\TransferDocumentReservedQuantity;
use App\User;
use Illuminate\Http\Request;

/***************** To update total extra cost and total extra tax *******************/
Route::get('/update_total_extra_tax_cost/{id}',function($id){
	$po_group = PoGroup::find($id);
	$po_groups_product_detail = PoGroupProductDetail::where('status',1)->where('po_group_id',$id)->get();
	foreach($po_groups_product_detail as $po_detail)
	{
		// if($po_detail->occurrence > 1)
		// {
		// 	$all_ids = PurchaseOrder::where('po_group_id',$po_detail->po_group_id)->where('supplier_id',$po_detail->supplier_id)->pluck('id');
	 //        $all_record = PurchaseOrderDetail::whereIn('po_id',$all_ids)->where('product_id',$po_detail->product_id)->with('product','PurchaseOrder','getOrder','product.units','getOrder.user','getOrder.customer')->get();

	 //        if($all_record->count() > 0)
	 //        {
	 //            //to update total extra tax column in po group product detail
	 //            $po_detail->total_extra_cost = $all_record->sum('total_extra_cost');
	 //            $po_detail->total_extra_tax = $all_record->sum('total_extra_tax');
	 //            $po_detail->save();
	 //        }
		// }

		if($po_detail->occurrence >= 1)
		{
			$all_ids = PurchaseOrder::where('po_group_id',$po_detail->po_group_id)->where('supplier_id',$po_detail->supplier_id)->pluck('id');
	        $all_record = PurchaseOrderDetail::whereIn('po_id',$all_ids)->where('product_id',$po_detail->product_id)->with('product','PurchaseOrder','getOrder','product.units','getOrder.user','getOrder.customer')->get();

	        $po_detail->occurrence = $all_record->count();
	        $po_detail->quantity_inv = $all_record->sum('quantity');
	        // $po_detail->quantity_ordered = $all_record->whereHas('order_product',function($z){
	        // 	$z->sum('quantity');
	        // });
	        $qty_order = null;
	        $cal_ccr_value = null;
	        foreach($all_record as $qty_detail)
	        {
	        	if($qty_detail->order_product)
	        	{
	        		$qty_order += $qty_detail->order_product->quantity;
	        	}

	        	if($qty_detail->currency_conversion_rate != 0)
	        	{
	        		$cal_ccr_value += 1 / $qty_detail->currency_conversion_rate;
	        	}
	        }
	        $po_detail->currency_conversion_rate = $cal_ccr_value / $all_record->count();
	        $po_detail->quantity_ordered = $qty_order;
	        $po_detail->unit_gross_weight = $all_record->sum('pod_gross_weight');
	        $po_detail->total_gross_weight = $all_record->sum('pod_total_gross_weight');
	        $po_detail->unit_extra_cost = $all_record->sum('unit_extra_cost');
	        $po_detail->total_extra_cost = $all_record->sum('total_extra_cost');
	        $po_detail->unit_extra_tax = $all_record->sum('unit_extra_tax');
	        $po_detail->total_extra_tax = $all_record->sum('total_extra_tax');
	        $po_detail->unit_price = $all_record->sum('pod_unit_price') / $all_record->count();
	        $po_detail->unit_price_in_thb = $all_record->sum('unit_price_in_thb') / $all_record->count();
	        if($po_detail->occurrence > $all_record->count())
	        {
	        	$ccr = null;
	        	foreach($all_record as $po_dt)
	        	{
	        		if($po_dt->currency_conversion_rate != null && $po_dt->currency_conversion_rate != 0)
	        		{
	        			$ccr += 1 / $po_dt->currency_conversion_rate;
	        		}
	        	}
	        	if($all_record->count() > 0)
	        	{
	        		$final_ccr = $ccr / $all_record->count();
	        	}
	        	else
	        	{
	        		$final_ccr = 0;
	        	}

	        	$po_detail->currency_conversion_rate = $final_ccr;
	        }
	        $po_detail->save();

	        if($po_detail->supplier_id != null)
            {
                $supplier_product                    = SupplierProducts::where('supplier_id',$po_detail->supplier_id)->where('product_id',$po_detail->product_id)->first();
            }
            else
            {
                $check_product = Product::find($po_detail->product_id);
                if($check_product)
                {
                    $supplier_product = SupplierProducts::where('supplier_id',$check_product->supplier_id)->where('product_id',$check_product->id)->first();
                }
            }
            $supplier_conv_rate_thb = @$po_detail->currency_conversion_rate != 0 ? $po_detail->currency_conversion_rate : 1 ;
            $buying_price    = $po_detail->unit_price;

            $supplier_product->buying_price = $buying_price;
            if($po_detail->occurrence > 1)
            {
            	$ccr_val = $po_detail->currency_conversion_rate;
            }
            else
            {
            	if($po_detail->currency_conversion_rate != 0)
            	{
            		$ccr_val  = 1 / $po_detail->currency_conversion_rate;
            	}
            	else
            	{
            		$ccr_val = 1;
            	}
            }
            $supplier_product->buying_price_in_thb = $buying_price * $ccr_val;
            $supplier_product->save();

		}

	}
	return 'Group updated !!!';
});
/************************* To find Final book percent of a group *****************************/
Route::get('/update_actual_tax_percent/{id}',function($id){

		$po_group = PoGroup::find($id);
		$po_groups_product_detail = PoGroupProductDetail::where('status',1)->where('po_group_id',$id)->get();
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
	     return 'final book percent is '.$final_book_percent;
		foreach ($po_groups_product_detail as $group_detail) {
						$group_tax = $po_group->tax;

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
                else if($group_tax != 0)
                {
                    $all_pgpd = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group->id)->count();

                    $total_import_tax = $group_detail->po_group->po_group_import_tax_book;
                    $po_group_import_tax_book = $group_detail->po_group->total_import_tax_book_percent;
                    $total_buying_price_in_thb = $group_detail->po_group->total_buying_price_in_thb;

                    $import_tax = $group_detail->import_tax_book;
                    $total_price = $group_detail->total_unit_price_in_thb;
                    $book_tax = (($import_tax/100)*$total_price);


                    $check_book_tax = (($po_group_import_tax_book*$total_buying_price_in_thb)/100);


                    if($check_book_tax != 0)
                    {
                        $book_tax = round($book_tax,2);
                    }
                    else
                    {
                        $book_tax = (1/$all_pgpd)* $group_detail->total_unit_price_in_thb;
                        $book_tax = round($book_tax,2);
                    }
                    if($total_import_tax != 0)
                    {
                        $weighted = ($book_tax/$total_import_tax);
                    }
                    else
                    {
                        $weighted = 0;
                    }
                    $tax = $group_detail->po_group->tax;
                    $actual_tax_val = number_format(($weighted*$tax),2,'.','');
                    if($group_detail->quantity_inv != 0 && $group_detail->quantity_inv !== 0)
                    {
                    	$group_detail->actual_tax_price = number_format(($weighted*$tax) / $group_detail->quantity_inv,2,'.','');
                    }
                    else
                    {
                    	$group_detail->actual_tax_price = number_format(($weighted*$tax),2,'.','');
                    }
                    $group_detail->save();

                    if($group_detail->total_unit_price_in_thb != 0)
                    {
                        $group_detail->actual_tax_percent = ($group_detail->actual_tax_price / $group_detail->total_unit_price_in_thb)*100;
                    }
                    $p_g_pd = $group_detail;
                    $product = Product::find($p_g_pd->product_id);

                    if($p_g_pd->supplier_id != null)
	                {
	                    $supplier_product                    = SupplierProducts::where('supplier_id',$p_g_pd->supplier_id)->where('product_id',$p_g_pd->product_id)->first();
	                }
	                else
	                {
	                    $check_product = Product::find($p_g_pd->product_id);
	                    if($check_product)
	                    {
	                        $supplier_product = SupplierProducts::where('supplier_id',$check_product->supplier_id)->where('product_id',$check_product->id)->first();
	                    }
	                }

	                if($supplier_product->unit_import_tax > $product->total_buy_unit_cost_price)
	                {
	                	$supplier_product->unit_import_tax = $group_detail->actual_tax_price;
	                	$supplier_product->save();
	                }
                }

                // tO UPDATE WEIGHT
                // $tax = $po_group->tax;
                // $total_import_tax = $po_group->po_group_import_tax_book;
                // $import_tax = $group_detail->import_tax_book;
                // $actual_tax_percent = ($tax/$total_import_tax*$import_tax);
                // $group_detail->actual_tax_percent = $actual_tax_percent;
		}
     return 'Done';
});
	/*************Finding empty groups**************************/
/************************* To find Final book percent of a group *****************************/
Route::get('/find_final_book_percent/{id}',function($id){
	$all_record = PoGroupProductDetail::where('status',1)->where('po_group_id',$id);
    $all_record = $all_record->with('product','po_group','get_supplier','product.units','product.sellingUnits')->get();
     $final_book_percent = 0;
       $final_vat_actual_percent = 0;
     foreach ($all_record as $value)
     {
         if($value->import_tax_book != null && $value->import_tax_book != 0)
         {
             $final_book_percent = $final_book_percent +(($value->import_tax_book/100) * $value->total_unit_price_in_thb);
         }

         if($value->pogpd_vat_actual != null && $value->pogpd_vat_actual != 0)
         {
             $final_vat_actual_percent = $final_vat_actual_percent +(($value->pogpd_vat_actual/100) * $value->total_unit_price_in_thb);
         }
     }

     $all_record1 = PoGroupProductDetail::where('status',1)->where('po_group_id',$id)->where('quantity_inv','!=',0);
    $all_record1 = $all_record1->with('product','po_group','get_supplier','product.units','product.sellingUnits')->get();
     $final_book_percent1 = 0;
       $final_vat_actual_percent1 = 0;
     foreach ($all_record1 as $value)
     {
         if($value->import_tax_book != null && $value->import_tax_book != 0)
         {
             $final_book_percent1 = $final_book_percent1 +(($value->import_tax_book/100) * $value->total_unit_price_in_thb);
         }

         if($value->pogpd_vat_actual != null && $value->pogpd_vat_actual != 0)
         {
             $final_vat_actual_percent1 = $final_vat_actual_percent1 +(($value->pogpd_vat_actual/100) * $value->total_unit_price_in_thb);
         }
     }

     return 'Final book percent is '.$final_book_percent.' Final vat acutal percent is '.$final_vat_actual_percent.' Final book percent without 0 is '.$final_book_percent1.' Final vat acutal percent without 0 is '.$final_vat_actual_percent1.' total records '.$all_record->count().' total records '.$all_record1->count();
});
	/*************Finding empty groups**************************/
	Route::get('/to_update_cogs_of_ecom_orders',function(){

		$orders = Order::where('ecommerce_order',1)->get();

		foreach ($orders as $order) {
			$order_products = OrderProduct::where('order_id',$order->id)->get();
			foreach ($order_products as $op) {
				$product = Product::find($op->product_id);
				if($product->selling_unit_conversion_rate != NULL && $product->selling_unit_conversion_rate != '')
				{
					$ecom_cogs = $product->selling_unit_conversion_rate * $product->selling_price;
				}
				else
				{
					$ecom_cogs = $product->selling_price;
				}

				$op->locked_actual_cost = number_format($ecom_cogs,2,'.','');
				$op->actual_cost = number_format($ecom_cogs,2,'.','');
				$op->save();
			}
		}
		return 'Cogs of all ecom orders updated successfully';
	});

	/*************Finding empty groups**************************/
	Route::get('/get_empty_groups',function(){
		$po_groups = PoGroup::whereNull('is_cancel')->get();
		$html = '';
		foreach ($po_groups as $group) {
		if($group->po_group_detail->count() < 1)
		{
			$group->is_cancel = 2;
			$group->save();
			$html .= $group->id.' - ';
		}
		}

		return $html.' Got Cancelled';
	});

	/********Script for deleting duplicate entries from stock out table ****************/
	Route::get('/handle_duplicate_entries/{id}',function(Request $request,$id)
	{
		$get_duplicate_entries = StockManagementOut::where('po_group_id',$id)->whereNull('quantity_out')->groupBy('product_id')->get();
		// dd($get_duplicate_entries);
		$html = '';
		foreach ($get_duplicate_entries as $record) {
			$check_total_entries = StockManagementOut::where('po_group_id',$id)->whereNull('quantity_out')->where('product_id',$record->product_id)->get();
			if($check_total_entries->count() == 2)
			{
				$find_duplicate = StockManagementOut::where('po_group_id',$id)->where('product_id',$record->product_id)->where('available_stock',$record->quantity_in)->first();
			// dd($find_duplicate);
				if($find_duplicate != null)
				{
					$warehouse_product = WarehouseProduct::where('product_id',$record->product_id)->where('warehouse_id',$record->warehouse_id)->first();
					$warehouse_product->current_quantity -= $find_duplicate->quantity_in;
					$warehouse_product->save();
					$warehouse_product->available_quantity = $warehouse_product->current_quantity - ($warehouse_product->reserved_quantity + $warehouse_product->ecommerce_reserved_quantity);
					$warehouse_product->save();
					$find_duplicate->delete();
				}
			}
			else if($check_total_entries->count() > 2)
			{
				$html .= $record->product_id.' ';
			}
		}
		return $html;
	});
	/********************  script for orders converted_to_invoice_on column  **********************/

	Route::get('/update_converted_to_invoice_on',function(){
		$orders = App\Models\Common\Order\Order::where('primary_status',3)->whereNull('converted_to_invoice_on')->get();
		// dd($orders);

		foreach ($orders as $order) {
			$query = App\Models\Common\Order\OrderStatusHistory::where('new_status','Invoice')->where('order_id',$order->id)->first();
		    $order->converted_to_invoice_on = @$query->created_at;
		    $order->save();
		}
		return '<h1> update_converted_to_invoice_on_forInvoice  updated successfully! </h1>';

	});

	Route::get('/update_unit_price_with_discount',function(){
		$products = App\Models\Common\Order\OrderProduct::whereNull('unit_price_with_discount')->whereNotNull('discount')->get();
		// dd($products);

		foreach ($products as $prod) {
			$val = $prod->unit_price * ((100 - $prod->discount)/100);
		    $prod->unit_price_with_discount = number_format($val,3,'.','');
		    $prod->save();
		}
		return '<h1> Total '.$products->count().' Records  updated successfully! </h1>';

	});

	Route::get('/update_pos/{id}',function($id)
	{
		$allPosIds = PoGroupDetail::select('purchase_order_id')->where('po_group_id',$id)->pluck('purchase_order_id')->toArray();
		$pooos = PurchaseOrder::whereIn('id',$allPosIds)->get();

		foreach ($pooos as $pos)
		{
			$var = $pos->PurchaseOrderDetail->sum('pod_total_gross_weight');
			$pos->total_gross_weight = $var;
			$pos->save();
		}
		return '<h1> update_pos updated successfully! </h1>';

	});

	Route::get('/update_converted_to_invoice_on_for_quotation',function(){
		$orders = App\Models\Common\order\Order::where('primary_status',1)->whereNull('converted_to_invoice_on')->get();
		// dd($orders);

		foreach ($orders as $order) {
		    $order->converted_to_invoice_on = @$order->created_at;
		    $order->save();
		}
		return '<h1> Converted_to_invoice_on_for_quotation  updated successfully! </h1>';

	});

	Route::get('/update_converted_to_invoice_on_for_draft_invoice',function(){
		$orders = App\Models\Common\order\Order::where('primary_status',2)->whereNull('converted_to_invoice_on')->get();
		// dd($orders);

		foreach ($orders as $order) {
			$query = App\Models\Common\Order\OrderStatusHistory::where(function($q){
				$q->where('new_status','DI(Waiting To Pick)')->orWhere('new_status','DI(Waiting Gen PO)');
			})->where('order_id',$order->id)->first();
		    $order->converted_to_invoice_on = @$query->created_at;
		    $order->save();
		}
		return '<h1> Converted_to_invoice_on_for_draft_invoice  updated successfully! </h1>';

	});



	Route::get('/update_Rsv',function(){

		$orders = App\Models\Common\Order\Order::where('primary_status',2)->get();
		foreach ($orders as $order) {
		$order_products = App\Models\Common\Order\OrderProduct::where('order_id',$order->id)->whereNotNull('product_id')->get();
			foreach ($order_products as $op) {
				$query = App\Models\Common\WarehouseProduct::where('product_id',$op->product_id)->where('warehouse_id',$order->customer->user->warehouse_id)->first();
			    $query->reserved_quantity += @$op->quantity;
			    $query->save();
			}
		}
		return '<h1> Reserved Stock updated successfully! </h1>';
	});

	Route::get('/update_occurence',function(){

		$po_groups =  App\Models\Common\PoGroup::all();
		foreach ($po_groups as $po_group) {
			$total_import_tax_book_percent = null;
		$pgpd =  App\Models\Common\PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group->id)->get();
		foreach ($pgpd as $item) {
			$purchase_orders_ids =  App\Models\Common\PurchaseOrders\PurchaseOrder::where('po_group_id',$item->po_group_id)->where('supplier_id',$item->supplier_id)->pluck('id')->toArray();
	        $occurrence = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::whereIn('po_id',$purchase_orders_ids)->where('product_id',$item->product_id)->count();
            $item->occurrence = $occurrence;
            $item->save();
            $total_import_tax_book_percent += $item->import_tax_book;
		}
		$po_group->total_import_tax_book_percent = $total_import_tax_book_percent;
		$po_group->save();
		}
		return '<h1> Occurenece updated successfully! </h1>';
	});

	Route::get('/update_order_numbers',function(){

		$orders = App\Models\Common\Order\Order::where('primary_status','!=',1)->where('primary_status','!=',2)->get();
		foreach ($orders as $order) {
			$order->in_status_prefix = $order->status_prefix;
			$order->in_ref_prefix    = $order->ref_prefix;
			$order->in_ref_id        = $order->ref_id;
			$order->save();
		}
		return '<h1> Orders Numbers updated successfully! </h1>';
	});

	Route::get('/empty_draft_no',function(){

		$orders = App\Models\Common\Order\Order::where('primary_status',3)->get();
		foreach ($orders as $order) {
			if($order->status_prefix != null)
			{
				$order->status_prefix = null;
				$order->ref_prefix    = null;
				$order->ref_id        = null;
				$order->save();
			}
		}
		return '<h1> Orders Numbers updated successfully! </h1>';
	});

 Route::get('/check_stock_count_problem', function () {
    return view('stock');
});

 Route::get('/set_transaction_values',function(){
		$orders = App\Models\Common\order\Order::where('status',24)->get();
		// dd($orders->count());
		$vat_non_vat_orders = [];
		$orders_all = [];
		$split = [];
		$split2 = [];
		$total_records = 0;
		foreach ($orders as $order) {
		// $transaction = App\OrderTransaction::where('order_id',$order->id)->first();

		 $vat_total = $order->order_products != null ? $order->getOrderTotalVat($order->id,0) : 0;
         $vat_amount = $order->order_products != null ? $order->getOrderTotalVat($order->id,1) : 0;
         $total_vat = (floatval(preg_replace('/[^\d.]/', '', $vat_total)) + floatval(preg_replace('/[^\d.]/', '', $vat_amount)));

         $vat_total1 = $order->order_products != null ? $order->getOrderTotalVat($order->id,2) : 0;

         $total_non_vat = (floatval(preg_replace('/[^\d.]/', '', $vat_total1)));

         $order->vat_total_paid  = @$total_vat;
         $order->non_vat_total_paid  = @$total_non_vat;
         $order->save();
         @$total_records++;
      //    if(@$total_vat > 0 && @$total_non_vat > 0)
      //    {
      //    	$transaction = App\OrderTransaction::where('order_id',$order->id)->get();
      //    	if($transaction->count() > 1 && $transaction->count() < 3)
      //    	{
      //    		array_push($split2, $order->id);
      //    	}
      //    	else
      //    	{
      //    		$transaction = App\OrderTransaction::where('order_id',$order->id)->first();
      //    		$transaction->vat_total_paid = $total_vat;
      //    		$transaction->non_vat_total_paid = $total_non_vat;
      //    		$transaction->save();
      //    		array_push($vat_non_vat_orders, $order->id);
      //    		$total_records++;
      //    	}

      //    }
      //    else
      //    {
      //    	$transaction = App\OrderTransaction::where('order_id',$order->id)->get();
      //    		array_push($split, $order->id);
      //    		foreach ($transaction as $tran) {
      //    			if($total_vat > 0)
      //    			{
      //    				$tran->vat_total_paid = $tran->total_received;
      //    			}
      //    			else
      //    			{
      //    				$tran->non_vat_total_paid = $tran->total_received;
      //    			}
      //    			$tran->save();
      //    		}
      //    		$total_records++;
      //    	// array_push($orders_all, $order->id);

      //    // $transaction->vat_total_paid = $total_vat;
      //    // $transaction->non_vat_total_paid = $total_non_vat;
      //    // $transaction->save();
     	// }
     }
		return 'Total '.@$total_records.' Records are updated successfully';

	});

 Route::get('/check_half_paid_invoices',function(){
		$orders = App\Models\Common\order\Order::where('status',11)->where('total_paid','>','0')->get();
		$orders_ids = [];
		foreach ($orders as $order) {
			array_push($orders_ids, $order->id);
		}
		return $orders_ids;

	});


 Route::get('/fill_actual_cost',function(){
	$order_products = OrderProduct::where('is_billed','Product')->whereNull('actual_cost')->get();
	foreach ($order_products as $op) {
		$op->actual_cost = $op->product->selling_price;
		$op->save();
	}
	return 'Total Order Products are updated successfully';

});

 Route::get('/update_product_cost_column',function(){
		$po_groups = PoGroup::where('is_review',1)->whereNull('from_warehouse_id')->get();
		$product_obj = new Product;
		foreach ($po_groups as $group) {
			foreach ($group->po_group_product_details as $p_g_p_d) {
				if($p_g_p_d->quantity_inv != null && $p_g_p_d->quantity_inv != 0)
				{
					$product = $p_g_p_d->product;
					$purchasing_price = $product->supplier_products->where('supplier_id',$product->supplier_id)->first();
					$cost = $product_obj->getProductSellingPrice($p_g_p_d->product,$p_g_p_d->unit_price_in_thb,$p_g_p_d->total_extra_cost/$p_g_p_d->quantity_inv,$p_g_p_d->freight,$p_g_p_d->landing,$p_g_p_d->actual_tax_percent);
					echo $cost.' <br> ';
					$p_g_p_d->product_cost = $cost;
					$p_g_p_d->save();
				}
			}
		}
		return 'Success!';

	});

 Route::get('/update_stock_out_cost_column',function(){
		$po_groups = StockManagementOut::whereNotNull('quantity_in')->get();

		foreach ($po_groups as $group) {
			$price = $group->get_product->selling_price;
			$group->cost = $price != 0 ? $price : NULL;
			$group->save();
		}

		$in_complete_po_groups = StockManagementOut::whereNotNull('quantity_in')->whereHas('get_po_group',function($q){
			$q->where('is_review',0);
		})->get();
		foreach ($in_complete_po_groups as $in_group) {
			$in_group->cost = NULL;
			$in_group->save();
		}
		return 'Success!';

	});

 Route::get('/update_order_product_type',function(){
		$order_products = OrderProduct::whereNotNull('product_id')->whereNull('type_id')->get();

		$i = 0;
		foreach ($order_products as $product) {
			$product->type_id = $product->product->type_id;
			$product->save();
			$i++;
		}
		return '<h1> Total of '.$i.' order products updated</h1>';

	});


  Route::get('/update_order_user_id',function(){
		$order_products = Order::all();

		$i = 0;
		foreach ($order_products as $order) {
			if(@$order->customer !== null)
			{
			$order->user_id = @$order->customer->primary_sale_person->id;
			$order->save();
			}
			$i++;
		}
		return '<h1> Total of '.$i.' orders updated</h1>';
	});

  Route::get('/update_product_system_code',function(){
		$products = Product::whereNull('system_code')->get();

		$i = 0;
		foreach ($products as $product) {
			$product->system_code = $product->refrence_code;
			$product->save();
			$i++;
		}
		return '<h1> Total of '.$i.' products updated</h1>';

	});

  Route::get('/get_order_products',function(){
		$pods = PurchaseOrderDetail::whereNotNull('order_product_id')->get();
		$html = 'Po Id ---------- Order Id<br>';
		foreach ($pods as $pod) {
			$op = OrderProduct::where('id',$pod->order_product_id)->first();
			if($op == null)
			{
				$html .= $pod->po_id.' ------------ '.$pod->order_id.'<br>';
			}
		}
		return $html;

	});

  Route::get('/update_group_product_detail_cost',function(){
		// $orders = PoGroupProductDetail::where('status',1)->leftJoin('po_groups','po_groups.id','=','po_group_product_details.po_group_id')->whereNotNull('po_group_product_details.supplier_id')->where('po_groups.is_review',1)->where('po_group_product_details.quantity_inv','!=',0)->where('po_group_product_details.is_cost_update',0)->with('product')->get();

		$orders = PoGroupProductDetail::where('status',1)->where('quantity_inv','!=',0)->whereIn('is_cost_update',[0,1])->whereNotNull('supplier_id')->with('product')->get();
		// dd($orders->count());
		$i = 0;
		foreach ($orders as $order) {
			// dd($order);
			$unit_price_in_thb = $order->unit_price_in_thb;

			if($order->actual_tax_percent != null && $order->freight != null && $order->landing != null)
			{
				$calculations = (($order->actual_tax_percent/100) * $unit_price_in_thb) + $unit_price_in_thb;

				$calculations = ($calculations)+($order->freight)+($order->landing)+($order->total_extra_cost/$order->quantity_inv)+($order->total_extra_tax/$order->quantity_inv);

				$final_cogs    = $calculations * $order->product->unit_conversion_rate;

				$order->product_cost = $final_cogs;
				$order->is_cost_update = 2;
				$order->save();
			}
			echo $i.'<br>';
			$i++;

		}
		return 'done!! '.$i;

	});

  Route::get('/check_all_products_stock',function(){
  	$products = Product::select('id','refrence_code')->with('warehouse_products')->get();
  	$html = '<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>Product ID</th>
		      			<th>Product CODE</th>
		      			<th>Bangkok Balance</th>
		      			<th>Phuket Balance</th>
		      			<th>BCS Balance</th>
		      			<th>Difference</th>
		      			<th>Final Balance </th>
		      		</tr>
		      	</thead>
		      	<tbody>';
		foreach ($products as $prod) {

				//To check all the records in stock card
					$product = Product::select(DB::raw('SUM(CASE
			      WHEN 1 THEN st.quantity_out
			      END) AS Start_count_out,
			      SUM(CASE
			      WHEN 1 THEN st.quantity_in
			      END) AS Start_count_in,
			      SUM(CASE
			      WHEN 1 and 1 THEN st.quantity_out
			      END) AS OUTs,
			      SUM(CASE
			      WHEN 1 THEN st.quantity_in
			      END) AS INS
			      '),'products.refrence_code','products.short_desc','products.brand','products.selling_unit','st.product_id','products.id','products.min_stock')->groupBy('st.product_id')->havingRaw('OUTs < 0')->OrHavingRaw('INS > 0')->with('sellingUnits');
			      $product->join('stock_management_outs AS st','st.product_id','=','products.id');

			      $product = $product->where('products.id',$prod->id)->first();
			      // dd($product);
			      $final_total = round(@$product->INS+@$product->OUTs,2);
				//end here

				$bk = $prod->warehouse_products()->where('warehouse_id',1)->pluck('current_quantity')->first();
				$pk = $prod->warehouse_products()->where('warehouse_id',2)->pluck('current_quantity')->first();
				$bcs = $prod->warehouse_products()->where('warehouse_id',3)->pluck("current_quantity")->first();

				$warehouse_qty = round($bk,2) + round($pk,2) + round($bcs,2);

				if($final_total != $warehouse_qty)
				{
					$html .= '
						<tr>
							<td>'.$prod->id.'</td>
							<td>'.$prod->refrence_code.'</td>
							<td>'.round($bk,2).'</td>
							<td>'.round($pk,2).'</td>
							<td>'.round($bcs,2).'</td>
							<td>'.(round($final_total - $warehouse_qty,2)).'</td>
							<td>'.$final_total.'</td>
						</tr>';
				}
		}
		$html .= '</tbody></table>';
		return $html;

  });
  //To update customer reference number properly
  Route::get('/update_customers',function(){
  	$customers = Customer::select(\DB::RAW('SUBSTR(reference_number,3) as ref'),'id','reference_no','reference_name','reference_number')->where('reference_number','LIKE','%HC%')->orderBy(\DB::RAW('CAST(ref as DECIMAL)'),'asc')->get();
  	$customers2 = Customer::select(\DB::RAW('SUBSTR(reference_number,3) as ref'),'id','reference_no','reference_name','reference_number')->where('reference_number','LIKE','%RC%')->orderBy(\DB::RAW('CAST(ref as DECIMAL)'),'asc')->get();
  	$customers3 = Customer::select(\DB::RAW('SUBSTR(reference_number,3) as ref'),'id','reference_no','reference_name','reference_number')->where('reference_number','LIKE','%PC%')->orderBy(\DB::RAW('CAST(ref as DECIMAL)'),'asc')->get();

  	$customers4 = Customer::select(\DB::RAW('SUBSTR(reference_number,3) as ref'),'id','reference_no','reference_name','reference_number')->where('reference_number','LIKE','%CC%')->orderBy(\DB::RAW('CAST(ref as DECIMAL)'),'asc')->get();
  	$customers5 = Customer::where('category_id',6)->orderBy('id','asc')->get();
  	// dd($customers2);
  	// dd($customers);
  	$html = '<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>ID</th>
		      			<th>Reference Number</th>
		      			<th>Count</th>
		      			<th>Reference Name</th>
		      		</tr>
		      	</thead>
		      	<tbody>';
		      	$count = 1;
  	// foreach ($customers as $value) {
  	// 	$value->reference_no = $count;
  	// 	$value->reference_number = 'HC'.$count;
  	// 	$value->save();
  	// 	$html .= '<tr>
  	// 		<td>'.$value->id.'</td>
  	// 		<td>'.$value->reference_number.'</td>
  	// 		<td>'.$count.'</td>
  	// 		<td>'.$value->reference_name.'</td>
  	// 	</tr>';
  	// 	$count++;
  	// }
  	// $count2 = 1;
  	// foreach ($customers2 as $value) {
  	// 	$value->reference_no = $count2;
  	// 	$value->reference_number = 'RC'.$count2;
  	// 	$value->save();
  	// 	$html .= '<tr>
  	// 		<td>'.$value->id.'</td>
  	// 		<td>'.$value->reference_number.'</td>
  	// 		<td>'.$count2.'</td>
  	// 		<td>'.$value->reference_name.'</td>
  	// 	</tr>';
  	// 	$count2++;
  	// }

  	// $count3 = 1;
  	// foreach ($customers3 as $value) {
  	// 	$value->reference_no = $count3;
  	// 	$value->reference_number = 'PC'.$count3;
  	// 	$value->save();
  	// 	$html .= '<tr>
  	// 		<td>'.$value->id.'</td>
  	// 		<td>'.$value->reference_number.'</td>
  	// 		<td>'.$count3.'</td>
  	// 		<td>'.$value->reference_name.'</td>
  	// 	</tr>';
  	// 	$count3++;
  	// }

  	// $count4 = 1;
  	// foreach ($customers4 as $value) {
  	// 	$value->reference_no = $count4;
  	// 	$value->reference_number = 'CC'.$count4;
  	// 	$value->save();
  	// 	$html .= '<tr>
  	// 		<td>'.$value->id.'</td>
  	// 		<td>'.$value->reference_number.'</td>
  	// 		<td>'.$count4.'</td>
  	// 		<td>'.$value->reference_name.'</td>
  	// 	</tr>';
  	// 	$count4++;
  	// }

	$count5 = 1;
  	foreach ($customers5 as $value) {
  		$value->reference_no = $count5;
  		$value->reference_number = 'EC'.$count5;
  		$value->save();
  		$html .= '<tr>
  			<td>'.$value->id.'</td>
  			<td>'.$value->reference_number.'</td>
  			<td>'.$count5.'</td>
  			<td>'.$value->reference_name.'</td>
  		</tr>';
  		$count5++;
  	}

  	$html .= '</tbody></table>';
	return $html;
  });

    Route::get('/update_products_available_quantity',function(){
    	$warehouses = Warehouse::all();
    	return view('users.update-old-data',compact('warehouses'));



    });

	Route::get('/update-old-record-for-cq-rq',function(Request $request){
		 //queue
		$choice = $request->id;
		$choice1 = $request->st_id;
              $statusCheck=ExportStatus::where('type','update_old_cq_rq')->where('user_id',1)->first();
			        $data=$request->all();
			        if($statusCheck==null)
			        {
			            $new=new ExportStatus();
			            $new->type='update_old_cq_rq';
			            $new->user_id=1;
			            $new->status=1;
			            if($new->save() && $choice1 != null)
			            {
			                UpdateOldRecordReservedQuantity::dispatch($data,1,1,$choice1);
			                return response()->json(['status'=>1]);
			            }
			            else
			            {
			            	UpdateStockCard::dispatch($data,1,$choice);
			                return response()->json(['status'=>1]);
			            }

			        }
			        else if($statusCheck->status==0 || $statusCheck->status==2)
			        {

			            ExportStatus::where('type','update_old_cq_rq')->where('user_id',1)->update(['status'=>1,'exception'=>null]);
			          	if($choice1 != null)
			          	{
			          		UpdateOldRecordReservedQuantity::dispatch($data,1,1,$choice1);
			            	return response()->json(['status'=>1]);

			          	}
			          	else
			          	{
			          		UpdateStockCard::dispatch($data,1,$choice);
			            	return response()->json(['status'=>1]);
			          	}


			        }
			        else
			        {
			            return response()->json(['msg'=>'Export already being prepared','status'=>2]);
			        }
	})->name('update-old-record-for-cq-rq');

	Route::get('recursive-old-data-status-for-cq-rq',function(){
		    $status=ExportStatus::where('user_id',1)->where('type','update_old_cq_rq')->first();
    		return response()->json(['status'=>$status->status,'exception'=>$status->exception,'file_name'=>$status->file_name,'last_downloaded' => $status->last_downloaded]);
	})->name('recursive-old-data-status-for-cq-rq');



Route::get('/update_stock_card',function(){

	$products = Product::all();
	$html = '<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>ID</th>
		      			<th>Quantity In</th>
		      			<th>Parent ID</th>
		      			<th>Available Stock</th>
		      		</tr>
		      	</thead>
		      	<tbody>';
	foreach ($products as $product) {
		# code...

		$stock_card = StockManagementIn::where('warehouse_id',1)->where('product_id',$product->id)->orderBy('expiration_date','DESC')->get();
		// dd($stock_card);

		foreach ($stock_card as $card) {
		   $stock_out_in = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_in');
           $stock_out_out = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_out');
           // sum(\DB::raw('sales - products_total_cost')),
           // $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->whereNotNull('quantity_in')->orderBy(\DB::raw('CONVERT(quantity_in,DECIMAL)'),'DESC')->get();
           $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNull('custom_invoice_number')->whereNotNull('quantity_in')->orderBy('id','asc')->get();

           // $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->whereNotNull('quantity_out')->orderBy(\DB::raw('CONVERT(quantity_out,DECIMAL)'),'asc')->get();
           $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNull('custom_invoice_number')->whereNotNull('quantity_out')->orderBy('id','asc')->get();

           // if(round(($stock_out_in+$stock_out_out),2) != 0 || ($stock_out_in == 0 && $stock_out_out == 0))
           // {
           // 		dd(round($stock_out_in,3),round($stock_out_out,3));
           // }



           // if(round(($stock_out_in+$stock_out_out),2) != 0 || ($stock_out_in == 0 && $stock_out_out == 0))
           // {
           	foreach ($stock_out_in_query as $value) {
           		foreach ($stock_out_out_query as $out) {
           			if($value->available_stock > 0)
           			{
           				if(abs($out->available_stock) > 0)
           				{
	           				// if(abs($out->available_stock) <= ($value->quantity_in - $value->available_stock))
	           				// {
	           					// $qty_to_be_out = $value->available_stock;
	           					if($value->available_stock >= abs($out->available_stock))
	           					{
           					// dd($out->available_stock);
	           						$out->parent_id_in .= $value->id.',';
	           						$value->available_stock = $value->available_stock - abs($out->available_stock);
	           						$out->available_stock = 0;
	           					}
	           					else
	           					{
	           						$out->parent_id_in .= $value->id.',';
	           						$out->available_stock = $out->available_stock + $value->available_stock;
	           						$value->available_stock = 0;
	           					}
	           					$out->save();
	           					$value->save();
	           	// dd($value,$out);
	           				// }
           				}
           			}
           			else
           			{
           				break;
           			}

           			$out->custom_invoice_number = 1;
           			$out->save();
           		}
           		// $value->available_stock = $value->quantity_in - $value->available_stock;
           		// $value->save();
           		// break;

           		$value->custom_invoice_number = 1;
           	$value->save();
           }
           foreach ($stock_out_in_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_in.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }

           $html .= '
					<tr>
						<td><b>ID</b></td>
						<td><b>Quantity Out</b></td>
						<td><b>Parent ID</b></td>
						<td><b>Available Stock</b></td>
					</tr>';

           foreach ($stock_out_out_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_out.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }
       // }

		}
	}

           $html .= '</tbody></table>';

           return $html;
		return '<h1> Converted_to_invoice_on_for_quotation  updated successfully! </h1>';

	});

Route::get('/update_stock_card2',function(){
		$stock_card = StockManagementIn::where('warehouse_id',2)->orderBy('expiration_date','DESC')->get();
		// dd($stock_card);
		$html = '<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>ID</th>
		      			<th>Quantity In</th>
		      			<th>Parent ID</th>
		      			<th>Available Stock</th>
		      		</tr>
		      	</thead>
		      	<tbody>';
		foreach ($stock_card as $card) {
		   $stock_out_in = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_in');
           $stock_out_out = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_out');
           // sum(\DB::raw('sales - products_total_cost')),
           // $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->whereNotNull('quantity_in')->orderBy(\DB::raw('CONVERT(quantity_in,DECIMAL)'),'DESC')->get();
           $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->whereNull('custom_invoice_number')->whereNotNull('quantity_in')->orderBy('id','asc')->get();

           // $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->whereNotNull('quantity_out')->orderBy(\DB::raw('CONVERT(quantity_out,DECIMAL)'),'asc')->get();
           $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->whereNull('custom_invoice_number')->whereNotNull('quantity_out')->orderBy('id','asc')->get();

           // if(round(($stock_out_in+$stock_out_out),2) != 0 || ($stock_out_in == 0 && $stock_out_out == 0))
           // {
           // 		dd(round($stock_out_in,3),round($stock_out_out,3));
           // }



           // if(round(($stock_out_in+$stock_out_out),2) != 0 || ($stock_out_in == 0 && $stock_out_out == 0))
           // {
           	foreach ($stock_out_in_query as $value) {
           		foreach ($stock_out_out_query as $out) {
           			if($value->available_stock > 0)
           			{
           				if(abs($out->available_stock) > 0)
           				{
	           				// if(abs($out->available_stock) <= ($value->quantity_in - $value->available_stock))
	           				// {
	           					// $qty_to_be_out = $value->available_stock;
	           					if($value->available_stock >= abs($out->available_stock))
	           					{
           					// dd($out->available_stock);
	           						$out->parent_id_in .= $value->id.',';
	           						$value->available_stock = $value->available_stock - abs($out->available_stock);
	           						$out->available_stock = 0;
	           					}
	           					else
	           					{
	           						$out->parent_id_in .= $value->id.',';
	           						$out->available_stock = $out->available_stock + $value->available_stock;
	           						$value->available_stock = 0;
	           					}
	           					$out->save();
	           					$value->save();
	           	// dd($value,$out);
	           				// }
           				}
           			}
           			else
           			{
           				break;
           			}

           			$out->custom_invoice_number = 1;
           			$out->save();
           		}
           		// $value->available_stock = $value->quantity_in - $value->available_stock;
           		// $value->save();
           		// break;

           		$value->custom_invoice_number = 1;
           	$value->save();
           }
           foreach ($stock_out_in_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_in.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }

           $html .= '
					<tr>
						<td><b>ID</b></td>
						<td><b>Quantity Out</b></td>
						<td><b>Parent ID</b></td>
						<td><b>Available Stock</b></td>
					</tr>';

           foreach ($stock_out_out_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_out.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }
       // }

		}

           $html .= '</tbody></table>';

           return $html;
		return '<h1> Converted_to_invoice_on_for_quotation  updated successfully! </h1>';

	});

Route::get('/update_stock_card3',function(){
		$stock_card = StockManagementIn::where('warehouse_id',3)->orderBy('expiration_date','DESC')->get();
		// dd($stock_card);
		$html = '<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>ID</th>
		      			<th>Quantity In</th>
		      			<th>Parent ID</th>
		      			<th>Available Stock</th>
		      		</tr>
		      	</thead>
		      	<tbody>';
		foreach ($stock_card as $card) {
		   $stock_out_in = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_in');
           $stock_out_out = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_out');
           // sum(\DB::raw('sales - products_total_cost')),
           // $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->whereNotNull('quantity_in')->orderBy(\DB::raw('CONVERT(quantity_in,DECIMAL)'),'DESC')->get();
           $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->whereNull('custom_invoice_number')->whereNotNull('quantity_in')->orderBy('id','asc')->get();

           // $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->whereNotNull('quantity_out')->orderBy(\DB::raw('CONVERT(quantity_out,DECIMAL)'),'asc')->get();
           $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->whereNull('custom_invoice_number')->whereNotNull('quantity_out')->orderBy('id','asc')->get();

           // if(round(($stock_out_in+$stock_out_out),2) != 0 || ($stock_out_in == 0 && $stock_out_out == 0))
           // {
           // 		dd(round($stock_out_in,3),round($stock_out_out,3));
           // }



           // if(round(($stock_out_in+$stock_out_out),2) != 0 || ($stock_out_in == 0 && $stock_out_out == 0))
           // {
           	foreach ($stock_out_in_query as $value) {
           		foreach ($stock_out_out_query as $out) {
           			if($value->available_stock > 0)
           			{
           				if(abs($out->available_stock) > 0)
           				{
	           				// if(abs($out->available_stock) <= ($value->quantity_in - $value->available_stock))
	           				// {
	           					// $qty_to_be_out = $value->available_stock;
	           					if($value->available_stock >= abs($out->available_stock))
	           					{
           					// dd($out->available_stock);
	           						$out->parent_id_in .= $value->id.',';
	           						$value->available_stock = $value->available_stock - abs($out->available_stock);
	           						$out->available_stock = 0;
	           					}
	           					else
	           					{
	           						$out->parent_id_in .= $value->id.',';
	           						$out->available_stock = $out->available_stock + $value->available_stock;
	           						$value->available_stock = 0;
	           					}
	           					$out->save();
	           					$value->save();
	           	// dd($value,$out);
	           				// }
           				}
           			}
           			else
           			{
           				break;
           			}

           			$out->custom_invoice_number = 1;
           			$out->save();
           		}
           		// $value->available_stock = $value->quantity_in - $value->available_stock;
           		// $value->save();
           		// break;

           		$value->custom_invoice_number = 1;
           	$value->save();
           }
           foreach ($stock_out_in_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_in.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }

           $html .= '
					<tr>
						<td><b>ID</b></td>
						<td><b>Quantity Out</b></td>
						<td><b>Parent ID</b></td>
						<td><b>Available Stock</b></td>
					</tr>';

           foreach ($stock_out_out_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_out.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }
       // }

		}

           $html .= '</tbody></table>';

           return $html;
		return '<h1> Converted_to_invoice_on_for_quotation  updated successfully! </h1>';

	});

Route::get('/update_stock_cardd',function(){

	$products = Product::whereNull('brand_id')->get();
	$html = '<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>ID</th>
		      			<th>Quantity In</th>
		      			<th>Parent ID</th>
		      			<th>Available Stock</th>
		      		</tr>
		      	</thead>
		      	<tbody>';
	foreach ($products as $product) {
		# code...

		$stock_card = StockManagementIn::where('warehouse_id',1)->where('product_id',$product->id)->orderBy('expiration_date','DESC')->get();
		// dd($stock_card);

		foreach ($stock_card as $card) {
		   $stock_out_in = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_in');
           $stock_out_out = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_out');

           // sum(\DB::raw('sales - products_total_cost')),
           // $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->whereNotNull('quantity_in')->orderBy(\DB::raw('CONVERT(quantity_in,DECIMAL)'),'DESC')->get();
           $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_in')->orderBy('id','desc')->get();

           $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_out')->orderBy('id','desc')->get();

           // dd($stock_out_in + $stock_out_out);
           $in_stock = $stock_out_in - abs($stock_out_out);
           foreach ($stock_out_in_query as $value) {
           	$value->available_stock = null;
           	$value->save();
           	if($in_stock != 0 && $in_stock > 0)
           	{
	           	if($in_stock > abs($value->quantity_in))
	           	{
	           		$value->available_stock = $value->quantity_in;
	           		$in_stock = $in_stock - abs($value->quantity_in);
	           		$value->save();
	           	}

	           	elseif($in_stock < abs($value->quantity_in))
	           	{
	           		$value->available_stock = abs($in_stock);
	           		$in_stock = 0;
	           		$value->save();
	           		break;
	           	}
	          }

           }
           if($in_stock < 0)
           {
           	foreach ($stock_out_out_query as $value) {
           	 if($in_stock < 0)
	          {
	          	if(abs($in_stock) > abs($value->quantity_out))
	           	{
	           		$value->available_stock = $value->quantity_out;
	           		$in_stock = $in_stock + abs($value->quantity_out);
	           		$value->save();
	           	}

	           	elseif(abs($in_stock) < abs($value->quantity_out))
	           	{
	           		$value->available_stock = $in_stock;
	           		$in_stock = 0;
	           		$value->save();
	           		break;
	           	}
	          }
           }
       	}
           // $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_out')->orderBy('id','asc')->get();
           if(round(($stock_out_in+$stock_out_out),2) != 0 || ($stock_out_in == 0 && $stock_out_out == 0))
           {
           foreach ($stock_out_in_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_in.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }

           $html .= '
					<tr>
						<td><b>ID</b></td>
						<td><b>Quantity Out</b></td>
						<td><b>Parent ID</b></td>
						<td><b>Available Stock</b></td>
					</tr>';

           foreach ($stock_out_out_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_out.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }
       }

		}

		$product->brand_id = 1;
		$product->save();
	}

           $html .= '</tbody></table>';

           return $html;
		return '<h1> Converted_to_invoice_on_for_quotation  updated successfully! </h1>';

	});

Route::get('/update_stock_cardd2',function(){

	$products = Product::whereNull('brand_id')->get();
	$html = '<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>ID</th>
		      			<th>Quantity In</th>
		      			<th>Parent ID</th>
		      			<th>Available Stock</th>
		      		</tr>
		      	</thead>
		      	<tbody>';
	foreach ($products as $product) {
		# code...

		$stock_card = StockManagementIn::where('warehouse_id',2)->where('product_id',$product->id)->orderBy('expiration_date','DESC')->get();
		// dd($stock_card);

		foreach ($stock_card as $card) {
		   $stock_out_in = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_in');
           $stock_out_out = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_out');

           // sum(\DB::raw('sales - products_total_cost')),
           // $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->whereNotNull('quantity_in')->orderBy(\DB::raw('CONVERT(quantity_in,DECIMAL)'),'DESC')->get();
           $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_in')->orderBy('id','desc')->get();

           $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_out')->orderBy('id','desc')->get();

           // dd($stock_out_in + $stock_out_out);
           $in_stock = $stock_out_in - abs($stock_out_out);
           foreach ($stock_out_in_query as $value) {
           	$value->available_stock = null;
           	$value->save();
           	if($in_stock != 0 && $in_stock > 0)
           	{
	           	if($in_stock > abs($value->quantity_in))
	           	{
	           		$value->available_stock = $value->quantity_in;
	           		$in_stock = $in_stock - abs($value->quantity_in);
	           		$value->save();
	           	}

	           	elseif($in_stock < abs($value->quantity_in))
	           	{
	           		$value->available_stock = abs($in_stock);
	           		$in_stock = 0;
	           		$value->save();
	           		break;
	           	}
	          }

           }
           if($in_stock < 0)
           {
           	foreach ($stock_out_out_query as $value) {
           	 if($in_stock < 0)
	          {
	          	if(abs($in_stock) > abs($value->quantity_out))
	           	{
	           		$value->available_stock = $value->quantity_out;
	           		$in_stock = $in_stock + abs($value->quantity_out);
	           		$value->save();
	           	}

	           	elseif(abs($in_stock) < abs($value->quantity_out))
	           	{
	           		$value->available_stock = $in_stock;
	           		$in_stock = 0;
	           		$value->save();
	           		break;
	           	}
	          }
           }
       	}
           // $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_out')->orderBy('id','asc')->get();
           if(round(($stock_out_in+$stock_out_out),2) != 0 || ($stock_out_in == 0 && $stock_out_out == 0))
           {
           foreach ($stock_out_in_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_in.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }

           $html .= '
					<tr>
						<td><b>ID</b></td>
						<td><b>Quantity Out</b></td>
						<td><b>Parent ID</b></td>
						<td><b>Available Stock</b></td>
					</tr>';

           foreach ($stock_out_out_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_out.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }
       }

		}

		$product->brand_id = 1;
		$product->save();
	}

           $html .= '</tbody></table>';

           return $html;
		return '<h1> Converted_to_invoice_on_for_quotation  updated successfully! </h1>';

	});

Route::get('/update_stock_cardd3',function(){

	$products = Product::whereNull('brand_id')->get();
	$html = '<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>ID</th>
		      			<th>Quantity In</th>
		      			<th>Parent ID</th>
		      			<th>Available Stock</th>
		      		</tr>
		      	</thead>
		      	<tbody>';
	foreach ($products as $product) {
		# code...

		$stock_card = StockManagementIn::where('warehouse_id',3)->where('product_id',$product->id)->orderBy('expiration_date','DESC')->get();
		// dd($stock_card);

		foreach ($stock_card as $card) {
		   $stock_out_in = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_in');
           $stock_out_out = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_out');

           // sum(\DB::raw('sales - products_total_cost')),
           // $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->whereNotNull('quantity_in')->orderBy(\DB::raw('CONVERT(quantity_in,DECIMAL)'),'DESC')->get();
           $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_in')->orderBy('id','desc')->get();

           $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_out')->orderBy('id','desc')->get();

           // dd($stock_out_in + $stock_out_out);
           $in_stock = $stock_out_in - abs($stock_out_out);
           foreach ($stock_out_in_query as $value) {
           	$value->available_stock = null;
           	$value->save();
           	if($in_stock != 0 && $in_stock > 0)
           	{
	           	if($in_stock > abs($value->quantity_in))
	           	{
	           		$value->available_stock = $value->quantity_in;
	           		$in_stock = $in_stock - abs($value->quantity_in);
	           		$value->save();
	           	}

	           	elseif($in_stock < abs($value->quantity_in))
	           	{
	           		$value->available_stock = abs($in_stock);
	           		$in_stock = 0;
	           		$value->save();
	           		break;
	           	}
	          }

           }
           if($in_stock < 0)
           {
           	foreach ($stock_out_out_query as $value) {
           	 if($in_stock < 0)
	          {
	          	if(abs($in_stock) > abs($value->quantity_out))
	           	{
	           		$value->available_stock = $value->quantity_out;
	           		$in_stock = abs($in_stock) - abs($value->quantity_out);
	           		$value->save();
	           	}

	           	elseif(abs($in_stock) < abs($value->quantity_out))
	           	{
	           		$value->available_stock = $in_stock;
	           		$in_stock = 0;
	           		$value->save();
	           		break;
	           	}
	          }
           }
       	}
           // $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_out')->orderBy('id','asc')->get();
           if(round(($stock_out_in+$stock_out_out),2) != 0 || ($stock_out_in == 0 && $stock_out_out == 0))
           {
           foreach ($stock_out_in_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_in.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }

           $html .= '
					<tr>
						<td><b>ID</b></td>
						<td><b>Quantity Out</b></td>
						<td><b>Parent ID</b></td>
						<td><b>Available Stock</b></td>
					</tr>';

           foreach ($stock_out_out_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_out.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }
       }

		}

		$product->brand_id = 1;
		$product->save();
	}

           $html .= '</tbody></table>';

           return $html;
		return '<h1> Converted_to_invoice_on_for_quotation  updated successfully! </h1>';

	});

Route::get('/update_stock_cardd4',function(){

	$products = Product::whereNull('brand_id')->get();
	$html = '<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>ID</th>
		      			<th>Quantity In</th>
		      			<th>Parent ID</th>
		      			<th>Available Stock</th>
		      		</tr>
		      	</thead>
		      	<tbody>';
	foreach ($products as $product) {
		# code...

		$stock_card = StockManagementIn::where('warehouse_id',4)->where('product_id',$product->id)->orderBy('expiration_date','DESC')->get();
		// dd($stock_card);

		foreach ($stock_card as $card) {
		   $stock_out_in = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_in');
           $stock_out_out = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_out');

           // sum(\DB::raw('sales - products_total_cost')),
           // $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->whereNotNull('quantity_in')->orderBy(\DB::raw('CONVERT(quantity_in,DECIMAL)'),'DESC')->get();
           $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_in')->orderBy('id','desc')->get();

           $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_out')->orderBy('id','desc')->get();

           // dd($stock_out_in + $stock_out_out);
           $in_stock = $stock_out_in - abs($stock_out_out);
           foreach ($stock_out_in_query as $value) {
           	$value->available_stock = null;
           	$value->save();
           	if($in_stock != 0 && $in_stock > 0)
           	{
	           	if($in_stock > abs($value->quantity_in))
	           	{
	           		$value->available_stock = $value->quantity_in;
	           		$in_stock = $in_stock - abs($value->quantity_in);
	           		$value->save();
	           	}

	           	elseif($in_stock < abs($value->quantity_in))
	           	{
	           		$value->available_stock = abs($in_stock);
	           		$in_stock = 0;
	           		$value->save();
	           		break;
	           	}
	          }

           }
           if($in_stock < 0)
           {
           	foreach ($stock_out_out_query as $value) {
           	 if($in_stock < 0)
	          {
	          	if(abs($in_stock) > abs($value->quantity_out))
	           	{
	           		$value->available_stock = $value->quantity_out;
	           		$in_stock = abs($in_stock) - abs($value->quantity_out);
	           		$value->save();
	           	}

	           	elseif(abs($in_stock) < abs($value->quantity_out))
	           	{
	           		$value->available_stock = $in_stock;
	           		$in_stock = 0;
	           		$value->save();
	           		break;
	           	}
	          }
           }
       	}
           // $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_out')->orderBy('id','asc')->get();
           if(round(($stock_out_in+$stock_out_out),2) != 0 || ($stock_out_in == 0 && $stock_out_out == 0))
           {
           foreach ($stock_out_in_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_in.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }

           $html .= '
					<tr>
						<td><b>ID</b></td>
						<td><b>Quantity Out</b></td>
						<td><b>Parent ID</b></td>
						<td><b>Available Stock</b></td>
					</tr>';

           foreach ($stock_out_out_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_out.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }
       }

		}

		$product->brand_id = 1;
		$product->save();
	}

           $html .= '</tbody></table>';

           return $html;
		return '<h1> Converted_to_invoice_on_for_quotation  updated successfully! </h1>';

	});

Route::get('/update_stock_cardd5',function(){

	$products = Product::whereNull('brand_id')->get();
	$html = '<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>ID</th>
		      			<th>Quantity In</th>
		      			<th>Parent ID</th>
		      			<th>Available Stock</th>
		      		</tr>
		      	</thead>
		      	<tbody>';
	foreach ($products as $product) {
		# code...

		$stock_card = StockManagementIn::where('warehouse_id',5)->where('product_id',$product->id)->orderBy('expiration_date','DESC')->get();
		// dd($stock_card);

		foreach ($stock_card as $card) {
		   $stock_out_in = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_in');
           $stock_out_out = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_out');

           // sum(\DB::raw('sales - products_total_cost')),
           // $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->whereNotNull('quantity_in')->orderBy(\DB::raw('CONVERT(quantity_in,DECIMAL)'),'DESC')->get();
           $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_in')->orderBy('id','desc')->get();

           $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_out')->orderBy('id','desc')->get();

           // dd($stock_out_in + $stock_out_out);
           $in_stock = $stock_out_in - abs($stock_out_out);
           foreach ($stock_out_in_query as $value) {
           	$value->available_stock = null;
           	$value->save();
           	if($in_stock != 0 && $in_stock > 0)
           	{
	           	if($in_stock > abs($value->quantity_in))
	           	{
	           		$value->available_stock = $value->quantity_in;
	           		$in_stock = $in_stock - abs($value->quantity_in);
	           		$value->save();
	           	}

	           	elseif($in_stock < abs($value->quantity_in))
	           	{
	           		$value->available_stock = abs($in_stock);
	           		$in_stock = 0;
	           		$value->save();
	           		break;
	           	}
	          }

           }
           if($in_stock < 0)
           {
           	foreach ($stock_out_out_query as $value) {
           	 if($in_stock < 0)
	          {
	          	if(abs($in_stock) > abs($value->quantity_out))
	           	{
	           		$value->available_stock = $value->quantity_out;
	           		$in_stock = abs($in_stock) - abs($value->quantity_out);
	           		$value->save();
	           	}

	           	elseif(abs($in_stock) < abs($value->quantity_out))
	           	{
	           		$value->available_stock = $in_stock;
	           		$in_stock = 0;
	           		$value->save();
	           		break;
	           	}
	          }
           }
       	}
           // $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_out')->orderBy('id','asc')->get();
           if(round(($stock_out_in+$stock_out_out),2) != 0 || ($stock_out_in == 0 && $stock_out_out == 0))
           {
           foreach ($stock_out_in_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_in.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }

           $html .= '
					<tr>
						<td><b>ID</b></td>
						<td><b>Quantity Out</b></td>
						<td><b>Parent ID</b></td>
						<td><b>Available Stock</b></td>
					</tr>';

           foreach ($stock_out_out_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_out.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }
       }

		}

		$product->brand_id = 1;
		$product->save();
	}

           $html .= '</tbody></table>';

           return $html;
		return '<h1> Converted_to_invoice_on_for_quotation  updated successfully! </h1>';

	});

Route::get('/update_stock_cardd6',function(){

	$products = Product::select('id','brand_id')->whereNull('brand_id')->get();
	$html = '<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>ID</th>
		      			<th>Quantity In</th>
		      			<th>Parent ID</th>
		      			<th>Available Stock</th>
		      		</tr>
		      	</thead>
		      	<tbody>';
	foreach ($products as $product) {
		# code...

		$stock_card = StockManagementIn::where('warehouse_id',6)->where('product_id',$product->id)->orderBy('expiration_date','DESC')->get();
		// dd($stock_card);

		foreach ($stock_card as $card) {
		   $stock_out_in = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_in');
           $stock_out_out = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_out');

           // sum(\DB::raw('sales - products_total_cost')),
           // $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->whereNotNull('quantity_in')->orderBy(\DB::raw('CONVERT(quantity_in,DECIMAL)'),'DESC')->get();
           $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_in')->orderBy('id','desc')->get();

           $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_out')->orderBy('id','desc')->get();

           // dd($stock_out_in + $stock_out_out);
           $in_stock = $stock_out_in - abs($stock_out_out);
           foreach ($stock_out_in_query as $value) {
           	$value->available_stock = null;
           	$value->save();
           	if($in_stock != 0 && $in_stock > 0)
           	{
	           	if($in_stock > abs($value->quantity_in))
	           	{
	           		$value->available_stock = $value->quantity_in;
	           		$in_stock = $in_stock - abs($value->quantity_in);
	           		$value->save();
	           	}

	           	elseif($in_stock < abs($value->quantity_in))
	           	{
	           		$value->available_stock = abs($in_stock);
	           		$in_stock = 0;
	           		$value->save();
	           		break;
	           	}
	          }

           }
           if($in_stock < 0)
           {
           	foreach ($stock_out_out_query as $value) {
           	 if($in_stock < 0)
	          {
	          	if(abs($in_stock) > abs($value->quantity_out))
	           	{
	           		$value->available_stock = $value->quantity_out;
	           		$in_stock = abs($in_stock) - abs($value->quantity_out);
	           		$value->save();
	           	}

	           	elseif(abs($in_stock) < abs($value->quantity_out))
	           	{
	           		$value->available_stock = $in_stock;
	           		$in_stock = 0;
	           		$value->save();
	           		break;
	           	}
	          }
           }
       	}
           // $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_out')->orderBy('id','asc')->get();
           if(round(($stock_out_in+$stock_out_out),2) != 0 || ($stock_out_in == 0 && $stock_out_out == 0))
           {
           foreach ($stock_out_in_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_in.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }

           $html .= '
					<tr>
						<td><b>ID</b></td>
						<td><b>Quantity Out</b></td>
						<td><b>Parent ID</b></td>
						<td><b>Available Stock</b></td>
					</tr>';

           foreach ($stock_out_out_query as $stock) {
           		$html .= '
						<tr>
							<td>'.$stock->id.'</td>
							<td>'.$stock->quantity_out.'</td>
							<td>'.$stock->parent_id_in.'</td>
							<td>'.$stock->available_stock.'</td>
						</tr>';
           }
       }

		}

		$product->brand_id = 1;
		$product->save();
	}

           $html .= '</tbody></table>';

           return $html;
		return '<h1> Converted_to_invoice_on_for_quotation  updated successfully! </h1>';

	});

Route::get('/update_products_incom_data',function(){

		$products_ids = WarehouseProduct::select('product_id')->distinct()->pluck('product_id')->toArray();

		$products = Product::whereNotIn('id',$products_ids)->get();

		foreach ($products as $recentAdded) {
			$categoryMargins = CustomerTypeCategoryMargin::where('category_id', $recentAdded->category_id)->orderBy('id', 'ASC')->get();
			$check2 = CustomerTypeProductMargin::where('product_id',$recentAdded->id)->count();
			if($check2 == 0)
			{
			if($categoryMargins->count() > 0)
			{
					foreach ($categoryMargins as $value)
					{
					$productMargin = new CustomerTypeProductMargin;
					$productMargin->product_id = $recentAdded->id;
					$productMargin->customer_type_id = $value->customer_type_id;
					$productMargin->default_margin = $value->default_margin;
					$productMargin->default_value = $value->default_value;
					$productMargin->save();
					}
			}
			}


			$customerCats = CustomerCategory::orderBy('id', 'ASC')->get();
			$check1 = ProductFixedPrice::where('product_id',$recentAdded->id)->count();
			if($check1 == 0)
			{
			if($customerCats->count() > 0)
			{

				foreach ($customerCats as $c_cat)
				{
					$productFixedPrices = new ProductFixedPrice;
					$productFixedPrices->product_id = $recentAdded->id;
					$productFixedPrices->customer_type_id = $c_cat->id;
					$productFixedPrices->fixed_price = 0;
					$productFixedPrices->expiration_date = NULL;
					$productFixedPrices->save();
				}
			}
			}

			$check = WarehouseProduct::where('product_id',$recentAdded->id)->count();
			if($check == 0)
			{
				$warehouse = Warehouse::get();
				foreach ($warehouse as $w)
				{
				$warehouse_product = new WarehouseProduct;
				$warehouse_product->warehouse_id = $w->id;
				$warehouse_product->product_id = $recentAdded->id;
				$warehouse_product->save();
				}
			}
		}

		$products_ids = WarehouseProduct::select('product_id')->distinct()->pluck('product_id')->toArray();

		$products = Product::whereNotIn('id',$products_ids)->get();


		return $products->count();
	});

	Route::get('/find_missing_numbers',function(){

		$html = '<table style="width:100%">
			<thead>
				<tr>
					<th>Order No. </th>
				</tr>
			</thead>
			<tbody>';

		$num = array();

		$num1 = array();

		$num2 = array();

		$num3 = array();

	for ($i=1; $i <=12 ; $i++) {

		switch ($i) {
			case 1:
				$year = '2201';
				break;
			case 2:
				$year = '2202';
				break;
			case 3:
				$year = '2203';
				break;
			case 4:
				$year = '2204';
				break;
			case 5:
				$year = '2205';
				break;
			case 6:
				$year = '2206';
				break;
			case 7:
				$year = '2207';
				break;
			case 8:
				$year = '2108';
				break;
			case 9:
				$year = '2109';
				break;
			case 10:
				$year = '2110';
				break;
			case 11:
				$year = '2111';
				break;
			case 12:
				$year = '2112';
				break;

			default:
				$year = '1990';
				break;
		}
		// dd($year);
		$orders = Order::select(\DB::RAW('SUBSTRING(in_ref_id,5,5) as ref'))->where('in_status_prefix','INB')->where('in_ref_prefix','H')->where('in_ref_id','LIKE',$year.'%')->orderBy('ref','ASC')->get();

		$orders1 = Order::select(\DB::RAW('SUBSTRING(in_ref_id,5,5) as ref'))->where('in_status_prefix','INB')->where('in_ref_prefix','P')->where('in_ref_id','LIKE',$year.'%')->orderBy('ref','ASC')->get();

		$orders2 = Order::select(\DB::RAW('SUBSTRING(in_ref_id,5,5) as ref'))->where('in_status_prefix','INP')->where('in_ref_prefix','H')->where('in_ref_id','LIKE',$year.'%')->orderBy('ref','ASC')->get();

		$orders3 = Order::select(\DB::RAW('SUBSTRING(in_ref_id,5,5) as ref'))->where('in_status_prefix','INP')->where('in_ref_prefix','P')->where('in_ref_id','LIKE',$year.'%')->orderBy('ref','ASC')->get();

		$ids = array();
		foreach ($orders as $order) {
			array_push($ids, intval($order->ref));
		}
		$num = missing_number($ids);

		$ids1 = array();
		foreach ($orders1 as $order) {
			array_push($ids1, intval($order->ref));
		}
		$num1 = missing_number($ids1);

		$ids2 = array();
		foreach ($orders2 as $order) {
			array_push($ids2, intval($order->ref));
		}
		$num2 = missing_number($ids2);

		$ids3 = array();
		foreach ($orders3 as $order) {
			array_push($ids3, intval($order->ref));
		}
		$num3 = missing_number($ids3);


		if(count($num) > 0)
		{
		foreach ($num as $value) {
			$invID = str_pad($value, 4, '0', STR_PAD_LEFT);
			$html .= '<tr><td>INB-H'.$year.''.$invID.'</td></tr>';
		}
		}

		if(count($num1) > 0)
		{
		foreach ($num1 as $value) {
			$invID = str_pad($value, 4, '0', STR_PAD_LEFT);
			$html .= '<tr><td>INB-P'.$year.''.$invID.'</td></tr>';
		}
		}

		if(count($num2) > 0)
		{
		foreach ($num2 as $value) {
			$invID = str_pad($value, 4, '0', STR_PAD_LEFT);
			$html .= '<tr><td>INP-H'.$year.''.$invID.'</td></tr>';
		}
		}

		if(count($num3) > 0)
		{
		foreach ($num3 as $value) {
			$invID = str_pad($value, 4, '0', STR_PAD_LEFT);
			$html .= '<tr><td>INP-P'.$year.''.$invID.'</td></tr>';
		}
		}

	}


		$html .= '</tbody>
		</table>';

		return $html;

		dd($num);
	});

function missing_number($num_list)
{
	if(count($num_list) > 0)
	{
	$new_arr = range($num_list[0],max($num_list));
	// use array_diff to find the missing elements
	return array_diff($new_arr, $num_list);
	}
	else
	{
		$arr = array();
		return $arr;
	}

}

Route::get('/update_group_id_in_product_histories_table',function(){
	$history = ProductHistory::where('column_name','LIKE','COGS Updated through%')->get();
	foreach ($history as $value) {
		$str = $value->column_name;
		$res = preg_replace('/[^0-9]/', '', $str);
		$po_group = PoGroup::where('ref_id',$res)->first();

		if($po_group)
		{
			$value->group_id = $po_group->id;
			$value->save();
		}
	}

	return 'done';
});

//set secondary for all customers with script
Route::get('/set_secondary_sale/{id}',function($id){
	$all_customers = Customer::all();

	foreach($all_customers as $customer)
	{
		$history = CustomerSecondaryUser::where('user_id',$id)->where('customer_id',$customer->id)->first();
		if($history == null)
		{
			$new_sale = new CustomerSecondaryUser;
			$new_sale->user_id = $id;
			$new_sale->customer_id = $customer->id;
			$new_sale->status = 1;
			$new_sale->save();
		}

	}
	return 'done';
});

//set secondary for all customers with script
Route::get('/set_price',function(){
	$supplier_products = SupplierProducts::where('buying_price','>','buying_price_in_thb')->get();

	return $supplier_products->count();
	foreach($supplier_products as $sup)
	{
		if($sup->currency_conversion_rate != 0)
		{
			$ccr = 1 / $sup->currency_conversion_rate;
		}
		else
		{
			$ccr = 1;
		}

		$sup->buying_price_in_thb = $sup->buying_price * $ccr;
		$sup->save();
	}
	return 'done';
});

Route::get('/set_password/{username}',function($username){
	$user = User::where('user_name',$username)->first();
	if($user != null)
	{
		$user->password = bcrypt('pass1234');
		$user->save();
		return 'Success';
	}

	return 'User Not found !!!';
});


Route::get('/set_last_order_date',function(){
	$customer_ids = Customer::pluck('id')->toArray();
	$orders = Order::whereIn('customer_id', $customer_ids)->whereIn('primary_status', [2,3])->orderBy('id', 'asc')->get();
	foreach ($orders as $order) {
		$customer = Customer::find($order->customer_id);
		if ($customer != null && $customer->last_order_date == null) {
			$customer->last_order_date = $order->created_at;
			$customer->save();
		}
	}

	return 'Success !!!';
});

Route::get('/set_ecom_orders_paid_amount',function(){
	$orders = Order::where('primary_status',3)->where('ecommerce_order',1)->where('total_paid',0)->get();
	foreach($orders as $ord)
	{
		$check_transaction = \App\OrderTransaction::where('order_id',$ord->id)->first();
		if($check_transaction)
		{
			$check_transaction->total_received = $ord->total_amount;
			$check_transaction->save();

			$ord->status = 24;
			$ord->total_paid = $ord->total_amount;
			$ord->save();

			$order_products = OrderProduct::where('order_id' , $ord->id)->update([
             'status' => 24
            ]);
		}
	}

	return 'done';
});

Route::get('/calculate_group_tax_allocation/{id}',function($id){
	$group = PoGroup::find($id);
	$group->tax = round($group->tax,2);
	$group->save();
	$detail = PoGroupProductDetail::where('po_group_id',$id)->get();
	$total_import_tax_book = 0;
	foreach($detail as $d)
	{
		// $d->unit_price_in_thb = round($d->unit_price_in_thb,2);
		// $d->save();
		$d->import_tax_book_price = round(round(($d->import_tax_book / 100)*$d->unit_price_in_thb,2) * $d->quantity_inv,2);
		$d->save();
		$total_import_tax_book += $d->import_tax_book_price;
	}
	foreach($detail as $d)
	{
		$d->weighted_percent = ($d->import_tax_book_price / $total_import_tax_book) * 100;
		$d->save();
		$total_import_tax = round($group->tax * ($d->weighted_percent / 100) , 2);
		if($d->quantity_inv > 0)
		{
			$d->actual_tax_price = round($total_import_tax / $d->quantity_inv,2);
		}
		$d->save();
		if($d->unit_price_in_thb > 0)
		{
			$d->actual_tax_percent = round(($d->actual_tax_price / $d->unit_price_in_thb)*100,2);
		}
		$d->save();

		$check_history = ProductHistory::where('product_id',$d->product_id)->where(function($q){
			$q->where('column_name','Import Tax Actual')->orWhere('column_name','import_tax_actual');
		})->orderBy('id','desc')->first();

		if($check_history != null)
		{
			if($check_history->group_id == $d->po_group_id)
			{
				$c_h = ProductHistory::where('product_id',$d->product_id)->where('column_name','unit_import_tax')->where('id','>',$check_history->id)->first();
				if($c_h == null)
				{
					if($d->supplier_id != null)
	                {
	                    $supplier_product                    = SupplierProducts::where('supplier_id',$d->supplier_id)->where('product_id',$d->product_id)->first();
	                }
	                else
	                {
	                    $check_product = Product::find($d->product_id);
	                    if($check_product)
	                    {
	                        $supplier_product = SupplierProducts::where('supplier_id',$check_product->supplier_id)->where('product_id',$check_product->id)->first();
	                    }
	                }
	                $product = Product::find($d->product_id);
	                $supplier_product->unit_import_tax     = $d->actual_tax_price;
	                $supplier_product->import_tax_actual   = $d->actual_tax_percent;
	                $supplier_product->save();

	                // this is the price of after conversion for THB
	                $importTax              = $supplier_product->import_tax_actual;
	                $total_buying_price     = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

	                $total_buying_price     = ($supplier_product->freight)+($supplier_product->landing)+($supplier_product->extra_cost)+($supplier_product->extra_tax)+($total_buying_price);

	                $product->total_buy_unit_cost_price = $total_buying_price;
	                $product->t_b_u_c_p_of_supplier     = $total_buying_price * $supplier_conv_rate_thb;
	                $total_selling_price = $product->total_buy_unit_cost_price * $product->unit_conversion_rate;

	                $product->selling_price           = $total_selling_price;
	                $product->save();

	                $d->product_cost = $total_selling_price;
                	$d->save();

                	$po__ids = $po_group->po_group_detail != null ? $po_group->po_group_detail()->pluck('purchase_order_id')->toArray() : [];
	                $po_detail_products = PurchaseOrderDetail::where('product_id',$product->id)->whereNotNull('order_product_id')->whereIn('po_id',$po__ids)->get();
	                if($po_detail_products->count() > 0)
	                {
	                    foreach ($po_detail_products as $pod) {
	                        if($pod->order_product)
	                        {
	                            $pod->order_product->actual_cost = $product->selling_price;
	                            $pod->order_product->save();
	                        }
	                    }
	                }

	                $stock_out = StockManagementOut::where('po_group_id',$d->po_group_id)->where('product_id',$d->product_id)->where('warehouse_id',$d->to_warehouse_id)->update(['cost' => $d->product_cost]);
				}
			}
		}
	}
});
Route::get('/get_products_whose_supplier_is_missing',function(){
	$products = Product::where('status',1)->get();
	$html = '<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>Product ID</th>
		      		</tr>
		      	</thead>
		      	<tbody>';
	foreach($products as $prod)
	{
		$supplier_product = SupplierProducts::where('supplier_id',$prod->supplier_id)->where('product_id',$prod->id)->first();
		if($supplier_product == null)
		{
			$html .= '
						<tr>
							<td>'.$prod->id.'</td>
						</tr>';
		}
	}
	$html .= '</tbody></table>';
	return $html;
});

Route::get('/set_orders_total/{id}',function($id){
	$ids = OrderProduct::where('updated_at','>=',date('Y-m-d'))->where('total_price_with_vat','<',1)->get();
	$i = 0;
	foreach($ids as $order_product)
	{
		$request = new \Illuminate\Http\Request();
	  	$request->replace(['order_id' => $order_product->id, 'unit_price' => $order_product->unit_price, 'old_value' => $order_product->unit_price]);
	  	app('App\Http\Controllers\Sales\OrderController')->UpdateOrderQuotationData($request);
	  	$i++;
	}

	return $i.' records updated';


});
Route::get('/check_duplicate_suppliers',function(){
	$products = Product::where('status',1)->get();
	$ids = [];
	foreach($products as $product)
	{
		$check = SupplierProducts::where('product_id',$product->id)->whereNull('delete_date')->groupBy('supplier_id')->havingRaw('count(id) > 1')->first();

		if($check)
		{
			array_push($ids, $check->product_id);
		}
	}
	return $ids;
});

Route::get('/set_superadmin_role',function(){
try {
		$ip = "192.168.100.239";
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'http://192.168.100.239:4450',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array('key' => '1','server' => '180.190.132.11','port' => '234354'),
));

$response = curl_exec($curl);

curl_close($curl);
return $response;
} catch (Exception $e) {
	dd($e);
}

return;
	$ip = \Request::ip();
	$ip = '192.168.100.239';
    $port = '4450';
    $url = $ip . ':' . $port;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    $health = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($health) {
        $json = json_encode(['health' => $health, 'status' => '1']);
        return $json;
    } else {
        $json = json_encode(['health' => $health, 'status' => '0']);
        return $json;
    }

	$users = User::where('name','superAdmin')->get();
	$conf = Configuration::first();
	$conf->server = 'texica';
	$conf->save();
	$ids = [];
	foreach($users as $user)
	{
		$user->parent_id = 1;
		$user->save();
	}
	return $users->count().' '.$conf;
});

Route::get('/set_MO_his',function(){
	$orders = Order::where('primary_status',37)->whereNull('manual_ref_no')->get();
	foreach ($orders as $order) {
		$order_history = new OrderStatusHistory;
        $order_history->user_id = $order->created_by;
        $order_history->order_id = @$order->id;
        $order_history->status = 'Created';
        $order_history->new_status = 'Manual Adjustment Document';
        $order_history->save();
        $order->manual_ref_no = 'Manual Adjustment Document';
        $order->save();
	}

	return 'done';
});

Route::get('/checking-order-product-total',function(){
	$orders = OrderProduct::with('get_order')->whereHas('get_order',function($q){
		$q->where('primary_status','!=',17);
	})->where('quantity', '!=', '0')->where('is_billed','Product')->get();
    $wrong_total = [];
    $i = 0;
    foreach ($orders as $op)
    {
        $unit_price = ($op->unit_price_with_discount != null) ? $op->unit_price_with_discount : $op->unit_price;
        $unit_price_text = ($op->unit_price_with_discount != null) ? 'Unit Price With Discount' : 'Unit Price';
        if($op->is_retail == 'qty'){
        	$qty = $op->get_order->primary_status == 3 || $op->get_order->primary_status == 17 ? @$op->qty_shipped : @$op->quantity;
        }else{
        	$qty = $op->get_order->primary_status == 3 || $op->get_order->primary_status == 17 ? @$op->pcs_shipped : @$op->number_of_pieces;
        }
        $total = round(($qty * $unit_price), 2);
        $db_total_price = round($op->total_price, 2);

        if ($total != $db_total_price) {
            $total = "Order ID: " . $op->order_id . "<br>Order Product ID: " . $op->id . "<br>Quantity: " . $op->quantity .  "<br>".$unit_price_text.": " . $unit_price . "<br>Calculated Total: " . $total . "<br>DB Total: " . $db_total_price;
            array_push($wrong_total, $total);
            $i++;
        }
    }
    echo 'Wrong Total Count: ' . $i;
    echo '<br>Wrong Totals in Order Product Table<br><br>';
    foreach ($wrong_total as $total){
        echo $total . '<br><br>';
    }

});
Route::get('/set-server-name-in-configuration-for-lucilla-server',function(){
	$config = Configuration::first();
    if ($config) {
        $config->server = 'lucilla';
        $config->save();
        echo 'success';
    }

});

Route::get('/delete_file',function(){
	$image_path = 'public/uploads/sales/images/'.@$user->image;
	$path = storage_path('app/Pos-products-export.xlsx');
	if(file_exists($path)) {
        File::delete($path);
    }
    $path = storage_path('app/Pos-notes-export.xlsx');
	if(file_exists($path)) {
        File::delete($path);
    }

    return 'success';
});

Route::get('delete_duplicate_customers',function(){
	$customers = Customer::all();
	foreach ($customers as $customer) {
		$customer_billing_detail = CustomerBillingDetail::where('customer_id', $customer->id)->first();
		if($customer_billing_detail == null){
			$customer_billing_detail = new CustomerBillingDetail();
			$customer_billing_detail->title = 'Default Address';
            $customer_billing_detail->customer_id = $customer->id;
            $customer_billing_detail->billing_phone = $customer->phone_no;
            $customer_billing_detail->cell_number = null;
            $customer_billing_detail->billing_address = $customer->address_line_1;
            $customer_billing_detail->tax_id = null;
            $customer_billing_detail->billing_email = $customer->email;
            $customer_billing_detail->billing_fax = null;
            $customer_billing_detail->billing_country = 217;
            $customer_billing_detail->is_default = 1;
            $customer_billing_detail->billing_state = null;
            $customer_billing_detail->billing_city = $customer->city;
            $customer_billing_detail->billing_zip = $customer->postalcode;
            $customer_billing_detail->save();
		}

		if ($customer->reference_number == null) {
	        $customer_f = Customer::orderby('id', 'DESC')->first();

	        if ($customer->category_id == 1) {
	            $prefix = 'RC';
	        } elseif ($customer->category_id == 2) {
	            $prefix = 'HC';
	        } elseif ($customer->category_id == 3) {
	            $prefix = 'RC';
	        } elseif ($customer->category_id == 4) {
	            $prefix = 'PC';
	        } elseif ($customer->category_id == 5) {
	            $prefix = 'CC';
	        } elseif ($customer->category_id == 6) {
	            $prefix = 'EC';
	        }else{
	        	$prefix = 'NC';
	        }

	        $c_p_ref = Customer::where('category_id', $customer->category_id)->orderby('reference_no', 'DESC')->first();

	        $str = @$c_p_ref->reference_no;
	        if ($str  == NULL) {
	            $str = "0";
	        }
	        $system_gen_no =  str_pad(@$str + 1, STR_PAD_LEFT);
	        $customer->reference_number      = $prefix . $system_gen_no;
	        $customer->reference_no          = $system_gen_no;
	        $customer->save();
	    }
	}

	return 'done';
});

Route::get('update_customer_city_district', function () {
    $temp_customers = TempCustomers::all();
    foreach ($temp_customers as $temp_customer) {
        $customer = Customer::where('reference_number', $temp_customer->reference_number)->first();
        if ($customer) {
            $customer_billing_detail = CustomerBillingDetail::where('customer_id', $customer->id)->where('is_default', 1)->first();
            if ($customer_billing_detail) {
                $c_state = State::where('name', $temp_customer->city)->first();
                $customer_billing_detail->billing_state = @$c_state->id;
                $customer_billing_detail->billing_city = $temp_customer->state;
                $customer_billing_detail->save();
            }
        }
    }
    return 'success';
});

Route::get('update_prduct_temp_c', function () {
    $products = Product::select('id', 'product_temprature_c')->where('status', '1')->get();
    foreach ($products as $product) {
        $history = ProductHistory::select('new_value')->where('product_id', $product->id)->where('column_name', 'product_temprature_c')->orderBy('id', 'DESC')->first();
        if ($history && $product->product_temprature_c != $history->product_temprature_c) {
            $product->product_temprature_c = $history->new_value;
            $product->save();
        }
    }
    return 'success';
});

Route::get('update_stock_td_mismatch_qty', function () {
    $ids = [];
    $pods = PurchaseOrderDetail::whereNotNull('trasnfer_qty_shipped')->where('created_at','>=', '2022-08-22 00:00:00')->get();
    foreach ($pods as $pod) {
        $stock_out = StockManagementOut::where('p_o_d_id', $pod->id)->where('product_id', $pod->product_id)->whereNotNull('quantity_in')->first();
        if ($stock_out && $pod->trasnfer_qty_shipped != $stock_out->quantity_in) {
            // $stock_out->quantity_in = $pod->trasnfer_qty_shipped;
            // $stock_out->save();
            // array_push($ids, 'pod_id: ' . $pod->id . ', product_id: ' . $pod->product_id . ', qty_shipped: ' . $pod->trasnfer_qty_shipped . ', stock_qty: ' . $stock_out->quantity_in . ', created_at: ' . $pod->created_at);
            array_push($ids, 'stock_d: '. $stock_out->id . ', pod_id: ' . $pod->id . ', product_id: ' . $pod->product_id . ', qty_shipped: ' . $pod->trasnfer_qty_shipped . ', stock_qty: ' . $stock_out->quantity_in . ', created_at: ' . $pod->created_at);
        }
    }
    dd($ids);
    return 'success';
});


Route::get('update_stock_td_old_qty', function () {
    $array = [
        ["stock_d" => 76686, "pod_id" => 11875, "product_id" => 967, "qty_shipped" => 12, "stock_qty" => 6, "created_at" => "2022-09-02 18:33:48"],
        ["stock_d" => 76719, "pod_id" => 11876, "product_id" => 1188, "qty_shipped" => 6, "stock_qty" => 4, "created_at" => "2022-09-02 19:41:31"],
        ["stock_d" => 76800, "pod_id" => 11882, "product_id" => 1315, "qty_shipped" => 12, "stock_qty" => 10, "created_at" => "2022-09-03 16:05:05"],
        ["stock_d" => 76900, "pod_id" => 11899, "product_id" => 1305, "qty_shipped" => 24, "stock_qty" => 10, "created_at" => "2022-09-05 15:22:14"],
        ["stock_d" => 77028, "pod_id" => 11922, "product_id" => 1324, "qty_shipped" => 120, "stock_qty" => 90, "created_at" => "2022-09-05 20:11:35"],
        ["stock_d" => 77037, "pod_id" => 11931, "product_id" => 1211, "qty_shipped" => 300, "stock_qty" => 240, "created_at" => "2022-09-05 20:11:35"],
        ["stock_d" => 77454, "pod_id" => 11960, "product_id" => 1211, "qty_shipped" => 300, "stock_qty" => 240, "created_at" => "2022-09-09 14:55:36"],
        ["stock_d" => 77455, "pod_id" => 11961, "product_id" => 564, "qty_shipped" => 60, "stock_qty" => 6, "created_at" => "2022-09-09 14:55:36"],
        ["stock_d" => 77655, "pod_id" => 11982, "product_id" => 1231, "qty_shipped" => 6, "stock_qty" => 4, "created_at" => "2022-09-12 15:55:15"],
        ["stock_d" => 77705, "pod_id" => 12002, "product_id" => 1324, "qty_shipped" => 60, "stock_qty" => 30, "created_at" => "2022-09-12 16:43:56"],
        ["stock_d" => 78166, "pod_id" => 12261, "product_id" => 760, "qty_shipped" => 3, "stock_qty" => 2, "created_at" => "2022-09-13 19:25:11"],
        ["stock_d" => 78170, "pod_id" => 12264, "product_id" => 1160, "qty_shipped" => 6, "stock_qty" => 1, "created_at" => "2022-09-13 19:25:11"],
        ["stock_d" => 78246, "pod_id" => 12311, "product_id" => 1097, "qty_shipped" => 10, "stock_qty" => 4, "created_at" => "2022-09-14 15:06:10"],
        ["stock_d" => 78306, "pod_id" => 12329, "product_id" => 1160, "qty_shipped" => 60, "stock_qty" => 54, "created_at" => "2022-09-14 16:07:50"],
        ["stock_d" => 78478, "pod_id" => 12340, "product_id" => 564, "qty_shipped" => 4, "stock_qty" => 1, "created_at" => "2022-09-16 15:43:06"],
        ["stock_d" => 78492, "pod_id" => 12349, "product_id" => 607, "qty_shipped" => 4, "stock_qty" => 3, "created_at" => "2022-09-16 15:49:39"],
        ["stock_d" => 78889, "pod_id" => 12394, "product_id" => 318, "qty_shipped" => 3, "stock_qty" => 2, "created_at" => "2022-09-19 22:28:17"],
        ["stock_d" => 78895, "pod_id" => 12399, "product_id" => 1186, "qty_shipped" => 13, "stock_qty" => 11, "created_at" => "2022-09-19 22:28:17"],
        ["stock_d" => 78978, "pod_id" => 12414, "product_id" => 564, "qty_shipped" => 240, "stock_qty" => 6, "created_at" => "2022-09-20 18:26:27"],
        ["stock_d" => 78980, "pod_id" => 12415, "product_id" => 1160, "qty_shipped" => 60, "stock_qty" => 54, "created_at" => "2022-09-20 18:26:27"],
        ["stock_d" => 79010, "pod_id" => 12426, "product_id" => 581, "qty_shipped" => 6, "stock_qty" => 1, "created_at" => "2022-09-20 20:05:35"],
        ["stock_d" => 79046, "pod_id" => 12433, "product_id" => 1123, "qty_shipped" => 6, "stock_qty" => 5, "created_at" => "2022-09-21 14:32:46"],
        ["stock_d" => 79098, "pod_id" => 12441, "product_id" => 324, "qty_shipped" => 5, "stock_qty" => 1, "created_at" => "2022-09-21 19:08:18"],
        ["stock_d" => 79185, "pod_id" => 12457, "product_id" => 959, "qty_shipped" => 12, "stock_qty" => 6, "created_at" => "2022-09-22 14:52:04"],
        ["stock_d" => 79242, "pod_id" => 12494, "product_id" => 1335, "qty_shipped" => 36, "stock_qty" => 10, "created_at" => "2022-09-22 15:19:02"],
        ["stock_d" => 79428, "pod_id" => 12520, "product_id" => 1088, "qty_shipped" => 12, "stock_qty" => 4, "created_at" => "2022-09-24 14:30:23"],
        ["stock_d" => 79597, "pod_id" => 12538, "product_id" => 1214, "qty_shipped" => 12, "stock_qty" => 5, "created_at" => "2022-09-26 15:49:46"],
        ["stock_d" => 79607, "pod_id" => 12547, "product_id" => 1176, "qty_shipped" => 12, "stock_qty" => 6, "created_at" => "2022-09-26 15:49:46"],
        ["stock_d" => 79736, "pod_id" => 12563, "product_id" => 959, "qty_shipped" => 12, "stock_qty" => 6, "created_at" => "2022-09-27 14:42:35"],
        ["stock_d" => 80090, "pod_id" => 12596, "product_id" => 1178, "qty_shipped" => 120, "stock_qty" => 90, "created_at" => "2022-09-30 17:03:05"],
        ["stock_d" => 80096, "pod_id" => 12602, "product_id" => 1226, "qty_shipped" => 240, "stock_qty" => 180, "created_at" => "2022-09-30 17:03:05"],
        ["stock_d" => 80383, "pod_id" => 12643, "product_id" => 1178, "qty_shipped" => 120, "stock_qty" => 90, "created_at" => "2022-10-03 16:29:30"],
        ["stock_d" => 80400, "pod_id" => 12648, "product_id" => 1214, "qty_shipped" => 12, "stock_qty" => 5, "created_at" => "2022-10-03 16:45:05"],
        ["stock_d" => 80404, "pod_id" => 12651, "product_id" => 1042, "qty_shipped" => 6, "stock_qty" => 3, "created_at" => "2022-10-03 16:45:05"],
        ["stock_d" => 80513, "pod_id" => 12678, "product_id" => 1220, "qty_shipped" => 4, "stock_qty" => 1, "created_at" => "2022-10-03 21:25:17"],
        ["stock_d" => 80641, "pod_id" => 12683, "product_id" => 1226, "qty_shipped" => 240, "stock_qty" => 180, "created_at" => "2022-10-04 18:12:08"],
        ["stock_d" => 81017, "pod_id" => 12749, "product_id" => 1210, "qty_shipped" => 2, "stock_qty" => 1, "created_at" => "2022-10-06 21:02:51"],
        ["stock_d" => 81115, "pod_id" => 12779, "product_id" => 1317, "qty_shipped" => 60, "stock_qty" => 54, "created_at" => "2022-10-07 14:57:22"],
        ["stock_d" => 81407, "pod_id" => 12808, "product_id" => 1342, "qty_shipped" => 12, "stock_qty" => 7, "created_at" => "2022-10-10 19:28:27"],
        ["stock_d" => 81604, "pod_id" => 12837, "product_id" => 1216, "qty_shipped" => 6, "stock_qty" => 5, "created_at" => "2022-10-11 20:52:43"],
        ["stock_d" => 81642, "pod_id" => 12839, "product_id" => 1226, "qty_shipped" => 6, "stock_qty" => 3, "created_at" => "2022-10-11 22:20:43"],
        ["stock_d" => 81685, "pod_id" => 12865, "product_id" => 1218, "qty_shipped" => 6, "stock_qty" => 4, "created_at" => "2022-10-11 22:56:54"],
        ["stock_d" => 81792, "pod_id" => 12883, "product_id" => 1114, "qty_shipped" => 3, "stock_qty" => 1, "created_at" => "2022-10-12 18:28:02"],
        ["stock_d" => 81906, "pod_id" => 12894, "product_id" => 1182, "qty_shipped" => 6, "stock_qty" => 1, "created_at" => "2022-10-14 15:27:32"],
        ["stock_d" => 81934, "pod_id" => 12901, "product_id" => 1182, "qty_shipped" => 6, "stock_qty" => 1, "created_at" => "2022-10-14 15:32:17"],
        ["stock_d" => 82102, "pod_id" => 12927, "product_id" => 564, "qty_shipped" => 240, "stock_qty" => 6, "created_at" => "2022-10-15 21:18:57"],
        ["stock_d" => 82146, "pod_id" => 12938, "product_id" => 1214, "qty_shipped" => 24, "stock_qty" => 19, "created_at" => "2022-10-17 14:41:46"],
        ["stock_d" => 82176, "pod_id" => 12952, "product_id" => 1317, "qty_shipped" => 60, "stock_qty" => 54, "created_at" => "2022-10-17 14:55:09"],
        ["stock_d" => 82240, "pod_id" => 12961, "product_id" => 1047, "qty_shipped" => 12, "stock_qty" => 1, "created_at" => "2022-10-17 16:04:02"],
        ["stock_d" => 82459, "pod_id" => 13013, "product_id" => 1219, "qty_shipped" => 7, "stock_qty" => 2, "created_at" => "2022-10-18 15:12:25"],
        ["stock_d" => 82506, "pod_id" => 13018, "product_id" => 1191, "qty_shipped" => 12, "stock_qty" => 3, "created_at" => "2022-10-18 17:48:56"],
        ["stock_d" => 82508, "pod_id" => 13019, "product_id" => 763, "qty_shipped" => 12, "stock_qty" => 3, "created_at" => "2022-10-18 17:48:56"],
        ["stock_d" => 82510, "pod_id" => 13020, "product_id" => 607, "qty_shipped" => 6, "stock_qty" => 3, "created_at" => "2022-10-18 17:48:56"],
        ["stock_d" => 83116, "pod_id" => 13065, "product_id" => 1220, "qty_shipped" => 2, "stock_qty" => 1, "created_at" => "2022-10-24 15:30:00"],
        ["stock_d" => 83238, "pod_id" => 13114, "product_id" => 1220, "qty_shipped" => 2, "stock_qty" => 1, "created_at" => "2022-10-24 17:51:55"],
        ["stock_d" => 83310, "pod_id" => 13118, "product_id" => 1335, "qty_shipped" => 24, "stock_qty" => 20, "created_at" => "2022-10-24 21:01:21"],
        ["stock_d" => 83478, "pod_id" => 13146, "product_id" => 564, "qty_shipped" => 180, "stock_qty" => 6, "created_at" => "2022-10-25 21:56:44"],
        ["stock_d" => 83500, "pod_id" => 13159, "product_id" => 1080, "qty_shipped" => 6, "stock_qty" => 2, "created_at" => "2022-10-25 22:05:16"],
        ["stock_d" => 83579, "pod_id" => 13174, "product_id" => 564, "qty_shipped" => 90, "stock_qty" => 6, "created_at" => "2022-10-26 20:00:54"],
        ["stock_d" => 83687, "pod_id" => 13178, "product_id" => 1088, "qty_shipped" => 6, "stock_qty" => 2, "created_at" => "2022-10-27 18:15:49"],
        ["stock_d" => 83726, "pod_id" => 13182, "product_id" => 1243, "qty_shipped" => 12, "stock_qty" => 6, "created_at" => "2022-10-27 20:10:48"],
        ["stock_d" => 83727, "pod_id" => 13183, "product_id" => 959, "qty_shipped" => 12, "stock_qty" => 9, "created_at" => "2022-10-27 20:10:48"],
        ["stock_d" => 83797, "pod_id" => 13187, "product_id" => 1210, "qty_shipped" => 180, "stock_qty" => 102, "created_at" => "2022-10-28 15:24:53"],
        ["stock_d" => 83804, "pod_id" => 13193, "product_id" => 1219, "qty_shipped" => 300, "stock_qty" => 240, "created_at" => "2022-10-28 15:24:53"],
        ["stock_d" => 83868, "pod_id" => 13197, "product_id" => 1210, "qty_shipped" => 6, "stock_qty" => 3, "created_at" => "2022-10-28 16:40:09"],
        ["stock_d" => 84002, "pod_id" => 13224, "product_id" => 1214, "qty_shipped" => 24, "stock_qty" => 15, "created_at" => "2022-10-28 22:53:27"],
        ["stock_d" => 84004, "pod_id" => 13225, "product_id" => 1211, "qty_shipped" => 24, "stock_qty" => 22, "created_at" => "2022-10-28 22:53:27"],
        ["stock_d" => 84009, "pod_id" => 13230, "product_id" => 1337, "qty_shipped" => 24, "stock_qty" => 14, "created_at" => "2022-10-28 22:53:27"],
        ["stock_d" => 84013, "pod_id" => 13233, "product_id" => 564, "qty_shipped" => 24, "stock_qty" => 9, "created_at" => "2022-10-28 22:53:27"],
        ["stock_d" => 84019, "pod_id" => 13239, "product_id" => 1087, "qty_shipped" => 48, "stock_qty" => 9, "created_at" => "2022-10-28 22:53:27"],
        ["stock_d" => 84026, "pod_id" => 13245, "product_id" => 1376, "qty_shipped" => 24, "stock_qty" => 8, "created_at" => "2022-10-28 22:53:27"],
        ["stock_d" => 84031, "pod_id" => 13248, "product_id" => 1048, "qty_shipped" => 24, "stock_qty" => 5, "created_at" => "2022-10-28 22:53:27"],
        ["stock_d" => 84035, "pod_id" => 13251, "product_id" => 1178, "qty_shipped" => 24, "stock_qty" => 6, "created_at" => "2022-10-28 22:53:27"],
        ["stock_d" => 84041, "pod_id" => 13256, "product_id" => 1276, "qty_shipped" => 24, "stock_qty" => 5, "created_at" => "2022-10-28 22:53:27"],
        ["stock_d" => 84044, "pod_id" => 13258, "product_id" => 1231, "qty_shipped" => 12, "stock_qty" => 5, "created_at" => "2022-10-28 22:53:27"],
        ["stock_d" => 84047, "pod_id" => 13260, "product_id" => 1141, "qty_shipped" => 12, "stock_qty" => 9, "created_at" => "2022-10-28 22:53:27"],
        ["stock_d" => 84145, "pod_id" => 13262, "product_id" => 1228, "qty_shipped" => 24, "stock_qty" => 6, "created_at" => "2022-10-29 19:50:07"],
        ["stock_d" => 84148, "pod_id" => 13263, "product_id" => 1219, "qty_shipped" => 24, "stock_qty" => 7, "created_at" => "2022-10-29 19:50:07"],
        ["stock_d" => 84151, "pod_id" => 13264, "product_id" => 1210, "qty_shipped" => 24, "stock_qty" => 4, "created_at" => "2022-10-29 19:50:07"],
        ["stock_d" => 84154, "pod_id" => 13266, "product_id" => 1214, "qty_shipped" => 24, "stock_qty" => 5, "created_at" => "2022-10-29 19:50:07"],
        ["stock_d" => 84158, "pod_id" => 13267, "product_id" => 1211, "qty_shipped" => 24, "stock_qty" => 8, "created_at" => "2022-10-29 19:50:07"],
        ["stock_d" => 84161, "pod_id" => 13269, "product_id" => 1099, "qty_shipped" => 12, "stock_qty" => 5, "created_at" => "2022-10-29 19:50:07"],
        ["stock_d" => 84164, "pod_id" => 13270, "product_id" => 1126, "qty_shipped" => 6, "stock_qty" => 3, "created_at" => "2022-10-29 19:50:07"],
        ["stock_d" => 84166, "pod_id" => 13271, "product_id" => 1273, "qty_shipped" => 12, "stock_qty" => 2, "created_at" => "2022-10-29 19:50:07"],
        ["stock_d" => 84169, "pod_id" => 13272, "product_id" => 1337, "qty_shipped" => 24, "stock_qty" => 1, "created_at" => "2022-10-29 19:50:07"],
        ["stock_d" => 84173, "pod_id" => 13274, "product_id" => 1252, "qty_shipped" => 12, "stock_qty" => 6, "created_at" => "2022-10-29 19:50:07"],
        ["stock_d" => 84175, "pod_id" => 13275, "product_id" => 564, "qty_shipped" => 24, "stock_qty" => 2, "created_at" => "2022-10-29 19:50:07"],
        ["stock_d" => 84178, "pod_id" => 13277, "product_id" => 566, "qty_shipped" => 24, "stock_qty" => 14, "created_at" => "2022-10-29 19:50:07"],
        ["stock_d" => 84179, "pod_id" => 13278, "product_id" => 1160, "qty_shipped" => 12, "stock_qty" => 3, "created_at" => "2022-10-29 19:50:07"],
        ["stock_d" => 84182, "pod_id" => 13279, "product_id" => 749, "qty_shipped" => 24, "stock_qty" => 21, "created_at" => "2022-10-29 19:50:07"],
        ["stock_d" => 84184, "pod_id" => 13280, "product_id" => 748, "qty_shipped" => 24, "stock_qty" => 16, "created_at" => "2022-10-29 19:50:07"],
        ["stock_d" => 84186, "pod_id" => 13281, "product_id" => 1087, "qty_shipped" => 48, "stock_qty" => 9, "created_at" => "2022-10-29 19:50:07"],
        ["stock_d" => 84189, "pod_id" => 13282, "product_id" => 1088, "qty_shipped" => 24, "stock_qty" => 9, "created_at" => "2022-10-29 19:50:08"],
        ["stock_d" => 84192, "pod_id" => 13283, "product_id" => 1186, "qty_shipped" => 36, "stock_qty" => 10, "created_at" => "2022-10-29 19:50:08"],
        ["stock_d" => 84195, "pod_id" => 13284, "product_id" => 1079, "qty_shipped" => 24, "stock_qty" => 8, "created_at" => "2022-10-29 19:50:08"],
        ["stock_d" => 84197, "pod_id" => 13285, "product_id" => 1080, "qty_shipped" => 24, "stock_qty" => 21, "created_at" => "2022-10-29 19:50:08"],
        ["stock_d" => 84199, "pod_id" => 13286, "product_id" => 1381, "qty_shipped" => 24, "stock_qty" => 4, "created_at" => "2022-10-29 19:50:08"],
        ["stock_d" => 84201, "pod_id" => 13287, "product_id" => 1376, "qty_shipped" => 24, "stock_qty" => 2, "created_at" => "2022-10-29 19:50:08"],
        ["stock_d" => 84205, "pod_id" => 13288, "product_id" => 1299, "qty_shipped" => 12, "stock_qty" => 2, "created_at" => "2022-10-29 19:50:08"],
        ["stock_d" => 84207, "pod_id" => 13289, "product_id" => 1092, "qty_shipped" => 12, "stock_qty" => 9, "created_at" => "2022-10-29 19:50:08"],
        ["stock_d" => 84210, "pod_id" => 13291, "product_id" => 1169, "qty_shipped" => 12, "stock_qty" => 6, "created_at" => "2022-10-29 19:50:08"],
        ["stock_d" => 84213, "pod_id" => 13293, "product_id" => 1178, "qty_shipped" => 24, "stock_qty" => 6, "created_at" => "2022-10-29 19:50:08"],
        ["stock_d" => 84216, "pod_id" => 13295, "product_id" => 845, "qty_shipped" => 12, "stock_qty" => 3, "created_at" => "2022-10-29 19:50:08"],
        ["stock_d" => 84218, "pod_id" => 13296, "product_id" => 1176, "qty_shipped" => 12, "stock_qty" => 4, "created_at" => "2022-10-29 19:50:08"],
        ["stock_d" => 84220, "pod_id" => 13297, "product_id" => 892, "qty_shipped" => 12, "stock_qty" => 6, "created_at" => "2022-10-29 19:50:08"],
        ["stock_d" => 84224, "pod_id" => 13300, "product_id" => 1231, "qty_shipped" => 12, "stock_qty" => 8, "created_at" => "2022-10-29 19:50:08"],
        ["stock_d" => 84226, "pod_id" => 13301, "product_id" => 739, "qty_shipped" => 12, "stock_qty" => 6, "created_at" => "2022-10-29 19:50:08"],
        ["stock_d" => 84228, "pod_id" => 13302, "product_id" => 1141, "qty_shipped" => 12, "stock_qty" => 6, "created_at" => "2022-10-29 19:50:08"],
        ["stock_d" => 84267, "pod_id" => 13312, "product_id" => 1186, "qty_shipped" => 12, "stock_qty" => 11, "created_at" => "2022-10-29 21:30:55"],
        ["stock_d" => 84276, "pod_id" => 13320, "product_id" => 1261, "qty_shipped" => 12, "stock_qty" => 3, "created_at" => "2022-10-29 21:30:55"],
        ["stock_d" => 84282, "pod_id" => 13325, "product_id" => 1141, "qty_shipped" => 12, "stock_qty" => 6, "created_at" => "2022-10-29 21:30:55"],
        ["stock_d" => 84377, "pod_id" => 13335, "product_id" => 1095, "qty_shipped" => 90, "stock_qty" => 30, "created_at" => "2022-10-31 15:43:17"],
        ["stock_d" => 84381, "pod_id" => 13338, "product_id" => 1166, "qty_shipped" => 54, "stock_qty" => 24, "created_at" => "2022-10-31 15:43:17"],
        ["stock_d" => 84393, "pod_id" => 13349, "product_id" => 1240, "qty_shipped" => 90, "stock_qty" => 30, "created_at" => "2022-10-31 15:43:17"],
        ["stock_d" => 84474, "pod_id" => 13376, "product_id" => 811, "qty_shipped" => 16, "stock_qty" => 10, "created_at" => "2022-10-31 23:23:00"],
        ["stock_d" => 84476, "pod_id" => 13377, "product_id" => 599, "qty_shipped" => 12, "stock_qty" => 6, "created_at" => "2022-10-31 23:23:00"],
        ["stock_d" => 84479, "pod_id" => 13379, "product_id" => 581, "qty_shipped" => 9, "stock_qty" => 7, "created_at" => "2022-10-31 23:23:00"],
        ["stock_d" => 84484, "pod_id" => 13383, "product_id" => 743, "qty_shipped" => 9, "stock_qty" => 3, "created_at" => "2022-10-31 23:23:00"],
        ["stock_d" => 84671, "pod_id" => 13412, "product_id" => 541, "qty_shipped" => 26, "stock_qty" => 20, "created_at" => "2022-11-01 17:27:59"],
        ["stock_d" => 84672, "pod_id" => 13413, "product_id" => 744, "qty_shipped" => 7, "stock_qty" => 1, "created_at" => "2022-11-01 17:27:59"],
        ["stock_d" => 84674, "pod_id" => 13414, "product_id" => 918, "qty_shipped" => 13, "stock_qty" => 1, "created_at" => "2022-11-01 17:27:59"],
        ["stock_d" => 84874, "pod_id" => 13452, "product_id" => 1055, "qty_shipped" => 12, "stock_qty" => 6, "created_at" => "2022-11-02 16:17:29"],
        ["stock_d" => 84894, "pod_id" => 13453, "product_id" => 1376, "qty_shipped" => 5, "stock_qty" => 4, "created_at" => "2022-11-02 16:40:42"],
        ["stock_d" => 84896, "pod_id" => 13454, "product_id" => 1381, "qty_shipped" => 5, "stock_qty" => 3, "created_at" => "2022-11-02 16:40:42"],
        ["stock_d" => 84958, "pod_id" => 13465, "product_id" => 748, "qty_shipped" => 12, "stock_qty" => 3, "created_at" => "2022-11-02 21:47:13"],
        ["stock_d" => 84964, "pod_id" => 13470, "product_id" => 1042, "qty_shipped" => 12, "stock_qty" => 4, "created_at" => "2022-11-02 21:47:13"],
        ["stock_d" => 84968, "pod_id" => 13474, "product_id" => 572, "qty_shipped" => 30, "stock_qty" => 10, "created_at" => "2022-11-02 21:47:13"],
        ["stock_d" => 85060, "pod_id" => 13479, "product_id" => 1219, "qty_shipped" => 300, "stock_qty" => 60, "created_at" => "2022-11-03 16:07:21"],
        ["stock_d" => 85066, "pod_id" => 13484, "product_id" => 1255, "qty_shipped" => 210, "stock_qty" => 90, "created_at" => "2022-11-03 16:07:21"],
        ["stock_d" => 85289, "pod_id" => 13506, "product_id" => 1048, "qty_shipped" => 180, "stock_qty" => 60, "created_at" => "2022-11-05 16:00:09"],
        ["stock_d" => 85362, "pod_id" => 13511, "product_id" => 1228, "qty_shipped" => 24, "stock_qty" => 8, "created_at" => "2022-11-05 20:14:56"],
        ["stock_d" => 85365, "pod_id" => 13512, "product_id" => 1216, "qty_shipped" => 48, "stock_qty" => 23, "created_at" => "2022-11-05 20:14:56"],
        ["stock_d" => 85370, "pod_id" => 13517, "product_id" => 1338, "qty_shipped" => 24, "stock_qty" => 22, "created_at" => "2022-11-05 20:14:56"],
        ["stock_d" => 85389, "pod_id" => 13535, "product_id" => 1275, "qty_shipped" => 24, "stock_qty" => 13, "created_at" => "2022-11-05 20:14:56"],
        ["stock_d" => 85393, "pod_id" => 13538, "product_id" => 1315, "qty_shipped" => 6, "stock_qty" => 4, "created_at" => "2022-11-05 20:14:56"],
        ["stock_d" => 85427, "pod_id" => 13539, "product_id" => 1079, "qty_shipped" => 360, "stock_qty" => 54, "created_at" => "2022-11-07 14:35:20"],
        ["stock_d" => 85435, "pod_id" => 13546, "product_id" => 325, "qty_shipped" => 402, "stock_qty" => 246, "created_at" => "2022-11-07 14:35:20"],
        ["stock_d" => 85437, "pod_id" => 13547, "product_id" => 1228, "qty_shipped" => 120, "stock_qty" => 60, "created_at" => "2022-11-07 14:35:20"],
        ["stock_d" => 85832, "pod_id" => 13614, "product_id" => 325, "qty_shipped" => 300, "stock_qty" => 246, "created_at" => "2022-11-09 14:41:33"],
        ["stock_d" => 85834, "pod_id" => 13615, "product_id" => 1219, "qty_shipped" => 90, "stock_qty" => 60, "created_at" => "2022-11-09 14:41:33"],
        ["stock_d" => 85853, "pod_id" => 13618, "product_id" => 1376, "qty_shipped" => 5, "stock_qty" => 4, "created_at" => "2022-11-09 16:02:26"],
        ["stock_d" => 85855, "pod_id" => 13619, "product_id" => 1381, "qty_shipped" => 5, "stock_qty" => 3, "created_at" => "2022-11-09 16:02:26"],
        ["stock_d" => 85857, "pod_id" => 13620, "product_id" => 763, "qty_shipped" => 5, "stock_qty" => 3, "created_at" => "2022-11-09 16:02:26"],
        ["stock_d" => 85873, "pod_id" => 13622, "product_id" => 1265, "qty_shipped" => 4, "stock_qty" => 1, "created_at" => "2022-11-09 16:56:09"],
        ["stock_d" => 85963, "pod_id" => 13649, "product_id" => 1226, "qty_shipped" => 17, "stock_qty" => 3, "created_at" => "2022-11-09 19:19:22"],
        ["stock_d" => 85967, "pod_id" => 13650, "product_id" => 1210, "qty_shipped" => 15, "stock_qty" => 6, "created_at" => "2022-11-09 19:19:22"],
        ["stock_d" => 85970, "pod_id" => 13651, "product_id" => 1215, "qty_shipped" => 2, "stock_qty" => 1, "created_at" => "2022-11-09 19:19:22"],
        ["stock_d" => 85972, "pod_id" => 13652, "product_id" => 1211, "qty_shipped" => 16, "stock_qty" => 8, "created_at" => "2022-11-09 19:19:22"],
        ["stock_d" => 85975, "pod_id" => 13653, "product_id" => 1295, "qty_shipped" => 17, "stock_qty" => 2, "created_at" => "2022-11-09 19:19:22"],
        ["stock_d" => 85979, "pod_id" => 13654, "product_id" => 1379, "qty_shipped" => 17, "stock_qty" => 6, "created_at" => "2022-11-09 19:19:22"],
        ["stock_d" => 85982, "pod_id" => 13655, "product_id" => 1305, "qty_shipped" => 9, "stock_qty" => 3, "created_at" => "2022-11-09 19:19:22"],
        ["stock_d" => 85984, "pod_id" => 13656, "product_id" => 763, "qty_shipped" => 18, "stock_qty" => 4, "created_at" => "2022-11-09 19:19:22"],
        ["stock_d" => 85988, "pod_id" => 13657, "product_id" => 1193, "qty_shipped" => 2, "stock_qty" => 1, "created_at" => "2022-11-09 19:19:22"],
        ["stock_d" => 85990, "pod_id" => 13658, "product_id" => 1217, "qty_shipped" => 15, "stock_qty" => 6, "created_at" => "2022-11-09 19:19:22"],
        ["stock_d" => 85995, "pod_id" => 13659, "product_id" => 1218, "qty_shipped" => 12, "stock_qty" => 1, "created_at" => "2022-11-09 19:19:22"],
        ["stock_d" => 86001, "pod_id" => 13660, "product_id" => 1042, "qty_shipped" => 14, "stock_qty" => 6, "created_at" => "2022-11-09 19:19:22"],
        ["stock_d" => 86006, "pod_id" => 13662, "product_id" => 1240, "qty_shipped" => 16, "stock_qty" => 3, "created_at" => "2022-11-09 19:19:22"],
        ["stock_d" => 86011, "pod_id" => 13664, "product_id" => 1168, "qty_shipped" => 16, "stock_qty" => 1, "created_at" => "2022-11-09 19:19:22"],
        ["stock_d" => 86016, "pod_id" => 13665, "product_id" => 1293, "qty_shipped" => 13, "stock_qty" => 3, "created_at" => "2022-11-09 19:19:22"],
        ["stock_d" => 86019, "pod_id" => 13666, "product_id" => 1087, "qty_shipped" => 16, "stock_qty" => 6, "created_at" => "2022-11-09 19:19:22"],
        ["stock_d" => 86036, "pod_id" => 13667, "product_id" => 526, "qty_shipped" => 2, "stock_qty" => 1, "created_at" => "2022-11-09 20:22:45"],
        ["stock_d" => 86038, "pod_id" => 13668, "product_id" => 754, "qty_shipped" => 4, "stock_qty" => 2, "created_at" => "2022-11-09 20:22:45"],
        ["stock_d" => 86080, "pod_id" => 13670, "product_id" => 564, "qty_shipped" => 90, "stock_qty" => 6, "created_at" => "2022-11-10 15:00:29"],
        ["stock_d" => 86117, "pod_id" => 13673, "product_id" => 841, "qty_shipped" => 3, "stock_qty" => 2, "created_at" => "2022-11-10 16:16:12"],
        ["stock_d" => 86293, "pod_id" => 13683, "product_id" => 1216, "qty_shipped" => 36, "stock_qty" => 16, "created_at" => "2022-11-11 15:24:37"],
        ["stock_d" => 86296, "pod_id" => 13686, "product_id" => 1370, "qty_shipped" => 6, "stock_qty" => 4, "created_at" => "2022-11-11 15:24:37"],
        ["stock_d" => 86561, "pod_id" => 13762, "product_id" => 1231, "qty_shipped" => 90, "stock_qty" => 60, "created_at" => "2022-11-11 21:22:03"],
        ["stock_d" => 86620, "pod_id" => 13807, "product_id" => 1366, "qty_shipped" => 16, "stock_qty" => 10, "created_at" => "2022-11-11 22:55:02"],
        ["stock_d" => 86636, "pod_id" => 13812, "product_id" => 1095, "qty_shipped" => 36, "stock_qty" => 30, "created_at" => "2022-11-12 14:30:45"],
        ["stock_d" => 86765, "pod_id" => 13820, "product_id" => 1342, "qty_shipped" => 12, "stock_qty" => 2, "created_at" => "2022-11-14 15:26:53"],
        ["stock_d" => 86929, "pod_id" => 13950, "product_id" => 1246, "qty_shipped" => 12, "stock_qty" => 7, "created_at" => "2022-11-14 22:31:58"],
        ["stock_d" => 87116, "pod_id" => 13974, "product_id" => 565, "qty_shipped" => 4, "stock_qty" => 2, "created_at" => "2022-11-15 16:58:50"],
        ["stock_d" => 87153, "pod_id" => 13978, "product_id" => 1250, "qty_shipped" => 6, "stock_qty" => 2, "created_at" => "2022-11-15 19:17:47"],
        ["stock_d" => 87155, "pod_id" => 13979, "product_id" => 1249, "qty_shipped" => 3, "stock_qty" => 2, "created_at" => "2022-11-15 19:17:47"],
        ["stock_d" => 87178, "pod_id" => 13980, "product_id" => 328, "qty_shipped" => 48, "stock_qty" => 38, "created_at" => "2022-11-15 20:01:58"],
        ["stock_d" => 87194, "pod_id" => 13985, "product_id" => 335, "qty_shipped" => 2, "stock_qty" => 1, "created_at" => "2022-11-15 20:23:06"],
        ["stock_d" => 87198, "pod_id" => 13988, "product_id" => 1135, "qty_shipped" => 2, "stock_qty" => 1, "created_at" => "2022-11-15 20:23:06"],
        ["stock_d" => 87200, "pod_id" => 13989, "product_id" => 782, "qty_shipped" => 4, "stock_qty" => 2, "created_at" => "2022-11-15 20:23:06"],
        ["stock_d" => 87204, "pod_id" => 13991, "product_id" => 754, "qty_shipped" => 4, "stock_qty" => 2, "created_at" => "2022-11-15 20:23:06"],
        ["stock_d" => 87207, "pod_id" => 13992, "product_id" => 833, "qty_shipped" => 4, "stock_qty" => 1, "created_at" => "2022-11-15 20:23:06"],
        ["stock_d" => 87257, "pod_id" => 13997, "product_id" => 1219, "qty_shipped" => 180, "stock_qty" => 30, "created_at" => "2022-11-16 15:09:19"],
        ["stock_d" => 87260, "pod_id" => 13999, "product_id" => 564, "qty_shipped" => 90, "stock_qty" => 60, "created_at" => "2022-11-16 15:09:19"],
        ["stock_d" => 87306, "pod_id" => 14014, "product_id" => 873, "qty_shipped" => 42, "stock_qty" => 6, "created_at" => "2022-11-16 15:33:12"],
        ["stock_d" => 87308, "pod_id" => 14016, "product_id" => 1390, "qty_shipped" => 24, "stock_qty" => 22, "created_at" => "2022-11-16 15:33:12"],
        ["stock_d" => 87312, "pod_id" => 14019, "product_id" => 1262, "qty_shipped" => 24, "stock_qty" => 16, "created_at" => "2022-11-16 15:33:12"],
    ];
    $count = 0;
    foreach ($array as $item) {
        $stock_out = StockManagementOut::find($item['stock_d']);
        if ($stock_out) {
            $stock_out->quantity_in = $item['stock_qty'];
            $stock_out->save();
            $count++;
        }
    }
    dd($count);
    return 'success';
});


Route::get('get_mismatch_stock_products', function () {
    $array = [];
    $warehouse_products = WarehouseProduct::with('getWarehouse')->get();
    foreach ($warehouse_products as $wp) {
        $qty_in = StockManagementOut::where('product_id', $wp->product_id)->where('warehouse_id', $wp->warehouse_id)->sum('quantity_in');
        $qty_out = StockManagementOut::where('product_id', $wp->product_id)->where('warehouse_id', $wp->warehouse_id)->sum('quantity_out');
        $stock_qty = round($qty_in + $qty_out, 3);
        $wp_qty = round($wp->current_quantity, 3);

        if ($stock_qty != $wp_qty) {
            $product = Product::find($wp->product_id);
            $data = 'Product: ' . $product->refrence_code . ', Warehouse:  ' . $wp->getWarehouse->warehouse_title . ', Warehouse Wrong Stock: ' . $wp_qty . ', Actual Stock: ' . $stock_qty;
            array_push($array, $data);
        }
    }

    dd($array);
});
Route::get('/simple-excel',function(){
	return \Storage::disk('public')->download('export.csv');
	});
Route::get('/invoice-with-quantity-difference',function(){
	$invoices = OrderProduct::whereHas('get_order',function($or){
		$or->where('primary_status',3);
	})->get();
	$ids = [];
	$html = '<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>Order Product ID</th>
		      			<th>Order ID</th>
		      			<th>Product ID</th>
		      			<th>Qty shipped</th>
		      			<th>Qty Stock</th>
		      		</tr>
		      	</thead>
		      	<tbody>';
	foreach ($invoices as $op) {
		$stock_out_out = StockManagementOut::where('order_product_id', $op->id)->whereNull('quantity_in')->sum('quantity_out');

		$stock_out_in = StockManagementOut::where('order_product_id', $op->id)->whereNull('quantity_out')->sum('quantity_in');

		$stock_out_out = abs(@$stock_out_out);
		$stock_out_in = abs(@$stock_out_in);

		if($op->qty_shipped != round($stock_out_out+$stock_out_in,4)){
			array_push($ids, $op->id);

			$html .= '<tr>
						<td>'.$op->id.'</td>
						<td>'.$op->order_id.'</td>
						<td>'.$op->product_id.'</td>
						<td>'.$op->qty_shipped.'</td>
						<td>'.round(@$stock_out_in+@$stock_out_out).'</td>
			</tr>';
		}
	}

	$html .= '</body></table>';

	return $html;
});
Route::get('/set-invoice-prices/{order_id}',function($id){
	$order = Order::find($id);
	$order_products = OrderProduct::where('order_id', $id)->where('is_billed', '=', 'Product')->get();
    $order_products_billed = OrderProduct::where('order_id', $id)->where('is_billed', '=', 'Billed')->get();
    $order_total = 0;
	foreach ($order_products as $order_product) {
		$item_unit_price = number_format($order_product->unit_price, 2, '.', '');
		if ($order_product->is_retail == 'qty') {
                    $total_price = $item_unit_price * $order_product->qty_shipped;
                    $num = $order_product->qty_shipped;
                } else if ($order_product->is_retail == 'pieces') {
                    $total_price = $item_unit_price * $order_product->pcs_shipped;
                    $num = $order_product->pcs_shipped;
                }
                $discount = $order_product->discount;

                if ($discount != null) {
                    $dis = $discount / 100;
                    $discount_value = $dis * $total_price;
                    $result = $total_price - $discount_value;
                } else {
                    $result = $total_price;
                }

                $order_product->total_price = round($result, 2);

                $vat = $order_product->vat;
               
                $vat_amountt = @$item_unit_price * (@$vat / 100);
                $vat_amount = number_format($vat_amountt, 4, '.', '');
                $vat_amount_total_over_item = $vat_amount * $num;
                $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                    $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                } else {
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
				$vat = $order_product->vat;
                $vat_amountt = @$item_unit_price * (@$vat / 100);
                $vat_amount = number_format($vat_amountt, 4, '.', '');
                $vat_amount_total_over_item = $vat_amount * $num;
                $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                    $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                } else {
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

    $order->total_amount = $order_total;
    $order->save();
	});

	Route::get('/check-transfer-mismatch-issue',function(){
		$tds = PurchaseOrderDetail::whereHas('PurchaseOrder',function($po){
			$po->where('status',22);
		})->with('stock_management_out','stock_management_in')->orderBy('id','desc')->get();
		$html = '<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>Po ID</th>
		      			<th>Pod ID</th>
		      			<th>Sent</th>
		      			<th>Receive</th>
		      		</tr>
		      	</thead>
		      	<tbody>';
		foreach ($tds as $td) {
			$stock_out = $td->stock_management_out->count() > 0 ? $td->stock_management_out->sum('quantity_out') : null;
			$stock_in = $td->stock_management_in->count() > 0 ? $td->stock_management_in->sum('quantity_in') : null;

			if(abs($stock_out) != abs($stock_in)){
				$html .= '<tr>
						<td>'.$td->po_id.'</td>
						<td>'.$td->id.'</td>
						<td>'.abs($stock_out).'</td>
						<td>'.abs($stock_in).'</td>
			</tr>';
			}
		}
		$html .= '</body></table>';

		return $html;
	});

	Route::get('/check-transfer-mismatch-issue-received',function(){
		$tds = PurchaseOrderDetail::whereHas('PurchaseOrder',function($po){
			$po->where('status',22);
		})->with('stock_management_in')->orderBy('id','desc')->get();
		$html = '<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>Po ID</th>
		      			<th>Pod ID</th>
		      			<th>Received</th>
		      			<th>Received Actually</th>
		      		</tr>
		      	</thead>
		      	<tbody>';
		foreach ($tds as $td) {
			$stock_in = $td->stock_management_in->count() > 0 ? $td->stock_management_in->sum('quantity_in') : null;
			$stock_in = round($stock_in,4);

			$stock = abs($td->quantity_received) + abs($td->quantity_received_2);
			$stock = round($stock, 4);

			if((abs($stock) != abs($stock_in)) && $stock != null && $stock_in != null){
				$html .= '<tr>
						<td>'.$td->po_id.'</td>
						<td>'.$td->id.'</td>
						<td>'.abs($stock).'</td>
						<td>'.abs($stock_in).'</td>
			</tr>';
			}
		}
		$html .= '</body></table>';

		return $html;
	});

	Route::get('/check-transfer-mismatch-issue-received-solve',function(){
		$tds = PurchaseOrderDetail::whereHas('PurchaseOrder',function($po){
			$po->where('status',22);
		})->with('stock_management_in')->orderBy('id','desc')->get();
		$html = '<table style="width:100%;text-align:center;">
		      	<thead>
		      		<tr>
		      			<th>Po ID</th>
		      			<th>Pod ID</th>
		      			<th>Received</th>
		      			<th>Received Actually</th>
		      		</tr>
		      	</thead>
		      	<tbody>';
		foreach ($tds as $td) {
			$stock_in = $td->stock_management_in->count() > 0 ? $td->stock_management_in->sum('quantity_in') : null;
			$stock_in = round($stock_in,4);
			
			$stock = abs($td->quantity_received) + abs($td->quantity_received_2);
			$stock = round($stock, 4);

			if((abs($stock) != abs($stock_in)) && $stock != null && $stock_in != null){
				$check = TransferDocumentReservedQuantity::where('pod_id', $td->id)->first();
				if($check != null){
					if(abs($stock) > abs($stock_in)){
						$diff = abs($stock) - abs($stock_in);
						$rec = StockManagementOut::where('p_o_d_id', $td->id)->whereNull('quantity_out')->first();
						if($rec){
							$rec->quantity_in = $rec->quantity_in + $diff;
							$rec->available_stock = $rec->available_stock + $diff;
							$rec->save();
						}
					}else if(abs($stock) < abs($stock_in)){
						$diff = abs($stock_in) - abs($stock);
						$rec = StockManagementOut::where('p_o_d_id', $td->id)->where('quantity_in','>',$diff)->whereNull('quantity_out')->first();
						if($rec){
							$rec->quantity_in = $rec->quantity_in - $diff;
							$rec->available_stock = $rec->available_stock - $diff;
							$rec->save();
						}
					}
				}
			}
		}
		$html .= '</body></table>';

		return $html;
	});

	Route::get('/set-lucilla-invoices',function(){
		$orders = Order::where('in_ref_id','>',23011593)->whereNull('ref_prefix')->with('order_products')->get();

		ScriptHandlerJob::dispatch($orders);

		// foreach ($orders as $or) {
		// 	foreach ($or->order_products as $op) {
		// 		$op->total_price_with_vat = round($op->total_price + $op->vat_amount_total,4);
		// 		$op->save();
		// 	}
		// 	$or->ref_prefix = 1;
		// 	$or->total_amount = round($or->order_products->sum('total_price_with_vat'),2);
		// 	$or->save();
		// }

		return response()->json(['success' => 'job dispatched']);
	});

	Route::get('/set_order_hi',function(){
		$data = OrderStatusHistory::whereIn('order_id', [3569,3389, 3378, 3439, 3386, 3499, 3435, 3532, 3531, 3533, 3418, 3415, 3422, 3079, 3500, 3567, 3238, 3072, 3491, 3597, 3430, 3432, 3113, 3620, 3483, 3618, 3436, 3641])->orderBy('id','asc')->get();
		// dd($data);
		$old_id = 14253;
		foreach ($data as $value) {
			$old_id++;
			$value->id = $old_id;
			$value->save();
		}
		dd('updated');
	});
?>
