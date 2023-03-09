<?php
namespace App\Helpers;
/**
 *
 */
use Carbon\Carbon;
class ExportHelper
{
	public static function prepareExcel($query,$global_terminologies,$not_visible_arr,$available_stock,$getCategories,$role_id,$stock, $request, $product_detail_section){

		// $handle = fopen(public_path('/export.csv'), 'w');
		$handle = fopen(storage_path('/app/Sold-Products-Report.csv'), 'w');

		// heading array starts
		$not_visible_arr = $not_visible_arr;
        $global_terminologies = $global_terminologies;
        $role_id = $role_id;
        $product_detail_section = $product_detail_section;

        $headings_array = [];
        if (!in_array('0',$not_visible_arr)) {
            array_push($headings_array, 'Order');
        }
        if (!in_array('1',$not_visible_arr)) {
            array_push($headings_array, 'Status');
        }
        if (!in_array('2',$not_visible_arr)) {
            array_push($headings_array, 'Ref. Po#');
        }
        if (!in_array('3',$not_visible_arr)) {
            array_push($headings_array, 'PO #');
        }
        if (!in_array('4',$not_visible_arr)) {
            array_push($headings_array, 'Customer #');
        }
        if (!in_array('5',$not_visible_arr)) {
            array_push($headings_array, 'Customer');
        }
        if (!in_array('6',$not_visible_arr)) {
            array_push($headings_array, 'Billing Name');
        }
        if (!in_array('7',$not_visible_arr)) {
            array_push($headings_array, 'Tax ID');
        }
        if (!in_array('8',$not_visible_arr)) {
            array_push($headings_array, 'Reference Address');
        }
        if (!in_array('9',$not_visible_arr)) {
            array_push($headings_array, 'Sale Person');
        }
        if (!in_array('10',$not_visible_arr)) {
            array_push($headings_array, 'Delivery Date');
        }
        if (!in_array('11',$not_visible_arr)) {
            array_push($headings_array, 'Created Date');
        }
        if (!in_array('12',$not_visible_arr)) {
            array_push($headings_array, 'Target Ship Date');
        }
        if (!in_array('13',$not_visible_arr)) {
            array_push($headings_array, $global_terminologies['supply_from']);
        }
        if (!in_array('14',$not_visible_arr)) {
            array_push($headings_array, $global_terminologies['our_reference_number']);
        }
        if (!in_array('15',$not_visible_arr)) {
            array_push($headings_array, $global_terminologies['category'] . '/' . $global_terminologies['subcategory']);
        }
        if (!in_array('16',$not_visible_arr)) {
            array_push($headings_array, 'Product Type');
        }
        if (!in_array('17',$not_visible_arr)) {
            array_push($headings_array, $global_terminologies['brand']);
        }
        if (!in_array('18',$not_visible_arr)) {
            array_push($headings_array, $global_terminologies['product_description']);
        }
        if (in_array('product_type_2', $product_detail_section))
        {
            if (!in_array('19',$not_visible_arr)) {
              $type_2 = (!array_key_exists('product_type_2', $global_terminologies)) ? 'Type 2' : $global_terminologies['product_type_2'];
                array_push($headings_array, $type_2);
            }
        }
        if (in_array('product_type_3', $product_detail_section))
        {
            if (!in_array('20',$not_visible_arr)) {
                $type_3 = (!array_key_exists('product_type_3', $global_terminologies)) ? 'Type 3' : $global_terminologies['product_type_3'];
                  array_push($headings_array, $type_3);
            }
        }
        if (!in_array('21',$not_visible_arr)) {
            array_push($headings_array, $available_stock);
        }
        if (!in_array('22',$not_visible_arr)) {
            array_push($headings_array, 'Selling Unit');
        }
        if (!in_array('23',$not_visible_arr)) {
            array_push($headings_array, $global_terminologies['qty']);
        }
        if (!in_array('24',$not_visible_arr)) {
            array_push($headings_array, 'Pieces');
        }
        if (!in_array('25',$not_visible_arr)) {
            array_push($headings_array, 'Unit Price');
        }
        if (!in_array('26',$not_visible_arr)) {
            array_push($headings_array, 'Discount %');
        }

        if($role_id != 3 && $role_id != 4 && $role_id != 6){
            if (!in_array('27',$not_visible_arr)) {
                array_push($headings_array, $global_terminologies['net_price'] . '(THB)');
            }
            if (!in_array('28',$not_visible_arr)) {
                array_push($headings_array, 'Total' . $global_terminologies['net_price'] . '/ unit (THB)');
            }
        }
        if (!in_array('29',$not_visible_arr)) {
            array_push($headings_array, 'Sub Total');
        }
        if (!in_array('30',$not_visible_arr)) {
            array_push($headings_array, 'Total Amount');
        }
        if (!in_array('31',$not_visible_arr)) {
            array_push($headings_array, 'Vat(THB)');
        }
        if (!in_array('32',$not_visible_arr)) {
            array_push($headings_array, 'Vat %');
        }
        if (!in_array('33',$not_visible_arr)) {
            array_push($headings_array, $global_terminologies['note_two']);
        }
        if (!in_array('34',$not_visible_arr)) {
            array_push($headings_array, 'Total Margin');
        }
        if (!in_array('35',$not_visible_arr)) {
            array_push($headings_array, 'Magin %');
        }

        if($getCategories->count() > 0)
        {
            $inc = 36;
            foreach($getCategories as $cat)
            {
                if(!in_array($inc,$not_visible_arr))
                {
                    array_push($headings_array, $cat->title . '(Fixed Price)');
                }
            }
            $inc++;
        }
        fputcsv($handle, $headings_array);
		// ends

		// excel body starts here
		$query->chunk(3000,function($invoices) use ($handle,$global_terminologies,$not_visible_arr,$available_stock,$getCategories,$role_id,$stock, $request, $product_detail_section){
          foreach ($invoices as $item) {
            $not_visible_arr = $not_visible_arr;
	        $role_id = $role_id;
	        $getCategories = $getCategories;
	        $request = $request;
	        $product_detail_section = $product_detail_section;
	        $data_array = [];

	          $order = $item->get_order;
	          $order_idd = null;
	          $po_noo = null;
	          $supply_from = null;
	          $qty = null;
	          $pieces = null;
	          $brand=null;
	          $cogs=null;
	          $total_cogs=null;

	          if($item->order_id != null)
	          {
	            $order = @$item->get_order;
	            $ret = $order->get_order_number_and_link($order);
	            $ref_no = $ret[0];
	            $link = $ret[1];
	            // dd($ref_no);
	            $order_idd = $ref_no;
	          }
	          else
	          {
	            $order_idd = "--";
	          }

	          if($item->order_id != null)
	          {
	            $status = $order != null ? ($order->status != null ? $order->statuses->title : 'N.A') : 'N.A';
	          }
	          else
	          {
	            $status = 'N.A';
	          }

	          if($item->order_id != null)
	          {
	            $ref_po_no = $order != null ? ($order->memo != NULL ? $order->memo : "N.A") : 'N.A';
	          }
	          else
	          {
	            $ref_po_no = 'N.A';
	          }

	          if($item->supplier_id != NULL && $item->from_warehouse_id == NULL)
	          {
	            $supply_from = $item->from_supplier->reference_name;
	          }

	          elseif($item->from_warehouse_id != NULL && $item->supplier_id == NULL)
	          {
	            $supply_from = $item->from_warehouse->warehouse_title;
	          }
	          else
	          {
	            $supply_from = 'N.A';
	          }

	          if($order->primary_status == 2)
	          {
	            $qty = ($item->quantity !== null ? round($item->quantity,2) : 'N.A');
	          }
	          else
	          {
	            $qty = ($item->qty_shipped !== null ? round($item->qty_shipped,2) : 'N.A');
	          }
	          if($order->primary_status == 2)
	          {
	            $pieces = ($item->number_of_pieces !== null ? round($item->number_of_pieces,2) : 'N.A');
	          }
	          else
	          {
	            $pieces = ($item->pcs_shipped !== null ? round($item->pcs_shipped,2) : 'N.A');
	          }

	          if($order->primary_status == 2)
	          {
	            $cogs = 'DRAFT';
	          }
	          else
	          {
	            $cogs = ($item->actual_cost !== null ? round($item->actual_cost, 2) : 'N.A');
	          }

	          if($order->primary_status == 2)
	          {
	            $total_cogs = 'DRAFT';
	          }
	          else
	          {
	            $total_cogs = ($item->qty_shipped !== null ? number_format((float)$item->actual_cost * $item->qty_shipped, 2,'.','') : 'N.A');
	          }


	          if($item->brand!=null)
	          {
	            $brand=$item->brand;
	          }
	          elseif($item->product_id!=null)
	          {
	            if($item->product->brand!=null)
	            {
	              $brand=$item->product->brand;
	            }
	            else
	            {
	              $brand='--';
	            }

	          }
	          else
	          {
	            $brand='--';
	          }
	          $warehouse_id = $request['warehouse_id_exp'];
	          $final_available = 0;
	          if($warehouse_id != null)
	          {
	            $final_available = $item->product != null ? $item->product->get_stock($item->product->id, $warehouse_id) : 0;
	          }
	          else
	          {
	            $warehouse_product = $item->warehouse_products->sum('available_quantity');
	            $final_available = $warehouse_product != null ? number_format($warehouse_product,3,'.','') : 0;
	          }

	          $product_type = $item->product != null ? ($item->product->productType2 != null ? $item->product->productType2->title : '--') : '--';

	          $product_type_3 = $item->product != null ? ($item->product->productType3 != null ? $item->product->productType3->title : '--') : '--';


	          if($item->vat_amount_total != null)
	          {
	            $vat_amount_total = number_format($item->vat_amount_total,2,'.','');
	          }
	          else
	          {
	            $vat_amount_total = 0;
	          }
	          if($item->purchase_order_detail != null){
	            $po_noo = $item->purchase_order_detail->PurchaseOrder->ref_id;
	          }
	          else{
	            $po_noo = '--';
	          }

	          $customer_categories_array = array();
	          if($getCategories->count() > 0)
	          {
	            foreach ($getCategories as $cat)
	            {
	              $fixed_value = $item->product->product_fixed_price->where('product_id',$item->product_id)->where('customer_type_id',$cat->id)->first();
	              $value = $fixed_value !== null ? $fixed_value->fixed_price : 0;
	              $va = round($value,3);
	              array_push($customer_categories_array, $va);
	            }
	          }

	          $order_notes = $item->get_order_product_notes;
	          $note_html = '';
	          if($item->status == 38)
	          {
	            $note_html = $item->remarks;
	          }
	          else if($order_notes->count() > 0)
	          {
	            foreach ($order_notes as $note) {
	              $note_html .= ' '.$note->note;
	            }
	          }
	          else
	          {
	            $note_html = '';
	          }
	          $cat_prod = $item->product != null ? ($item->product->productCategory != null ? $item->product->productCategory->title : '') : '';
	          $cat_prod .= $item->product != null ? ($item->product->productSubCategory != null ? (' / '.$item->product->productSubCategory->title) : '') : '';

	        if ($qty != 0 && $qty > 0){
	          if (!in_array('0',$not_visible_arr)) {
	              array_push($data_array, $order_idd);
	          }
	          if (!in_array('1',$not_visible_arr)) {
	              array_push($data_array, $status);
	          }
	          if (!in_array('2',$not_visible_arr)) {
	              array_push($data_array, $ref_po_no);
	          }
	          if (!in_array('3',$not_visible_arr)) {
	              array_push($data_array, $po_noo);
	          }
	          if (!in_array('4',$not_visible_arr)) {
	              array_push($data_array, $order->customer !== null ? $order->customer->reference_number : 'N.A');
	          }
	          if (!in_array('5',$not_visible_arr)) {
	              array_push($data_array, $order->customer !== null ? $order->customer->reference_name : 'N.A');
	          }
	          if (!in_array('6',$not_visible_arr)) {
	              array_push($data_array, $order->customer !== null ? $order->customer->company : 'N.A');
	          }
	          if (!in_array('7',$not_visible_arr)) {
                  $data = null;
                  if ($item->order_id == null) {
                    $data = '--';
                  }
                  else{
                    $billing = @$item->get_order->customer->getbilling->where('is_default', 1)->first();
                    $data = $billing !== null ? @$billing->tax_id : 'N.A';
                  }
	              array_push($data_array, $data);
	          }
	          if (!in_array('8',$not_visible_arr)) {
                $data = null;
                if ($item->order_id == null) {
                  $data = '--';
                }
                else{
                  $billing = @$item->get_order->customer->getbilling->where('is_default', 1)->first();
                  $data = $billing !== null ? @$billing->title : 'N.A';
                }
                array_push($data_array, $data);
	          }
	          if (!in_array('9',$not_visible_arr)) {
	              array_push($data_array, $order !== null ? ($order->user_id != NULL ? $order->user->name : "N.A") : 'N.A');
	          }
	          if (!in_array('10',$not_visible_arr)) {
	              array_push($data_array, $order->delivery_request_date !== null ? Carbon::parse($order->delivery_request_date)->format('d/m/Y') : 'N.A');
	          }
	          if (!in_array('11',$not_visible_arr)) {
	            $data = null;
	            if ($item->order_id == null) {
	                $data = '--';
	            }
	            $get_order = @$item->get_order;
	            $data = @$get_order->delivery_request_date !== null ? Carbon::parse(@$get_order->delivery_request_date)->format('d/m/Y') : 'N.A';
	              array_push($data_array, $data);
	          }
	          if (!in_array('12',$not_visible_arr)) {
	            $data = null;
	            if ($item->order_id == null) {
	                $data = '--';
	            }
	            $get_order = @$item->get_order;
	            $data = @$get_order->target_ship_date !== null ? Carbon::parse(@$get_order->target_ship_date)->format('d/m/Y') : 'N.A';
	              array_push($data_array, $data);
	          }
	          if (!in_array('13',$not_visible_arr)) {
	              array_push($data_array, $supply_from);
	          }
	          if (!in_array('14',$not_visible_arr)) {
	              array_push($data_array, $item->product->refrence_code);
	          }
	          if (!in_array('15',$not_visible_arr)) {
	              array_push($data_array, $cat_prod);
	          }
	          if (!in_array('16',$not_visible_arr)) {
	              array_push($data_array, $item->product != null ? ($item->product->productType != null ? $item->product->productType->title : '--') : '--');
	          }
	          if (!in_array('17',$not_visible_arr)) {
	              array_push($data_array, $brand);
	          }
	          if (!in_array('18',$not_visible_arr)) {
	              array_push($data_array, $item->product->short_desc);
	          }
	          if (in_array('product_type_2', $product_detail_section))
	          {
	            if (!in_array('19',$not_visible_arr)) {
	                array_push($data_array, $product_type);
	            }
	          }
	          if (in_array('product_type_3', $product_detail_section))
	          {
	            if (!in_array('20',$not_visible_arr)) {
	              array_push($data_array, $product_type_3);
	            }
	          }
	          if (!in_array('21',$not_visible_arr)) {
	              array_push($data_array, $final_available);
	          }
	          if (!in_array('22',$not_visible_arr)) {
	              array_push($data_array, ($item->product != null ? ($item->product->sellingUnits != null ? $item->product->sellingUnits->title : '') : ''));
	          }
	          if (!in_array('23',$not_visible_arr)) {
	              array_push($data_array, $qty);
	          }
	          if (!in_array('24',$not_visible_arr)) {
	              array_push($data_array, $pieces);
	          }
	          if (!in_array('25',$not_visible_arr)) {
	              array_push($data_array, ($item->unit_price !== null ? round($item->unit_price,2) : 'N.A'));
	          }
	          if (!in_array('26',$not_visible_arr)) {
	              array_push($data_array, $item->discount !== NULL ? $item->discount : "N.A");
	          }

	          if($role_id != 3 && $role_id != 4 && $role_id != 6){
	              if (!in_array('27',$not_visible_arr)) {
	                  array_push($data_array, $cogs);
	              }
	              if (!in_array('28',$not_visible_arr)) {
	                  array_push($data_array, $total_cogs);
	              }
	          }
	          if (!in_array('29',$not_visible_arr)) {
	              array_push($data_array, $item->total_price != null ? round($item->total_price,2) : 'N.A');
	          }
	          if (!in_array('30',$not_visible_arr)) {
	              array_push($data_array, $item->total_price_with_vat !== null ? round($item->total_price_with_vat,2) : 'N.A');
	          }
	          if (!in_array('31',$not_visible_arr)) {
	              array_push($data_array, $vat_amount_total);
	          }
	          if (!in_array('32',$not_visible_arr)) {
	              array_push($data_array, $item->vat !== null ? $item->vat.' %' : 'N.A');
	          }
	          if (!in_array('33',$not_visible_arr)) {
	              array_push($data_array, $note_html);
	          }
	          if (!in_array('34',$not_visible_arr)) {
	            $data = null;
	            if ($item->order_id == null) {
	                $data = '--';
	            }
	            $sales = $item->total_price;
	            $cogs = $item->actual_cost * $item->qty_shipped;
	            $margin = $sales - $cogs;
	            $data = number_format($margin, 2,'.',',');
	            array_push($data_array, $data);
	          }
	          if (!in_array('35',$not_visible_arr)) {
	            $data = null;
	            if ($item->order_id == null) {
	                $data = '--';
	            }
	            $sales = $item->total_price;

	            if ($sales != 0) {
	                $cogs = $item->actual_cost * $item->qty_shipped;
	                $margin = (($sales - $cogs)/$sales) * 100;
	                $data = number_format($margin, 2,'.',',') . ' %';
	            }
	            else{
	                $data = '0 %';
	            }
	            array_push($data_array, $data);
	          }

	          if($getCategories->count() > 0){
	              $arr_index = 0;
	              $increment = 36;
	              $ids=[];
	              foreach($getCategories as $cat){
	                  $current_qty  = substr($cat->title, 0, 3);
	                  if(array_key_exists($arr_index,$customer_categories_array))
	                  {
	                       if (!in_array($increment,$not_visible_arr)) {
	                          array_push($data_array, $customer_categories_array[$arr_index] != null ? number_format((float)$customer_categories_array[$arr_index], 3, '.', ''):'0');
	                      }
	                  }
	                  $increment+=1;
	                  $arr_index++;
	              }
	          }
	        	fputcsv($handle, $data_array);
	        }
          }

         });
		// ends
		fclose($handle);
		return true;

	}
}
