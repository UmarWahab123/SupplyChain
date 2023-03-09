<?php

namespace App\Exports;

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class SoldProductExportSupplierMargin implements ShouldAutoSize, WithEvents, FromQuery, WithHeadings, WithMapping
{
	protected $query = null;
	protected $global_terminologies = null;
    protected $row_color = null;
    protected $available_stock = null;
    protected $not_visible_arr = null;
    protected $getCategories = null;
    protected $role_id = null;
    protected $stock = null;
    protected $request = null;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($query,$global_terminologies,$not_visible_arr,$available_stock,$getCategories,$role_id,$stock,$request)
    {
        $this->query = $query;
        $this->global_terminologies = $global_terminologies;
        $this->not_visible_arr = $not_visible_arr;
        $this->available_stock = $available_stock;
        $this->getCategories = $getCategories;
        $this->role_id = $role_id;
        $this->stock = $stock;
        $this->request = $request;
    }
    public function query()
    {
        $query = $this->query;
        return $query;
    }
    public function map($item) : array {
        $not_visible_arr = $this->not_visible_arr;
        $role_id = $this->role_id;
        $getCategories = $this->getCategories;
        $request = $this->request;
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

          if(@$item->get_order_product->supplier_id != NULL && @$item->get_order_product->from_warehouse_id == NULL)
          {
            @$item->get_order_product->from_supplier->reference_name;
          }

          elseif(@$item->get_order_product->from_warehouse_id != NULL && @$item->get_order_product->supplier_id == NULL)
          {
            $supply_from = @$item->get_order_product->from_warehouse->warehouse_title;
          }
          else
          {
            $supply_from = 'N.A';
          }

          $qty = ($item->quantity !== null ? round($item->quantity,2) : 'N.A');


          $pieces = '--';

          $quantity = $item->quantity;
          $total_cost = $item->total_cost;
          $unit_cost = $item->quantity != 0 ? number_format($item->total_cost / $item->quantity,2) : 0;
          $cogs = $unit_cost;

          $total_cogs = ($item->total_cost !== null ? number_format((float)$item->total_cost, 2,'.','') : 'N.A');


          if(@$item->get_order_product->brand!=null)
	        {
	          $brand = $item->get_order_product->brand;
	        }
	        elseif(@$item->get_order_product->product_id!=null)
	        {
	          if(@$item->get_order_product->product->brand!=null)
	          {
	            $brand = @$item->get_order_product->product->brand ?? '--';
	          }
	          else
	          {
	            $brand = '--';

	          }
	        }
	        else
	        {
	          $brand = '--';
	        }
          $warehouse_id = $request['warehouse_id_exp'];
          $final_available = 0;
          if($warehouse_id != null)
          {
            $final_available = @$item->get_order_product->product != null ? @$item->get_order_product->product->get_stock($item->get_order_product->product_id, $warehouse_id) : 0;
          }
          else
          {
            $warehouse_product = @$item->get_order_product->warehouse_products->sum('available_quantity');
            $final_available = $warehouse_product != null ? number_format($warehouse_product,3,'.','') : 0;
          }

          $product_type = @$item->get_order_product->product != null ? (@$item->get_order_product->product->productType2 != null ? @$item->get_order_product->product->productType2->title : '--') : '--';

          $product_type_3 = @$item->get_order_product->product != null ? (@$item->get_order_product->product->productType3 != null ? @$item->get_order_product->product->productType3->title : '--') : '--';
          if($item->vat_out != null)
          {
            $vat_amount_total = number_format($item->vat_out,2,'.','');
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
              $fixed_value = @$item->get_order_product->product->product_fixed_price->where('customer_type_id',$cat->id)->first();
              $value = $fixed_value !== null ? $fixed_value->fixed_price : 0;
              $va = round($value,3);
              array_push($customer_categories_array, $va);
            }
          }

          $order_notes = @$item->get_order_product->get_order_product_notes;
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
          $cat_prod = @$item->get_order_product->product != null ? (@$item->get_order_product->product->productCategory != null ? $item->get_order_product->product->productCategory->title : '') : '';
          $cat_prod .= @$item->get_order_product->product != null ? (@$item->get_order_product->product->productSubCategory != null ? (' / '.@$item->get_order_product->product->productSubCategory->title) : '') : '';

          if($item->order_id == null)
	        {
	          $final = '--';
	        }
	        $sales = $item->sales;
	        $vat = @$item->get_order_product->vat ?? 0;
	        $val = ( 100 - $vat ) / 100;
	        $final = $sales * $val;
	        $final = @$final !== null ? number_format((float)$final,2,'.','') : 'N.A';


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
              array_push($data_array, $order->customer !== null ? $order->customer->reference_name : 'N.A');
          }
          if (!in_array('5',$not_visible_arr)) {
              array_push($data_array, $order !== null ? ($order->user_id != NULL ? $order->user->name : "N.A") : 'N.A');
          }
          if (!in_array('6',$not_visible_arr)) {
              array_push($data_array, $order->delivery_request_date !== null ? Carbon::parse($order->delivery_request_date)->format('d/m/Y') : 'N.A');
          }
          if (!in_array('7',$not_visible_arr)) {
              array_push($data_array, $item->created_at !== null ? Carbon::parse($item->created_at)->format('d/m/Y') : 'N.A');
          }
          if (!in_array('8',$not_visible_arr)) {
              array_push($data_array, $supply_from);
          }
          if (!in_array('9',$not_visible_arr)) {
              array_push($data_array, @$item->get_order_product->product->refrence_code);
          }
          if (!in_array('10',$not_visible_arr)) {
              array_push($data_array, $cat_prod);
          }
          if (!in_array('11',$not_visible_arr)) {
              array_push($data_array, @$item->get_order_product->product != null ? (@$item->get_order_product->product->productType != null ? @$item->get_order_product->product->productType->title : '--') : '--');
          }
          if (!in_array('12',$not_visible_arr)) {
              array_push($data_array, $brand);
          }
          if (!in_array('13',$not_visible_arr)) {
              array_push($data_array, @$item->get_order_product->product->short_desc);
          }
          if (!in_array('14',$not_visible_arr)) {
              array_push($data_array, $product_type);
          }
          if (!in_array('15',$not_visible_arr)) {
            array_push($data_array, $product_type_3);
        }
          if (!in_array('16',$not_visible_arr)) {
              array_push($data_array, $final_available);
          }
          if (!in_array('17',$not_visible_arr)) {
              array_push($data_array, (@$item->get_order_product->product != null ? (@$item->get_order_product->product->sellingUnits != null ? @$item->get_order_product->product->sellingUnits->title : '') : ''));
          }
          if (!in_array('18',$not_visible_arr)) {
              array_push($data_array, $qty);
          }
          if (!in_array('19',$not_visible_arr)) {
              array_push($data_array, $pieces);
          }
          if (!in_array('20',$not_visible_arr)) {
              array_push($data_array, ($item->get_order_product->unit_price !== null ? round($item->get_order_product->unit_price,2) : 'N.A'));
          }
          if (!in_array('21',$not_visible_arr)) {
              array_push($data_array, $item->get_order_product->discount !== NULL ? $item->get_order_product->discount : "N.A");
          }

          if($role_id != 3 && $role_id != 4 && $role_id != 6){
              if (!in_array('22',$not_visible_arr)) {
                  array_push($data_array, $cogs);
              }
              if (!in_array('23',$not_visible_arr)) {
                  array_push($data_array, $total_cogs);
              }
          }
          if (!in_array('24',$not_visible_arr)) {
              array_push($data_array, $final);
          }
          if (!in_array('25',$not_visible_arr)) {
              array_push($data_array, $item->sales !== null ? round($item->sales,2) : 'N.A');
          }
          if (!in_array('26',$not_visible_arr)) {
              array_push($data_array, $vat_amount_total);
          }
          if (!in_array('27',$not_visible_arr)) {
              array_push($data_array, @$item->get_order_product->vat !== null ? $item->get_order_product->vat.' %' : 'N.A');
          }
          if (!in_array('28',$not_visible_arr)) {
              array_push($data_array, $note_html);
          }

          if($getCategories->count() > 0){
              $arr_index = 0;
              $increment = 29;
              $ids=[];
              foreach($this->getCategories as $cat){
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
        }


        return $data_array;
    }
    public function headings(): array
    {
        $not_visible_arr = $this->not_visible_arr;
        $global_terminologies = $this->global_terminologies;
        $role_id = $this->role_id;

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
            array_push($headings_array, 'Customer');
        }
        if (!in_array('5',$not_visible_arr)) {
            array_push($headings_array, 'Sale Person');
        }
        if (!in_array('6',$not_visible_arr)) {
            array_push($headings_array, 'Delivery Date');
        }
        if (!in_array('7',$not_visible_arr)) {
            array_push($headings_array, 'Created Date');
        }
        if (!in_array('8',$not_visible_arr)) {
            array_push($headings_array, $global_terminologies['supply_from']);
        }
        if (!in_array('9',$not_visible_arr)) {
            array_push($headings_array, $global_terminologies['our_reference_number']);
        }
        if (!in_array('10',$not_visible_arr)) {
            array_push($headings_array, $global_terminologies['category'] . '/' . $global_terminologies['subcategory']);
        }
        if (!in_array('11',$not_visible_arr)) {
            array_push($headings_array, 'Product Type');
        }
        if (!in_array('12',$not_visible_arr)) {
            array_push($headings_array, $global_terminologies['brand']);
        }
        if (!in_array('13',$not_visible_arr)) {
            array_push($headings_array, $global_terminologies['product_description']);
        }
        if (!in_array('14',$not_visible_arr)) {
          $type_2 = (!array_key_exists('product_type_2', $global_terminologies)) ? 'Type 2' : $global_terminologies['product_type_2'];
            array_push($headings_array, $type_2);
        }
        if (!in_array('15',$not_visible_arr)) {
            $type_3 = (!array_key_exists('product_type_3', $global_terminologies)) ? 'Type 3' : $global_terminologies['product_type_3'];
              array_push($headings_array, $type_3);
        }
        if (!in_array('16',$not_visible_arr)) {
            array_push($headings_array, $this->available_stock);
        }
        if (!in_array('17',$not_visible_arr)) {
            array_push($headings_array, 'Selling Unit');
        }
        if (!in_array('18',$not_visible_arr)) {
            array_push($headings_array, $global_terminologies['qty']);
        }
        if (!in_array('19',$not_visible_arr)) {
            array_push($headings_array, 'Pieces');
        }
        if (!in_array('20',$not_visible_arr)) {
            array_push($headings_array, 'Unit Price');
        }
        if (!in_array('21',$not_visible_arr)) {
            array_push($headings_array, 'Discount %');
        }

        if($role_id != 3 && $role_id != 4 && $role_id != 6){
            if (!in_array('22',$not_visible_arr)) {
                array_push($headings_array, $global_terminologies['net_price'] . '(THB)');
            }
            if (!in_array('23',$not_visible_arr)) {
                array_push($headings_array, 'Total' . $global_terminologies['net_price'] . '/ unit (THB)');
            }
        }
        if (!in_array('24',$not_visible_arr)) {
            array_push($headings_array, 'Sub Total');
        }
        if (!in_array('25',$not_visible_arr)) {
            array_push($headings_array, 'Total Amount');
        }
        if (!in_array('26',$not_visible_arr)) {
            array_push($headings_array, 'Vat(THB)');
        }
        if (!in_array('27',$not_visible_arr)) {
            array_push($headings_array, 'Vat %');
        }
        if (!in_array('28',$not_visible_arr)) {
            array_push($headings_array, $global_terminologies['note_two']);
        }

        if($this->getCategories->count() > 0)
        {
            $inc = 29;
            foreach($this->getCategories as $cat)
            {
                if(!in_array($inc,$not_visible_arr))
                {
                    array_push($headings_array, $cat->title . '(Fixed Price)');
                }
            }
            $inc++;
        }


        return $headings_array;
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
            },
        ];
    }
}
