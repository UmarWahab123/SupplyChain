<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductSalesReportExport implements ShouldAutoSize, WithEvents, FromQuery, WithHeadings, WithMapping
{
    protected $query = null;
    protected $role_id = null;
    protected $getCategories = null;
    protected $getWarehouses = null;
    protected $global_terminologies = null;
    protected $not_visible_arr = null;
    protected $warehouse_id = null;
    protected $product_detail_section = null;

    public function __construct($query,$global_terminologies,$role_id,$getCategories,$getWarehouses,$not_visible_arr,$warehouse_id,$product_detail_section)
    {
        $this->query = $query;
        $this->role_id = $role_id;
        $this->not_visible_arr = $not_visible_arr;
        $this->global_terminologies = $global_terminologies;
        $this->getCategories = $getCategories;
        $this->getWarehouses = $getWarehouses;
        $this->warehouse_id = $warehouse_id;
        $this->product_detail_section = $product_detail_section;
    }

    public function query()
    {
        $query = $this->query;
        return $query;
    }


    public function headings() : array
    {
        $role_id = $this->role_id;
        $not_visible_arr = $this->not_visible_arr;
        $global_terminologies = $this->global_terminologies;
        $getCategories = $this->getCategories;
        $getWarehouses = $this->getWarehouses;
        $product_detail_section = $this->product_detail_section;

        $heading_array = [];

        if(!in_array('1',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['our_reference_number']);
        }
        if(!in_array('2',$not_visible_arr))
        {
            if(!array_key_exists('type', $global_terminologies))
            {
                array_push($heading_array, 'Product Type');
            }
            else
            {
                array_push($heading_array, $global_terminologies['type']);
            }
        }
        if (in_array('product_type_2', $product_detail_section))
        {
            if(!in_array('3',$not_visible_arr))
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
            if(!in_array('4',$not_visible_arr))
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
        if(!in_array('5',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['brand']);
        }
        if(!in_array('6',$not_visible_arr))
        {
            array_push($heading_array, $global_terminologies['product_description']);
        }
        if(!in_array('7',$not_visible_arr))
        {
            array_push($heading_array, 'Selling Unit');
        }
        if(!in_array('8',$not_visible_arr))
        {
            array_push($heading_array, 'Total ' . $global_terminologies['qty']);
        }
        if(!in_array('9',$not_visible_arr))
        {
            array_push($heading_array, 'Total Pieces');
        }
        if(!in_array('10',$not_visible_arr))
        {
            array_push($heading_array, 'Sub Total');
        }
        if(!in_array('11',$not_visible_arr))
        {
            array_push($heading_array, 'Total Amount');
        }
        if(!in_array('12',$not_visible_arr))
        {
            array_push($heading_array, 'Vat(THB)');
        }
        if(!in_array('13',$not_visible_arr))
        {
            array_push($heading_array, 'Total Stock');
        }
        if(!in_array('14',$not_visible_arr))
        {
            array_push($heading_array, 'Total Visible Stock');
        }
        if($role_id == 1 || $role_id == 2 || $role_id == 7 || $role_id == 11)
        {
            if(!in_array('15',$not_visible_arr))
            {
                array_push($heading_array, $global_terminologies['net_price'] . ' (THB)');
            }
        }
        if($role_id == 1 || $role_id == 2 || $role_id == 7 || $role_id == 11)
        {
            if(!in_array('16',$not_visible_arr))
            {
                array_push($heading_array, 'Total ' . $global_terminologies['net_price'] . ' (THB)');
            }
        }
        $key=17;
        if($getWarehouses->count() > 0)
        {
            foreach($getWarehouses as $warehouse)
            {
                if(!in_array($key,$not_visible_arr))
                {
                    array_push($heading_array, $warehouse->warehouse_title .' '. $global_terminologies['current_qty']);
                }
                $key++;
            }
        }

        if($getCategories->count() > 0)
        {
            foreach($getCategories as $cat)
            {
                if(!in_array($key,$not_visible_arr))
                {
                    array_push($heading_array, $cat->title .' ( Fixed Price )');
                }
                $key++;
            }
        }
        return $heading_array;
    }
    public function map($item) : array
    {
        $role_id = $this->role_id;
        $not_visible_arr = $this->not_visible_arr;
        $getCategories = $this->getCategories;
        $getWarehouses = $this->getWarehouses;
        $warehouse_id = $this->warehouse_id;
        $product_detail_section = $this->product_detail_section;

        $data_array = [];

        if(!in_array('1',$not_visible_arr))
        {
            array_push($data_array, $item->refrence_code);
        }
        if(!in_array('2',$not_visible_arr))
        {
            $data = $item->productType != null ? $item->productType->title : '--';
            array_push($data_array, $data);
        }
        if (in_array('product_type_2', $product_detail_section))
        {
            if(!in_array('3',$not_visible_arr))
            {
                $data = $item->productType2 != null ? $item->productType2->title : '--';
                array_push($data_array, $data);
            }
        }
        if (in_array('product_type_3', $product_detail_section))
        {
            if(!in_array('4',$not_visible_arr))
            {
                $data = $item->productType3 != null ? $item->productType3->title : '--';
                array_push($data_array, $data);
            }
        }
        if(!in_array('5',$not_visible_arr))
        {
            $data = $item->brand != null ? $item->brand : '--';
            array_push($data_array, $data);
        }
        if(!in_array('6',$not_visible_arr))
        {
            array_push($data_array, $item->short_desc);
        }
        if(!in_array('7',$not_visible_arr))
        {
            $data = $item->sellingUnits != null ? $item->sellingUnits->title : '--';
            array_push($data_array, $data);
        }
        if(!in_array('8',$not_visible_arr))
        {
            $data = number_format($item->QuantityText,2,'.','');
            array_push($data_array, 'Total' . $data);
        }
        if(!in_array('9',$not_visible_arr))
        {
            $data = number_format($item->PiecesText,2,'.','');
            array_push($data_array, $data);
        }
        if(!in_array('10',$not_visible_arr))
        {
            $data = number_format($item->totalPriceSub,2,'.','');
            array_push($data_array, $data);
        }
        if(!in_array('11',$not_visible_arr))
        {
            $data = number_format($item->TotalAmount,2,'.','');
            array_push($data_array, $data);
        }
        if(!in_array('12',$not_visible_arr))
        {
            $data = $item->VatTotalAmount != null ? number_format($item->VatTotalAmount,2,'.','') : '--';
            array_push($data_array, $data);
        }
        if(!in_array('13',$not_visible_arr))
        {
            $data = number_format($item->warehouse_products->sum('current_quantity'),2,'.','');
            array_push($data_array, $data);
        }
        if(!in_array('14',$not_visible_arr))
        {
            $visible = 0;
            $start = 15;
            foreach ($getWarehouses as $warehouse)
            {
                if(!in_array($start,$not_visible_arr))
                {
                   $warehouse_product = $item->warehouse_products->where('warehouse_id',$warehouse->id)->first();
                  $qty = ($warehouse_product->current_quantity != null) ? $warehouse_product->current_quantity: 0;
                  $visible += $qty;
                }

                $start += 1;
            }
            $data = number_format($visible,2,'.','');
            array_push($data_array, $data);
        }
        if($role_id == 1 || $role_id == 2 || $role_id == 7 || $role_id == 11)
        {
            if(!in_array('15',$not_visible_arr))
            {
                $data = (@$item->selling_price!=null) ? number_format((float)@$item->selling_price, 3, '.', ''):'0';
                $data2 = @$item->sellingUnits != null ? @$item->sellingUnits->title : '--';
                array_push($data_array, $data . '/' . $data2);
            }
        }

        if($role_id == 1 || $role_id == 2 || $role_id == 7 || $role_id == 11)
        {
            if(!in_array('16',$not_visible_arr))
            {
                $data = (@$item->totalCogs!=null)?number_format((float)(@$item->totalCogs), 2, '.', ''):'0';
                $data2 = @$item->sellingUnits != null ? @$item->sellingUnits->title : '--';
                array_push($data_array, $data . '/' . $data2);
            }
        }
        $key=17;
        if($getWarehouses->count() > 0)
        {
            foreach($getWarehouses as $warehouse)
            {
                if(!in_array($key,$not_visible_arr))
                {
                    if($warehouse_id == 'all')
                    {
                        $warehouse_product = $item->warehouse_products->where('warehouse_id',$warehouse->id)->first();
                        $qty = ($warehouse_product->current_quantity != null) ? $warehouse_product->current_quantity: 0;
                        $data = number_format($qty,2,'.','');
                        array_push($data_array, $data);
                    }
                    else if ($warehouse_id != null)
                    {
                        $warehouse_product = ($warehouse->id == $warehouse_id) ? $item->warehouse_products->where('warehouse_id',$warehouse_id)->first() : null;
                        if ($warehouse_product != null) {
                            $qty = ($warehouse_product->current_quantity != null) ? $warehouse_product->current_quantity: 0;
                            $data = number_format($qty,2,'.','');
                            array_push($data_array, $data);
                        }
                        else
                        {
                            array_push($data_array, '--');
                        }
                    }
                }
                $key++;
            }
        }

        if($getCategories->count() > 0)
        {
            foreach($getCategories as $cat)
            {
                $fixed_value = $item->product_fixed_price->where('product_id',$item->id)->where('customer_type_id',$cat->id)->first();
                $value = $fixed_value != null ? $fixed_value->fixed_price : 0;
                $va = number_format($value,3,'.','');
                if(!in_array($key,$not_visible_arr))
                {
                    array_push($data_array, $va);
                }
                $key++;
            }
        }
        return $data_array;
    }


    // public function view(): View
    // {

    //     $query = $this->query;
    //     $role_id = $this->role_id;
    //     $not_visible_arr = $this->not_visible_arr;
    //     $global_terminologies = $this->global_terminologies;
    //     $getCategories = $this->getCategories;
    //     $getWarehouses = $this->getWarehouses;
    //     return view('users.exports.product-sales-report-exp', ['query' => $query,'global_terminologies'=>$global_terminologies,'role_id' => $role_id, 'getCategories' => $getCategories,'getWarehouses' => $getWarehouses,'not_visible_arr' => $not_visible_arr]);
    // }



    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:W1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);

            },
        ];
    }
}
