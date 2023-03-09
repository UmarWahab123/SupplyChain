<?php

namespace App\Imports;

use App\Jobs\ProductsReceivingImportJob;
use App\Models\Common\PoGroup;
use App\Models\Common\PoGroupDetail;
use App\Models\Common\PoGroupProductDetail;
use App\Models\Common\Product;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PurchaseOrdersHistory;
use App\Models\Common\SupplierProducts;
use App\ProductHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\ToModel;
class BulkProductImportInGroupDetail implements ToCollection ,WithStartRow, WithHeadingRow
{
	public $group_id;
	public $user_id;
    /**
    * @param Collection $collection
    */

    public function __construct($group_id,$user_id)
    {
        $this->group_id = $group_id;
        $this->user_id = $user_id;
    }

    public function collection(Collection $rows)
    {
    	$user_id=$this->user_id;
    	$group_id=$this->group_id;
    	$error = 0;
        // dd($rows);

        $result = ProductsReceivingImportJob::dispatch($rows,$user_id,$group_id);

        // dd($result);
        // if($rows->count() > 1)
        // {
        // 	$row1 = $rows->toArray();
        //     $remove = array_shift($row1);

        //     $html_string = '<ol>';
        //     $increment = 1;
        //     foreach ($row1 as $row) {
        //     	// dd($row);
        //         $pod_id = intval($row[29]);
        //         $pogpd_id = intval($row[30]);
        //         $po_id = intval($row[31]);

        //     	if($row[16] != null)
        //         {
        //             if(!is_numeric($row[16]))
        //             {
        //                 $row[16] = null;
        //                 $error = 1;
        //                 $html_string .= '<li>Enter Valid Unit Price For Product <b>'.$row[5].'</b></li>';
        //             }
        //         }

        //         if($row[16] != null)
        //         {
        //         	$checkSameProduct = PurchaseOrderDetail::find($pod_id);
        //         	$old_value = $checkSameProduct->pod_unit_price;
        //         	if($old_value != $row[16])
        //         	{
				    //     $po = PurchaseOrder::find($po_id);
				    //     if($checkSameProduct->is_billed == "Product")
				    //     {
				       
				    //                 if($po->exchange_rate == null)
				    //                 {
				    //                     $supplier_conv_rate_thb = $po->PoSupplier->getCurrency->conversion_rate;
				    //                 }
				    //                 else
				    //                 {
				    //                     $supplier_conv_rate_thb = $po->exchange_rate;
				    //                 }
				    //                 $checkSameProduct->pod_unit_price = $row[16];
				    //                 $checkSameProduct->last_updated_price_on = date('Y-m-d');
				    //                 $checkSameProduct->pod_total_unit_price = ($row[16] * $checkSameProduct->quantity);

				    //                 $calculations = $checkSameProduct->total_unit_price_in_thb * ($checkSameProduct->pod_import_tax_book / 100);
				    //                 $checkSameProduct->pod_import_tax_book_price = $calculations;
				    //                 $checkSameProduct->save();

				    //                 //To calculate values in THB
				    //                 $checkSameProduct->unit_price_in_thb         = $checkSameProduct->pod_unit_price/$supplier_conv_rate_thb;
				    //                 $checkSameProduct->total_unit_price_in_thb   = $checkSameProduct->pod_total_unit_price/$supplier_conv_rate_thb;
				    //                 $checkSameProduct->pod_import_tax_book_price = ($checkSameProduct->pod_import_tax_book/100)*$checkSameProduct->total_unit_price_in_thb;
				    //                 $checkSameProduct->save();
				    //         //     }
				    //         // }

				    //         if($po->status >= 14)
				    //         {
				    //             $checkSameProduct = PurchaseOrderDetail::find($pod_id);
				    //             if($checkSameProduct->product_id != null)
				    //             {
				    //                 if($checkSameProduct->PurchaseOrder->supplier_id != null && $checkSameProduct->PurchaseOrder->from_warehouse_id == null)
				    //                 {
				    //                     $supplier_id = $checkSameProduct->PurchaseOrder->supplier_id;
				    //                 }
				    //                 else
				    //                 {
				    //                     $supplier_id = $checkSameProduct->product->supplier_id;
				    //                 }

				    //                 if($checkSameProduct->PurchaseOrder->exchange_rate != NULL)
				    //                 {
				    //                     $supplier_conv_rate_thb = $checkSameProduct->PurchaseOrder->exchange_rate;
				    //                 }
				    //                 else
				    //                 {
				    //                     $supplier_conv_rate_thb = $checkSameProduct->PurchaseOrder->PoSupplier->getCurrency->conversion_rate;
				    //                 }

				    //                 if($checkSameProduct->discount !== null)
				    //                 {
				    //                     $discount_price = $checkSameProduct->quantity * $row[16] - (($checkSameProduct->quantity * $row[16]) * ($checkSameProduct->discount / 100));
				    //                     if($checkSameProduct->quantity != 0 && $checkSameProduct->quantity != null)
				    //                     {
				    //                         $after_discount_price = ($discount_price / $checkSameProduct->quantity);
				    //                     }
				    //                     else
				    //                     {
				    //                         $after_discount_price = $discount_price;
				    //                     }
				    //                     $unit_price = $after_discount_price;
				    //                 }
				    //                 else
				    //                 {
				    //                     $unit_price = $row[16];
				    //                 }

				    //                 if($checkSameProduct->discount < 100 || $checkSameProduct->discount == null)
				    //                 {
				    //                     $getProductSupplier = SupplierProducts::where('product_id',$checkSameProduct->product_id)->where('supplier_id',$supplier_id)->first();
				    //                     $old_price_value = $getProductSupplier->buying_price;

				    //                     $getProductSupplier->buying_price = $unit_price;
				    //                     $getProductSupplier->buying_price_in_thb = ($unit_price / $supplier_conv_rate_thb);
				    //                     $getProductSupplier->save();

				    //                     $product_detail = Product::find($checkSameProduct->product_id);
				    //                     if($getProductSupplier !== null && $product_detail->supplier_id == $supplier_id)
				    //                     {
				    //                         $buying_price_in_thb = ($unit_price / $supplier_conv_rate_thb);

				    //                         $importTax = $getProductSupplier->import_tax_actual !== null ? $getProductSupplier->import_tax_actual : $product_detail->import_tax_book;

				    //                         $total_buying_price = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

				    //                         $total_buying_price = ($getProductSupplier->freight)+($getProductSupplier->landing)+($getProductSupplier->extra_cost)+($total_buying_price);

				    //                         $product_detail->total_buy_unit_cost_price = $total_buying_price;

				    //                         // this is supplier buying unit cost price
				    //                         $product_detail->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;

				    //                         // this is selling price
				    //                         $total_selling_price = $product_detail->total_buy_unit_cost_price * $product_detail->unit_conversion_rate;

				    //                         $product_detail->selling_price = $total_selling_price;
				    //                         $product_detail->last_price_updated_date = Carbon::now();
				    //                         $product_detail->save();

				    //                         $product_history              = new ProductHistory;
				    //                         $product_history->user_id     = $user_id;
				    //                         $product_history->product_id  = $checkSameProduct->product_id;
				    //                         $product_history->column_name = "Purchasing Price (From PO - ".$checkSameProduct->PurchaseOrder->ref_id.")"." Ref ID#. ".$checkSameProduct->id;
				    //                         $product_history->old_value   = $old_price_value;
				    //                         $product_history->new_value   = $unit_price;
				    //                         $product_history->save();
				    //                     }
				    //                 }
				    //             }
				    //         }

				    //         $order_history                   = new PurchaseOrdersHistory;
				    //         $order_history->user_id          = $user_id;
				    //         $order_history->order_id         = $checkSameProduct->order_id;
				    //         $order_history->reference_number = @$checkSameProduct->product->refrence_code;
				    //         $order_history->old_value        = @$old_value;
				    //         $order_history->column_name      = "Unit Price";
				    //         $order_history->new_value        = @$row[16];
				    //         $order_history->po_id            = @$checkSameProduct->po_id;
				    //         $order_history->pod_id           = @$checkSameProduct->id;
				    //         $order_history->save();

				    //     }
				    //     else
				    //     {
				    //         $checkSameProduct->pod_unit_price = $row[16];
				    //         $checkSameProduct->pod_total_unit_price = ($row[16] * $checkSameProduct->quantity);

				    //         $calculations = $checkSameProduct->total_unit_price_in_thb * ($checkSameProduct->pod_import_tax_book / 100);
				    //         $checkSameProduct->pod_import_tax_book_price = $calculations;
				    //         $checkSameProduct->save();

				    //         $order_history = new PurchaseOrdersHistory;
				    //         $order_history->user_id = $user_id;
				    //         $order_history->order_id = $checkSameProduct->order_id;
				    //         $order_history->reference_number = "Billed Item";
				    //         $order_history->old_value = @$old_value;
				    //         $order_history->column_name = "Unit Price";
				    //         $order_history->new_value = @$row[16];
				    //         $order_history->po_id = @$checkSameProduct->po_id;
				    //         $order_history->pod_id = @$checkSameProduct->id;
				    //         $order_history->save();
				    //     }

				    //     $sub_total = 0 ;
				    //     $total_import_tax_book_price = 0;
				    //     $query     = PurchaseOrderDetail::where('po_id',$po_id)->get();
				    //     foreach ($query as  $value)
				    //     {
				    //         $unit_price = $value->pod_unit_price;
				    //         $sub = $value->quantity * $unit_price - (($value->quantity * $unit_price) * ($value->discount / 100));
				    //         $value->pod_total_unit_price = $sub;
				    //         $value->save();
				    //         $sub_total += $sub;

				    //         $total_import_tax_book_price = $total_import_tax_book_price + $value->pod_import_tax_book_price;
				    //     }

				    //     $po_totoal_change = PurchaseOrder::find($po_id);
				    //     $po_totoal_change->total_import_tax_book_price = $total_import_tax_book_price;
				    //     $po_totoal_change->total = number_format($sub_total, 3, '.', '');
				    //     $po_totoal_change->total_in_thb = number_format($sub_total, 3, '.', '') * (1 / @$po_totoal_change->exchange_rate);
				    //     $po_totoal_change->save();

				    //     if($po_totoal_change->status >= 14)
				    //     {
				    //     	$this->updateGroup($po_id);
				    //     }
			    	// }
        //         }

        //         //Discount Column
        //         if($row[17] != null)
        //         {
        //             if(!is_numeric($row[17]))
        //             {
        //                 $row[17] = null;
        //                 $error = 1;
        //                 $html_string .= '<li>Enter Valid Discount For Product <b>'.$row[5].'</b></li>';
        //             }
        //         }

        //         if($row[17] != null)
        //         {
        //         	$po = PurchaseOrderDetail::where('id',$pod_id)->where('po_id',$po_id)->first();
        //         	$old_value_discount = $po->discount;
        // 			$checkSameProduct = PurchaseOrderDetail::where('po_id',$po->po_id)->where('product_id',$po->product_id)->get();
        // 			if($old_value_discount != $row[17])
        // 			{
	       //  			$po->discount = $row[17];
	       //  			$po->save();

	       //  			$order_history = new PurchaseOrdersHistory;
		      //           $order_history->user_id = @$user_id;
		      //           $order_history->order_id = @$po->order_id;
		      //           if($po->is_billed == "Billed")
		      //           {
		      //               $order_history->reference_number = "Billed Item";
		      //           }
		      //           else
		      //           {
		      //               $order_history->reference_number = @$po->product->refrence_code;
		      //           }
		      //           $order_history->old_value = @$old_value_discount;

		      //           $order_history->column_name = "Discount";

		      //           $order_history->new_value = @$row[17];
		      //           $order_history->po_id = @$po->po_id;
		      //           $order_history->save();

		      //           if($po->PurchaseOrder->status >= 14)
			     //        {
			     //            $checkSameProduct2 = PurchaseOrderDetail::find($pod_id);
			     //            if ($checkSameProduct2) 
			     //            {
			     //                $total = ($checkSameProduct2->pod_unit_price * $checkSameProduct2->quantity) * ($checkSameProduct2->discount / 100);
			     //                $total_thb = ($checkSameProduct2->unit_price_in_thb * $checkSameProduct2->quantity) * ($checkSameProduct2->discount / 100);
			     //                $po->pod_total_unit_price = $total;
			     //                $po->total_unit_price_in_thb = $total_thb;
			     //                $po->save();

			     //                if($checkSameProduct2->product_id != null)
			     //                {
			     //                    if($checkSameProduct2->PurchaseOrder->supplier_id == NULL && $checkSameProduct2->PurchaseOrder->from_warehouse_id != NULL)
			     //                    {
			     //                        $supplier_id = $checkSameProduct2->product->supplier_id;
			     //                    }
			     //                    else
			     //                    {
			     //                        $supplier_id = $checkSameProduct2->PurchaseOrder->supplier_id;
			     //                    }

			     //                    // this is the price of after conversion for THB
			     //                    if($checkSameProduct2->PurchaseOrder->exchange_rate != NULL)
			     //                    {
			     //                        $supplier_conv_rate_thb = $checkSameProduct2->PurchaseOrder->exchange_rate;
			     //                    }
			     //                    else
			     //                    {
			     //                        $supplier_conv_rate_thb = $checkSameProduct2->PurchaseOrder->PoSupplier->getCurrency->conversion_rate;
			     //                    }

			     //                    if($checkSameProduct2->pod_unit_price !== NULL)
			     //                    {
			     //                        $discount_price = $checkSameProduct2->quantity * $checkSameProduct2->pod_unit_price - (($checkSameProduct2->quantity * $checkSameProduct2->pod_unit_price) * ($checkSameProduct2->discount / 100));

			     //                        if($checkSameProduct2->quantity !== NULL && $checkSameProduct2->quantity !== 0 )
			     //                        {
			     //                            $after_discount_price = ($discount_price / $checkSameProduct2->quantity);
			     //                        }
			     //                        else
			     //                        {
			     //                            $after_discount_price = ($discount_price);
			     //                        }
			     //                        $unit_price = $after_discount_price;
			     //                    }
			     //                    else
			     //                    {
			     //                        $unit_price = $checkSameProduct2->pod_unit_price;
			     //                    }

			     //                    if($checkSameProduct2->discount < 100 || $checkSameProduct2->discount == null)
			     //                    {
			     //                        $getProductSupplier = SupplierProducts::where('product_id',@$checkSameProduct2->product_id)->where('supplier_id',@$supplier_id)->first();
			     //                        $old_price_value    = $getProductSupplier->buying_price;

			     //                        $getProductSupplier->buying_price_in_thb = ($unit_price / $supplier_conv_rate_thb);
			     //                        $getProductSupplier->buying_price = $unit_price;
			     //                        $getProductSupplier->save();

			     //                        $product_detail = Product::find($checkSameProduct2->product_id);

			     //                        if($getProductSupplier !== null && $product_detail->supplier_id == $supplier_id)
			     //                        {
			     //                            $buying_price_in_thb = ($getProductSupplier->buying_price / $supplier_conv_rate_thb);

			     //                            $importTax = $getProductSupplier->import_tax_actual !== null ? $getProductSupplier->import_tax_actual : $product_detail->import_tax_book;

			     //                            $total_buying_price = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

			     //                            $total_buying_price = ($getProductSupplier->freight)+($getProductSupplier->landing)+($getProductSupplier->extra_cost)+($getProductSupplier->extra_tax)+($total_buying_price);

			     //                            $product_detail->total_buy_unit_cost_price = $total_buying_price;

			     //                            // this is supplier buying unit cost price
			     //                            $product_detail->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;

			     //                            // this is selling price
			     //                            $total_selling_price = $product_detail->total_buy_unit_cost_price * $product_detail->unit_conversion_rate;

			     //                            $product_detail->selling_price = $total_selling_price;
			     //                            $product_detail->save();

			     //                            $product_history = new ProductHistory;
			     //                            $product_history->user_id = @$user_id;
			     //                            $product_history->product_id = $product_detail->id;
			     //                            $product_history->column_name = "Purchasing Price (From PO - ".$checkSameProduct2->PurchaseOrder->ref_id.")"." Ref ID#. ".$checkSameProduct2->id;
			     //                            $product_history->old_value = $old_price_value;
			     //                            $product_history->new_value = $unit_price;
			     //                            $product_history->save();
			     //                        }
			     //                    }
			     //                }
			     //            }
			     //        }

			     //        $sub_total = 0;
			     //        $query     = PurchaseOrderDetail::where('po_id',$po_id)->get();
			     //        foreach ($query as  $value)
			     //        {
			     //            $unit_price = @$value->pod_unit_price;
			     //            $sub = $value->quantity * $unit_price - (($value->quantity * $unit_price) * (@$value->discount / 100));

			     //            $unit_price_thb = @$value->unit_price_in_thb;
			     //            $sub_thb = $value->quantity * $unit_price_thb - (($value->quantity * $unit_price_thb) * (@$value->discount / 100));

			     //            $value->pod_total_unit_price = $sub;
			     //            $value->total_unit_price_in_thb = $sub_thb;
			     //            $value->save();
			     //            $sub_total += $sub;
			     //        }

			     //        // dd($sub_total);
			     //        $po_modifications = PurchaseOrder::find($po_id);
			     //        $po_modifications->total = $sub_total;
			     //        $po_modifications->save();

			     //        if($po_modifications->status >= 14)
			     //        {
			     //            $p_o_p_d = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_modifications->po_group_id)->where('product_id',$po->product_id)->first();
			     //            $updated_pod = PurchaseOrderDetail::where('id',$pod_id)->where('po_id',$po_id)->first();
			     //            if($p_o_p_d->occurrence == 1)
			     //            {
			     //                $p_o_p_d->discount = $updated_pod->discount;
			     //                $p_o_p_d->total_unit_price = $updated_pod->pod_total_unit_price;
			     //                $p_o_p_d->total_unit_price_in_thb = $updated_pod->total_unit_price_in_thb;
			     //                $p_o_p_d->save();
			     //            }
			     //            $this->updateGroup($po_id);
	       //      		}
        //     		}

        //         }

        //         //Qty Inv Column
        //         if($row[12] != null)
        //         {
        //             if(!is_numeric($row[12]))
        //             {
        //                 $row[12] = null;
        //                 $error = 1;
        //                 $html_string .= '<li>Enter Valid QTY Inv For Product <b>'.$row[5].'</b></li>';
        //             }
        //         }

        //         if($row[12] != null)
        //         {
        //         	$po = PurchaseOrderDetail::where('id',$pod_id)->where('po_id',$po_id)->first();
        //         	$old_value_quantity = $po->quantity;
        // 			$checkSameProduct = PurchaseOrderDetail::where('po_id',$po->po_id)->where('product_id',$po->product_id)->get();
        // 			if($old_value_quantity != $row[12])
        // 			{
	       //  			$po->quantity = $row[12];
	       //  			$po->save();

	       //  			$order_history = new PurchaseOrdersHistory;
		      //           $order_history->user_id = @$user_id;
		      //           $order_history->order_id = @$po->order_id;
		      //           if($po->is_billed == "Billed")
		      //           {
		      //               $order_history->reference_number = "Billed Item";
		      //           }
		      //           else
		      //           {
		      //               $order_history->reference_number = @$po->product->refrence_code;
		      //           }
		      //           $order_history->old_value = @$old_value_quantity;

		      //           $order_history->column_name = "QTY Inv";

		      //           $order_history->new_value = @$row[12];
		      //           $order_history->po_id = @$po->po_id;
		      //           $order_history->save();

		      //           if($po->PurchaseOrder->status >= 14)
			     //        {
			     //            $checkSameProduct2 = PurchaseOrderDetail::find($pod_id);
			     //            if ($checkSameProduct2) 
			     //            {
			     //                $total = ($checkSameProduct2->pod_unit_price * $checkSameProduct2->quantity) * ($checkSameProduct2->discount / 100);
			     //                $total_thb = ($checkSameProduct2->unit_price_in_thb * $checkSameProduct2->quantity) * ($checkSameProduct2->discount / 100);
			     //                $po->pod_total_unit_price = $total;
			     //                $po->total_unit_price_in_thb = $total_thb;
			     //                $po->save();

			     //                if($checkSameProduct2->product_id != null)
			     //                {
			     //                    if($checkSameProduct2->PurchaseOrder->supplier_id == NULL && $checkSameProduct2->PurchaseOrder->from_warehouse_id != NULL)
			     //                    {
			     //                        $supplier_id = $checkSameProduct2->product->supplier_id;
			     //                    }
			     //                    else
			     //                    {
			     //                        $supplier_id = $checkSameProduct2->PurchaseOrder->supplier_id;
			     //                    }

			     //                    // this is the price of after conversion for THB
			     //                    if($checkSameProduct2->PurchaseOrder->exchange_rate != NULL)
			     //                    {
			     //                        $supplier_conv_rate_thb = $checkSameProduct2->PurchaseOrder->exchange_rate;
			     //                    }
			     //                    else
			     //                    {
			     //                        $supplier_conv_rate_thb = $checkSameProduct2->PurchaseOrder->PoSupplier->getCurrency->conversion_rate;
			     //                    }

			     //                    if($checkSameProduct2->pod_unit_price !== NULL)
			     //                    {
			     //                        $discount_price = $checkSameProduct2->quantity * $checkSameProduct2->pod_unit_price - (($checkSameProduct2->quantity * $checkSameProduct2->pod_unit_price) * ($checkSameProduct2->discount / 100));

			     //                        if($checkSameProduct2->quantity !== NULL && $checkSameProduct2->quantity !== 0 )
			     //                        {
			     //                            $after_discount_price = ($discount_price / $checkSameProduct2->quantity);
			     //                        }
			     //                        else
			     //                        {
			     //                            $after_discount_price = ($discount_price);
			     //                        }
			     //                        $unit_price = $after_discount_price;
			     //                    }
			     //                    else
			     //                    {
			     //                        $unit_price = $checkSameProduct2->pod_unit_price;
			     //                    }
			     //                }
			     //            }
			     //        }

			     //        $sub_total = 0;
			     //        $query     = PurchaseOrderDetail::where('po_id',$po_id)->get();
			     //        foreach ($query as  $value)
			     //        {
			     //            $unit_price = @$value->pod_unit_price;
			     //            $sub = $value->quantity * $unit_price - (($value->quantity * $unit_price) * (@$value->discount / 100));

			     //            $unit_price_thb = @$value->unit_price_in_thb;
			     //            $sub_thb = $value->quantity * $unit_price_thb - (($value->quantity * $unit_price_thb) * (@$value->discount / 100));

			     //            $value->pod_total_unit_price = $sub;
			     //            $value->total_unit_price_in_thb = $sub_thb;
			     //            $value->save();
			     //            $sub_total += $sub;
			     //        }

			     //        // dd($sub_total);
			     //        $po_modifications = PurchaseOrder::find($po_id);
			     //        $po_modifications->total = $sub_total;
			     //        $po_modifications->save();

			     //        if($po_modifications->status >= 14)
			     //        {
			     //            $p_o_p_d = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_modifications->po_group_id)->where('product_id',$po->product_id)->first();
			     //            $updated_pod = PurchaseOrderDetail::where('id',$pod_id)->where('po_id',$po_id)->first();
			     //            if($p_o_p_d->occurrence == 1)
			     //            {
			     //                $p_o_p_d->quantity_inv = $updated_pod->quantity;
			     //                $p_o_p_d->total_unit_price = $updated_pod->pod_total_unit_price;
			     //                $p_o_p_d->total_unit_price_in_thb = $updated_pod->total_unit_price_in_thb;
			     //                $p_o_p_d->save();
			     //            }

			     //            if($p_o_p_d->occurrence > 1)
			     //            {
			     //                $p_o_p_d->quantity_inv -= $old_value_quantity;
			     //                $p_o_p_d->save();
			     //                $p_o_p_d->quantity_inv += $updated_pod->quantity;
			     //                $p_o_p_d->save();
			     //            }
			     //            $this->updateGroup($po_id);
			     //            // dd($p_o_p_d);
	       //      		}
        //     		}

        //         }
        //     }

        //     if($error == 1)
	       //  {
	       //      $success = 'hasError';
	       //      $this->error_msgs = $html_string;
	       //      $this->filterFunction($success);
	       //  }
	       //  else
	       //  {
	       //      if($error == 0)
	       //      {
	       //          $html_string = '';
	       //      }
	       //      $success = 'pass';
	       //      $this->error_msgs = $html_string;
	       //      $this->filterFunction($success);
        // 	}
        // }
    }

    public function startRow():int
    {
        return 1;
    }

    // public function filterFunction($success = null)
    // {
    //     if($success == 'fail')
    //     {
    //         $this->response = "File is Empty Please Upload Valid File !!!";
    //         $this->result   = "true";
    //     }
    //     elseif($success == 'pass')
    //     {
    //         $this->response = "Products Imported Successfully !!!";
    //         $this->result   = "false";
    //     }
    //     elseif($success == 'hasError')
    //     {
    //         $this->response = "Products Imported Successfully, But Some Of Them Has Issues !!!";
    //         $this->result   = "withissues";
    //     }
    //     elseif($success == 'redirect')
    //     {
    //         $this->response = "Import File Dosen\"t have PF# column, please Import valid file !!!";
    //         $this->result   = "true";
    //     }
    // }

    // public function updateGroup($po_id)
    // {
    //     $total_import_tax_book_price = 0;
    //     $po_totoal_change = PurchaseOrder::find($po_id);
    //     if($po_totoal_change->exchange_rate == null)
    //     {
    //         $supplier_conv_rate_thb = $po_totoal_change->PoSupplier->getCurrency->conversion_rate;
    //     }
    //     else
    //     {
    //         $supplier_conv_rate_thb = $po_totoal_change->exchange_rate;
    //     }

    //     foreach ($po_totoal_change->PurchaseOrderDetail as $p_o_d)
    //     {
    //         // $p_o_d->currency_conversion_rate  = $supplier_conv_rate_thb;
    //         $p_o_d->unit_price_in_thb         = $p_o_d->pod_unit_price/$supplier_conv_rate_thb;
    //         $p_o_d->total_unit_price_in_thb   = $p_o_d->pod_total_unit_price/$supplier_conv_rate_thb;
    //         $p_o_d->pod_import_tax_book_price = ($p_o_d->pod_import_tax_book/100)*$p_o_d->total_unit_price_in_thb;
    //         $p_o_d->save();
    //     }


    //     $po_totoal_change->total_in_thb = $po_totoal_change->total/$supplier_conv_rate_thb;
    //     $po_totoal_change->save();

    //     $total_import_tax_book_price2 = null;
    //     $total_buying_price_in_thb2   = null;

    //     // getting all po's with this po group
    //     $gettingAllPos = PoGroupDetail::select('purchase_order_id')->where('po_group_id', $po_totoal_change->po_group_id)->get();
    //     $po_group = PoGroup::find($po_totoal_change->po_group_id);

    //     if($po_group->is_review == 0)
    //     {
    //         if($gettingAllPos->count() > 0)
    //         {
    //             foreach ($gettingAllPos as $allPos)
    //             {
    //                 $purchase_order = PurchaseOrder::find($allPos->purchase_order_id);
    //                 $total_import_tax_book_price2 += $purchase_order->total_import_tax_book_price;
    //                 $total_buying_price_in_thb2   += $purchase_order->total_in_thb;
    //             }
    //         }

    //         $po_group->po_group_import_tax_book    = $total_import_tax_book_price2;
    //         $po_group->total_buying_price_in_thb   = $total_buying_price_in_thb2;
    //         $po_group->save();

    //         #average unit price
    //         $average_unit_price = 0;
    //         $average_count = 0;
    //         foreach ($gettingAllPos as $po_id)
    //         {
    //             $average_count++;
    //             $purchase_order = PurchaseOrder::find($po_id->purchase_order_id);

    //             $purchase_order_details = PurchaseOrderDetail::where('po_id',$purchase_order->id)->whereNotNull('product_id')->get();
    //             foreach ($purchase_order_details as $p_o_d) {

    //                 $po_group_product =  PoGroupProductDetail::where('status',1)->where('product_id',$p_o_d->product_id)->where('po_group_id',$po_group->id)->where('supplier_id',$purchase_order->supplier_id)->first();
                   
    //                 if($po_group_product != null)
    //                 {
    //                     if($po_group_product->occurrence > 1)
    //                     {
    //                         $ccr = $po_group_product->po_group->purchase_orders()->pluck('id')->toArray();
    //                         $po_group_product->unit_price                = ($p_o_d->pod_unit_price)/$average_count;
    //                         $average_currency = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'currency_conversion_rate');
    //                         $po_group_product->currency_conversion_rate = $average_currency/$po_group_product->occurrence;

    //                         $buying_price = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'buying_price_in_thb');
    //                         $po_group_product->unit_price_in_thb         =  $buying_price / $po_group_product->occurrence;

    //                         $total_buying_price = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'total_buying_price_in_thb');
    //                         $po_group_product->total_unit_price_in_thb         =  $total_buying_price / $po_group_product->occurrence;

    //                     }
    //                     else
    //                     {
    //                         $po_group_product->unit_price                = $p_o_d->pod_unit_price;
    //                         $po_group_product->currency_conversion_rate  = $p_o_d->currency_conversion_rate;

    //                         $po_group_product->unit_price_in_thb         =  $p_o_d->unit_price_in_thb;
    //                         if($po_group_product->discount > 0)
    //                         {
    //                             $discount_value = ($p_o_d->unit_price_in_thb * $po_group_product->quantity_inv) * ($po_group_product->discount / 100);
    //                         }
    //                         else
    //                         {
    //                             $discount_value = 0;
    //                         }
    //                         $po_group_product->total_unit_price_in_thb   = $p_o_d->unit_price_in_thb * $po_group_product->quantity_inv - $discount_value;
    //                         $po_group_product->import_tax_book_price     = ($po_group_product->import_tax_book/100)*$po_group_product->total_unit_price_in_thb;
    //                     }
                        

    //                     $po_group_product->save();
    //                 }
    //             }
    //         }

    //         $po_group = PoGroup::where('id',$po_group->id)->first();

    //         $total_import_tax_book_price = 0;
    //         $po_group_details = $po_group->po_group_product_details;
    //         foreach ($po_group_details as $po_group_detail)
    //         {
    //             $total_import_tax_book_price += ($po_group_detail->import_tax_book_price);
    //         }
    //         if($total_import_tax_book_price == 0)
    //         {
    //             foreach ($po_group_details as $po_group_detail)
    //             {
    //                 $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
    //                 $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
    //                 $total_import_tax_book_price += $book_tax;
    //             }
    //         }

    //         $po_group->po_group_import_tax_book      = $total_import_tax_book_price;
    //         $po_group->save();

    //         if($po_group->tax !== NULL)
    //         {
    //             $total_import_tax_book_price = 0;
    //             $total_import_tax_book_percent = 0;
    //             $po_group_details = $po_group->po_group_product_details;
    //             foreach ($po_group_details as $po_group_detail)
    //             {
    //                 $total_import_tax_book_price += ($po_group_detail->import_tax_book_price);
    //                 $total_import_tax_book_percent += ($po_group_detail->import_tax_book);

    //                 // To Recalculate the Actual tax
    //                 $tax_value                        = $po_group->tax;
    //                 $total_import_tax                 = $po_group->po_group_import_tax_book;
    //                 $import_tax                       = $po_group_detail->import_tax_book;
    //                 $actual_tax_percent               = ($tax_value/$total_import_tax*$import_tax); 
    //                 $po_group_detail->actual_tax_percent = $actual_tax_percent;

    //                 $po_group_detail->save();
    //             }
    //             if($total_import_tax_book_price == 0)
    //             {
    //                 foreach ($po_group_details as $po_group_detail)
    //                 {
    //                     $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
    //                     $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
    //                     $total_import_tax_book_price += $book_tax;
    //                 }
    //             }
    //             $po_group->po_group_import_tax_book = $total_import_tax_book_price;
    //             $po_group->total_import_tax_book_percent = $total_import_tax_book_percent;
    //             $po_group->save();

    //             foreach ($po_group->po_group_product_details as $group_detail)
    //             {
    //                 $tax = $po_group->tax;
    //                 $total_import_tax = $po_group->po_group_import_tax_book;
    //                 $import_tax = $group_detail->import_tax_book;
    //                 if($total_import_tax != 0 )
    //                 {
    //                     $actual_tax_percent = ($tax/$total_import_tax*$import_tax);
    //                     $group_detail->actual_tax_percent = $actual_tax_percent;
    //                 }

    //                 $group_detail->save();
    //             }
    //         }

    //         if($po_group->freight !== NULL)
    //         {
    //             $po_group_details = $po_group->po_group_product_details;
    //             foreach ($po_group_details as $po_group_detail)
    //             {
    //                 $item_gross_weight     = $po_group_detail->total_gross_weight;
    //                 $total_gross_weight    = $po_group->po_group_total_gross_weight;
    //                 $total_freight         = $po_group->freight;
    //                 $total_quantity        = $po_group_detail->quantity_inv;
    //                 $freight               = (($total_freight*($item_gross_weight/$total_gross_weight))/$total_quantity);
    //                 $po_group_detail->freight = $freight; 

    //                 $po_group_detail->save();
    //             }
    //         }

    //         if($po_group->landing !== NULL)
    //         {
    //             $po_group_details = $po_group->po_group_product_details;
    //             foreach ($po_group_details as $po_group_detail)
    //             {
    //                 $item_gross_weight     = $po_group_detail->total_gross_weight;
    //                 $total_gross_weight    = $po_group->po_group_total_gross_weight;
    //                 $total_quantity        = $po_group_detail->quantity_inv;
    //                 $total_landing         = $po_group->landing;
    //                 $landing               = (($total_landing*($item_gross_weight/$total_gross_weight))/$total_quantity);
    //                 $po_group_detail->landing = $landing;

    //                 $po_group_detail->save();
    //             }
    //         }
    //     }
    // }
}
