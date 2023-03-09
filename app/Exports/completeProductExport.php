<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;
use App\Models\Common\Product;

class completeProductExport implements ShouldAutoSize, WithEvents, FromQuery, WithHeadings, WithMapping
{
    protected $query = null;
    protected $getWarehouses = null;
    protected $getCategoriesSuggestedPrices = null;
    protected $getCategories = null;
    protected $row_color = null;
    protected $not_visible_arr = null;
    protected $global_terminologies=[];
    protected $hide_hs_description=null;
    protected $customer_suggested_prices_array=null;
    protected $product_detail_section=null;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($query, $not_visible_arr,$global_terminologies,$hide_hs_description,$getWarehouses,$getCategories,$getCategoriesSuggestedPrices,$customer_suggested_prices_array, $product_detail_section)
    {
        $this->query = $query;
        $this->getWarehouses = $getWarehouses;
        $this->getCategories = $getCategories;
        $this->getCategoriesSuggestedPrices = $getCategoriesSuggestedPrices;
        $this->not_visible_arr = $not_visible_arr;
        $this->global_terminologies = $global_terminologies;
        $this->hide_hs_description = $hide_hs_description;
        $this->customer_suggested_prices_array = $customer_suggested_prices_array;
        $this->product_detail_section = $product_detail_section;


    }

    public function query()
    {
        $query = $this->query;
        return $query;
    }

    public function headings() : array
    {
        $getWarehouses = $this->getWarehouses;
        $getCategories = $this->getCategories;
        $getCategoriesSuggestedPrices = $this->getCategoriesSuggestedPrices;
        $not_visible_arr = $this->not_visible_arr;
        $global_terminologies = $this->global_terminologies;
        $hide_hs_description=$this->hide_hs_description;
        $customer_suggested_prices_array=$this->customer_suggested_prices_array;
        $product_detail_section =$this->product_detail_section;
        $heading_array = [];


        if(!in_array('2',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['our_reference_number']);
        }
        if($hide_hs_description==0)

        {
            if(!in_array('3',$not_visible_arr))
            {
                array_push($heading_array, 'Hs Description');
            }
        }

        if(!in_array('4',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['suppliers_product_reference_no']);
        }
        if(!in_array('5',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['category'] . '/' . $global_terminologies['subcategory']);

        }
        if(!in_array('6',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['product_description']);
        }
        if(!in_array('7',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['note_two']);
        }
        if(!in_array('8',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['product_note_3']);
        }

        if(!in_array('10',$not_visible_arr))
        {
            array_push($heading_array, 'Billed Unit');
        }
        if(!in_array('11',$not_visible_arr))
        {
            array_push($heading_array, 'Selling Unit');
        }
        if(!in_array('12',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['type']);
        }
        if (in_array('product_type_2', $product_detail_section))
        {
            if(!in_array('13',$not_visible_arr))
            {
                if(!array_key_exists('product_type_2', $global_terminologies))
                {
                    array_push($heading_array, 'Type 2');
                }
                else
                {
                    array_push($heading_array, $global_terminologies['product_type_2']);
                }
            }
        }
        if (in_array('product_type_3', $product_detail_section))
        {
            if(!in_array('14',$not_visible_arr))
            {
                if(!array_key_exists('product_type_3', $global_terminologies))
                {
                    array_push($heading_array, 'Type 3');
                }
                else
                {
                    array_push($heading_array, $global_terminologies['product_type_3']);
                }
            }
        }
        if(!in_array('15',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['brand']);
        }
        if(!in_array('16',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['temprature_c']);
        }
        if(!in_array('17',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['extra_tax_per_billed_unit']);
        }

        if(!in_array('18',$not_visible_arr))
        {
            array_push($heading_array, 'Import Tax (Book) %');
        }
        if(!in_array('19',$not_visible_arr))
        {
            array_push($heading_array, 'VAT');
        }
        if(!in_array('20',$not_visible_arr))
        {
            array_push($heading_array, 'Default/Last Supplier');
        }
        if(!in_array('21',$not_visible_arr))
        {
            array_push($heading_array, 'Supplier Country');
        }
        if(!in_array('22',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['supplier_description']);
        }
        if(!in_array('23',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['purchasing_price'] . '(EUR)');
        }
        if(!in_array('24',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['purchasing_price'] . '(THB)');
        }
        if(!in_array('25',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['freight_per_billed_unit']);
        }
        if(!in_array('26',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['landing_per_billed_unit']);
        }
        if(!in_array('27',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['cost_price']);
        }
        if(!in_array('28',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['unit_conversion_rate']);
        }
        if(!in_array('29',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['net_price'] . '/unit (THB)');
        }
        if(!in_array('30',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['avg_units_for-sales']);
        }
        if(!in_array('31',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['expected_lead_time_in_days']);
        }
        if(!in_array('32',$not_visible_arr))
        {
            array_push($heading_array, 'Last Update Price');
        }
        if(!in_array('33',$not_visible_arr))
        {
            array_push($heading_array, 'Total Visible Stock');
        }
        if(!in_array('34',$not_visible_arr))
        {
            array_push($heading_array, 'On Water');
        }

        if(!in_array('35',$not_visible_arr))
        {
            array_push($heading_array, 'On Supplier');
        }

        if(!in_array('36',$not_visible_arr))
        {
            array_push($heading_array, 'Title');
        }
        if(!in_array('37',$not_visible_arr))
        {
            array_push($heading_array, 'Min Order Qty');
        }
        if(!in_array('38',$not_visible_arr))
        {
            array_push($heading_array, 'Max Order Qty');
        }
        if(!in_array('39',$not_visible_arr))
        {
            array_push($heading_array, 'Dimension');
        }
        if(!in_array('40',$not_visible_arr))
        {
            array_push($heading_array, 'E-commerce Product weight per unit');
        }
        if(!in_array('41',$not_visible_arr))
        {
            array_push($heading_array, 'E-Com Long Description');

        }
        if(!in_array('42',$not_visible_arr))
        {
            array_push($heading_array, 'E-Com Selling Price');
        }
        if(!in_array('43',$not_visible_arr))
        {
            array_push($heading_array, 'E-Com Discount Price');
        }
        if(!in_array('44',$not_visible_arr))
        {
            array_push($heading_array, 'E-Com Discount Expiry');
        }
        if(!in_array('45',$not_visible_arr))
        {
            array_push($heading_array, 'E-Com Selling Unit');
        }
        if(!in_array('46',$not_visible_arr))
        {
            array_push($heading_array, 'E-Com Selling Unit Conversion Rate');
        }
        if(!in_array('47',$not_visible_arr))
        {
            array_push($heading_array, 'E-Com COGS Price');
        }
        if(!in_array('48',$not_visible_arr))
        {
            array_push($heading_array, 'E-Com Status');
        }
        //



        $inc = 49;
        if($getWarehouses->count() > 0){
            foreach($getWarehouses as $warehouse)
            {
                if(!in_array($inc++,$not_visible_arr))
                {
                    array_push($heading_array, $warehouse->warehouse_title . '
                    ' . $global_terminologies['current_qty']);
                }
                if(!in_array($inc++,$not_visible_arr))
                {
                    array_push($heading_array, $warehouse->warehouse_title . ' Available Qty');
                }
                if(!in_array($inc++,$not_visible_arr))
                {
                    array_push($heading_array, $warehouse->warehouse_title . ' Reserved Qty');
                }

                // $inc+=3;
            }
        }

        if($getCategories->count() > 0)
        {
            foreach($getCategories as $cat)
            {
                if(!in_array($inc++,$not_visible_arr))
                {
                    array_push($heading_array, $cat->title . ' ( Fixed Price )');
                }
                // $inc+=1;
            }
        }


        if($getCategoriesSuggestedPrices->count() > 0)
        {
            foreach($getCategoriesSuggestedPrices as $cat)
            {
                if(!in_array($inc++,$not_visible_arr))
                {
                    array_push($heading_array, $cat->title .' ( Suggested Price )');
                }
                // $inc+=1;
            }

        }
        return $heading_array;
    }

    public function map($item) : array
    {
        $getWarehouses = $this->getWarehouses;
        $getCategories = $this->getCategories;
        $getCategoriesSuggestedPrices = $this->getCategoriesSuggestedPrices;
        $not_visible_arr = $this->not_visible_arr;
        $global_terminologies = $this->global_terminologies;
        $hide_hs_description=$this->hide_hs_description;
        $customer_suggested_prices_array=$this->customer_suggested_prices_array;
        $product_detail_section=$this->product_detail_section;

        $productObj=new Product;


        $data_array = [];

        $getProductDefaultSupplier = $item->supplier_products->where('supplier_id',$item->supplier_id)->first();

        if($getWarehouses->count() > 0)
        {
          foreach ($getWarehouses as $warehouse)
          {
            $warehouse_product = $item->warehouse_products->where('warehouse_id',$warehouse->id)->first();
            $current_qty  =  (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity:'0';
            $reservd_qty=  (@$warehouse_product->reserved_quantity != null || @$warehouse_product->ecommerce_reserved_quantity != null) ? @$warehouse_product->reserved_quantity + @$warehouse_product->ecommerce_reserved_quantity:'0';
            $available_qty=(@$warehouse_product->available_quantity != null) ? @$warehouse_product->available_quantity:'0';

            $warehosue_c_r_array[] = [
                "".substr($warehouse->warehouse_title, 0, 3).'_current_qty'.""  => $current_qty,

                "".substr($warehouse->warehouse_title, 0, 3).'_available_qty'.""  => $available_qty,

                "".substr($warehouse->warehouse_title, 0, 3).'_reserved_qty'."" => $reservd_qty
            ];
          }
        }

        if($getCategories->count() > 0)
        {
          foreach ($getCategories as $cat)
          {
            $fixed_value = $item->product_fixed_price()->where('product_id',$item->id)->where('customer_type_id',$cat->id)->first();
            $value = $fixed_value != null ? @$fixed_value->fixed_price : '0.00';
            $va = number_format($value,3,'.','');

            $customer_categories_array[] = [
              "".substr($cat->title, 0, 3).""  => $va,
            ];
          }
        }

        //Customer Category Dynamic Columns Starts Here
        if($getCategoriesSuggestedPrices->count() > 0)
        {
          foreach ($getCategoriesSuggestedPrices as $cat)
          {
            $selling_price = $item->selling_price;
            $suggest_price = $item->customer_type_product_margins()->where('product_id',$item->id)->where('customer_type_id',$cat->id)->first();
            $default_value = $suggest_price->default_value;

            $final_value = $selling_price + ($selling_price * ($default_value/100));

            $v = number_format($final_value,3,'.','');

            $customer_suggested_prices_array[] = [
              "".substr($cat->title, 0, 3).""  => $v,
            ];
          }
        }

        //To find total visible stock
        $visible = 0;
        $start = 49;
        foreach ($getWarehouses as $warehouse) {
          if(!in_array($start,$not_visible_arr))
          {
            $warehouse_product = $item->warehouse_products->where('warehouse_id',$warehouse->id)->first();
            $qty = ($warehouse_product->current_quantity != null) ? $warehouse_product->current_quantity: 0;
            $visible += $qty;
          }

          $start += 3;
        }

        $prodFixPrice   = $productObj->getDataOfProductMargins($item->id, 1, "prodFixPrice");
        $formated_value = number_format(@$prodFixPrice->fixed_price,3,'.',',');

        $on_water =$on_airplane = $on_domestic = 0;
        $on_water = $item->getOnWater($item->id);
        $on_water = ($on_water != null) ? $on_water : 0;

        $on_supplier = Product::getOnSupplier($item->id);
        $on_supplier = ($on_supplier != null) ? $on_supplier : 0;

        // $qty_ordered = Product::getQtyOrdered($item->id);
        // $qty_ordered = ($qty_ordered != null) ? $qty_ordered : 0;

        // $on_airplane = $item->getOnAirplane($item->id);
        // $on_airplane = ($on_airplane != null) ? $on_airplane : 0;

        // $on_domestic = $item->getOnDomestic($item->id);
        // $on_domestic = ($on_domestic != null) ? $on_domestic : 0;

        if(!in_array('2',$not_visible_arr))
        {
            $data = $item->refrence_code != null ? $item->refrence_code : '--';
            array_push($data_array, $data);
        }
        if($hide_hs_description==0)

        {
            if(!in_array('3',$not_visible_arr))
            {
                $data = $item->hs_description != null ? $item->hs_description : '--';
                array_push($data_array, $data);
            }
        }

        if(!in_array('4',$not_visible_arr))
        {
            $data = @$getProductDefaultSupplier->product_supplier_reference_no ? @$getProductDefaultSupplier->product_supplier_reference_no : '--';
            array_push($data_array, $data);
        }
        if(!in_array('5',$not_visible_arr))
        {
            $category = $item->productCategory ? $item->productCategory->title : '--';
            $sub_category = $item->productSubCategory ? $item->productSubCategory->title : '--';
            array_push($data_array, $category . '/'. $sub_category);
        }
        if(!in_array('6',$not_visible_arr))
        {
            $data = $item->short_desc != null ? $item->short_desc : '--';
            array_push($data_array, $data);
        }
        if(!in_array('7',$not_visible_arr))
        {
            $data = $item->product_notes != null ? $item->product_notes : '--';
            array_push($data_array, $data);
        }
        if(!in_array('8',$not_visible_arr))
        {
            $data = $item->product_note_3 != null ? $item->product_note_3 : '--';
            array_push($data_array, $data);
        }

        if(!in_array('10',$not_visible_arr))
        {
            $data = $item->units != null ? $item->units->title : '--';
            array_push($data_array, $data);
        }
        if(!in_array('11',$not_visible_arr))
        {
            $data = $item->sellingUnits != null ? $item->sellingUnits->title : '--';
            array_push($data_array, $data);
        }
        if(!in_array('12',$not_visible_arr))
        {
            $data = $item->productType != null ? $item->productType->title : '--';
            array_push($data_array, $data);
        }
        if (in_array('product_type_2', $product_detail_section))
        {
            if(!in_array('13',$not_visible_arr))
            {
                $data = $item->productType2 != null ? $item->productType2->title : '--';
                array_push($data_array, $data);
            }
        }
        if (in_array('product_type_3', $product_detail_section))
        {
            if(!in_array('14',$not_visible_arr))
            {
                $data = $item->productType3 != null ? $item->productType3->title : '--';
                array_push($data_array, $data);
            }
        }
        if(!in_array('15',$not_visible_arr))
        {
            $data = $item->brand != null ? $item->brand : '--';
            array_push($data_array, $data);
        }
        if(!in_array('16',$not_visible_arr))
        {
            $data = $item->product_temprature_c != null ? $item->product_temprature_c : '--';
            array_push($data_array, $item->product_temprature_c);
        }
        if(!in_array('17',$not_visible_arr))
        {
            $getProductDefaultSupplier = $item->supplier_products->where('supplier_id', @$item->supplier_id)->first();
            $data = $getProductDefaultSupplier && $getProductDefaultSupplier->extra_tax != null ? $getProductDefaultSupplier->extra_tax : '--';
            array_push($data_array, $data);
        }

        if(!in_array('18',$not_visible_arr))
        {
            $data = $item->import_tax_book != null ? $item->import_tax_book . '%' : '--';
            array_push($data_array, $data);
        }
        if(!in_array('19',$not_visible_arr))
        {
            $data = $item->vat != null ? $item->vat . '%' : '--';
            array_push($data_array, $data);
        }
        if(!in_array('20',$not_visible_arr))
        {
            $data = $item->def_or_last_supplier != null ? $item->def_or_last_supplier->reference_name : '--';
            array_push($data_array, $data);
        }
        if(!in_array('21',$not_visible_arr))
        {
            $data = $item->def_or_last_supplier->getcountry != null ? $item->def_or_last_supplier->getcountry->name : '--';
            array_push($data_array, $data);
        }
        if(!in_array('22',$not_visible_arr))
        {
            $data = @$getProductDefaultSupplier->supplier_description != null ? @$getProductDefaultSupplier->supplier_description : '--';
            array_push($data_array, $data);
        }
        if(!in_array('23',$not_visible_arr))
        {
            $data = @$getProductDefaultSupplier->buying_price != null ? number_format((float)@$getProductDefaultSupplier->buying_price, 3, '.', '') : '0';
            $symbol = $item->def_or_last_supplier != null && $item->def_or_last_supplier->getCurrency != null ? $item->def_or_last_supplier->getCurrency->currency_symbol : '';
            if ($symbol != null && $symbol != '')
            {
                array_push($data_array, $data . '/' . $symbol);
            }
            else
            {
                array_push($data_array, $data);
            }
        }
        if(!in_array('24',$not_visible_arr))
        {
            $data = @$getProductDefaultSupplier->buying_price_in_thb != null ? number_format((float)@$getProductDefaultSupplier->buying_price_in_thb, 3, '.', '') : '--';
            array_push($data_array, $data);
        }
        if(!in_array('25',$not_visible_arr))
        {
            $data = @$getProductDefaultSupplier->freight != null ? @$getProductDefaultSupplier->freight : '--';
            array_push($data_array, $data);
        }
        if(!in_array('26',$not_visible_arr))
        {
            $data = @$getProductDefaultSupplier->landing != null ? @$getProductDefaultSupplier->landing : '--';
            array_push($data_array, $data);
        }
        if(!in_array('27',$not_visible_arr))
        {
            $data = $item->total_buy_unit_cost_price != null ? number_format((float)$item->total_buy_unit_cost_price, 3, '.', '') : '--';
            array_push($data_array, $data);
        }
        if(!in_array('28',$not_visible_arr))
        {
            $data = $item->unit_conversion_rate != null ? number_format((float)$item->unit_conversion_rate, 3, '.', '') : '--';
            array_push($data_array, $data);
        }
        if(!in_array('29',$not_visible_arr))
        {
            $data = $item->selling_price != null ? number_format((float)$item->selling_price, 3, '.', '') : '--';
            array_push($data_array, $data);
        }
        if(!in_array('30',$not_visible_arr))
        {
            $data = $item->weight != null ? $item->weight : '--';
            array_push($data_array, $data);
        }
        if(!in_array('31',$not_visible_arr))
        {
            $data = @$getProductDefaultSupplier->leading_time != null ? @$getProductDefaultSupplier->leading_time : '--';
            array_push($data_array, $data);
        }
        if(!in_array('32',$not_visible_arr))
        {
            $data = $item->last_price_updated_date!= null ? Carbon::parse($item->last_price_updated_date)->format('d/m/Y') : '--';
            array_push($data_array, $data);
        }
        if(!in_array('33',$not_visible_arr))
        {
            $data = $visible != null ? number_format((float)$visible, 3, '.', '') : '--';
            array_push($data_array, $data);
        }
        if(!in_array('34',$not_visible_arr))
        {
            $data = $on_water != null ? $on_water : '--';
            array_push($data_array, $data);
        }
        if(!in_array('35',$not_visible_arr))
        {
            $data = $on_supplier != null ? $on_supplier : '--';
            array_push($data_array, $data);
        }
        if(!in_array('36',$not_visible_arr))
        {
            $data = $item->name != null ? $item->name : '--';
            array_push($data_array, $data);
        }
        if(!in_array('37',$not_visible_arr))
        {
            $data = $item->min_o_qty != null ? $item->min_o_qty : '--';
            array_push($data_array, $data);
        }
        if(!in_array('38',$not_visible_arr))
        {
            $data = $item->max_o_qty != null ? $item->max_o_qty : '--';
            array_push($data_array, $data);
        }
        if(!in_array('39',$not_visible_arr))
        {
            $html = $item->length !== null ? $item->length.' cm' : '--';
            $html .= ' x ';
            $html .= $item->width !== null ? $item->width.' cm' : '--';
            $html .= ' x ';
            $html .= $item->height !== null ? $item->height.' cm' : '--';

            array_push($data_array, $html);
        }
        if(!in_array('40',$not_visible_arr))
        {
            $data = $item->ecom_product_weight_per_unit != null ? $item->ecom_product_weight_per_unit.' kg' : '--';
            array_push($data_array, $data);
        }
        if(!in_array('41',$not_visible_arr))
        {
            $data = $item->long_desc != null ? $item->long_desc : '--';
            array_push($data_array, $data);
        }

        if(!in_array('42',$not_visible_arr))
        {
            $data = $item->ecommerce_price != null ? $item->ecommerce_price : '--';
            array_push($data_array, $data);
        }
        if(!in_array('43',$not_visible_arr))
        {
            $data = $item->discount_price != null ? $item->discount_price : '--';
            array_push($data_array, $data);
        }
        if(!in_array('44',$not_visible_arr))
        {
            $data = $item->discount_expiry_date != null ? $item->discount_expiry_date : '--';
            array_push($data_array, $data);
        }
        if(!in_array('45',$not_visible_arr))
        {
            $data = @$item->ecomSellingUnits != null ? @$item->ecomSellingUnits->title : '--';
            array_push($data_array, $data);
        }
        if(!in_array('46',$not_visible_arr))
        {
            $data = $item->selling_unit_conversion_rate == null ? '--' : $item->selling_unit_conversion_rate;
            array_push($data_array, $data);
        }
        if(!in_array('47',$not_visible_arr))
        {
            $data = number_format($item->selling_unit_conversion_rate * $item->selling_price,3,'.','');
            array_push($data_array, $data);
        }
        if(!in_array('48',$not_visible_arr))
        {
            $data = $item->ecommerce_enabled == 0 ? "disabled" : "enabled";
            array_push($data_array, $data);
        }
        // if(!in_array('45',$not_visible_arr))
        // {
        //     $data = $qty_ordered != null ? $qty_ordered : '--';;
        //     array_push($data_array, $data);
        // }
        // if(!in_array('30',$not_visible_arr)) On Airplane
        // if(!in_array('31',$not_visible_arr)) On Delivery


        if($getWarehouses->count() > 0){
            $increment = 49;
            $arr_index = 0;
            $ids=[];
            foreach($getWarehouses as $warehouse)
            {
                $current_qty  = substr($warehouse->warehouse_title, 0, 3).'_current_qty';
                $available_qty = substr($warehouse->warehouse_title, 0, 3).'_available_qty';
                $reserved_qty = substr($warehouse->warehouse_title, 0, 3).'_reserved_qty';
                if(array_key_exists($arr_index,$warehosue_c_r_array))
                {
                    if(!in_array($increment,$not_visible_arr))
                    {
                        $data = $warehosue_c_r_array[$arr_index][$current_qty] != null ? number_format((float)$warehosue_c_r_array[$arr_index][$current_qty], 3, '.', ''):'0';
                        array_push($data_array, $data);
                    }
                    if(!in_array($increment+1,$not_visible_arr))
                    {
                        $data = $warehosue_c_r_array[$arr_index][$available_qty]!= null ? number_format((float)$warehosue_c_r_array[$arr_index][$available_qty], 3, '.', ''):'0';
                        array_push($data_array, $data);
                    }
                    if(!in_array($increment+2,$not_visible_arr))
                    {
                        $data = $warehosue_c_r_array[$arr_index][$reserved_qty]!= null ? number_format((float)$warehosue_c_r_array[$arr_index][$reserved_qty], 3, '.', ''):'0';
                        array_push($data_array, $data);
                    }
                }
                $arr_index++;
                $increment+=3;
            }
        }

        if($getCategories->count() > 0)
        {
            $arr_index = 0;
            foreach($getCategories as $cat)
            {
                $current_qty  = substr($cat->title, 0, 3);
                if(array_key_exists($arr_index,$customer_categories_array))
                {
                    if(!in_array($increment,$not_visible_arr))
                    {
                        $data = $customer_categories_array[$arr_index][$current_qty] != null ? number_format((float)$customer_categories_array[$arr_index][$current_qty], 3, '.', ''):'0';
                        array_push($data_array, $data);
                    }
                }
                $increment+=1;
                $arr_index++;
            }
        }


        if($getCategoriesSuggestedPrices->count() > 0)
        {
            // dd($getCategoriesSuggestedPrices);
            $arr_index = 0;
            foreach($getCategoriesSuggestedPrices as $cat)
            {
                $current_qty  = substr($cat->title, 0, 3);
                if(array_key_exists($arr_index,$customer_suggested_prices_array))
                {

                        if(!in_array($increment,$not_visible_arr))
                        {
                            $data = $customer_suggested_prices_array[$arr_index][$current_qty] != null ? number_format((float)$customer_suggested_prices_array[$arr_index][$current_qty], 3, '.', ''):'0';
                            array_push($data_array, $data);
                        }

                }
                $increment+=1;
                $arr_index++;
            }

        }
        return $data_array;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // $event->sheet->getStyle('A1:V1')->applyFromArray([
                //     'font' => [
                //         'bold' => true
                //     ]
                // ]);
                // $event->sheet->getRowDimension(1)->setVisible(false);
            },
        ];
    }

}
