<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;
use App\Models\Common\Product;

class completeProductPosExport implements ShouldAutoSize, WithEvents, FromQuery, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($query, $not_visible_arr,$global_terminologies,$hide_hs_description,$getWarehouses,$getCategories,$getCategoriesSuggestedPrices,$customer_suggested_prices_array)
    {
        $this->query = $query;
        $this->getWarehouses = $getWarehouses;
        $this->getCategories = $getCategories;
        $this->getCategoriesSuggestedPrices = $getCategoriesSuggestedPrices;
        $this->not_visible_arr = $not_visible_arr;
        $this->global_terminologies = $global_terminologies;
        $this->hide_hs_description = $hide_hs_description;
        $this->customer_suggested_prices_array = $customer_suggested_prices_array;

    }

    public function query()
    {
        $query = $this->query;
        return $query;
    }

    public function headings() : array
    {
        $not_visible_arr = $this->not_visible_arr;
        $global_terminologies = $this->global_terminologies;
        $hide_hs_description=$this->hide_hs_description;
        $heading_array = [];

        array_push($heading_array, 'product_id');
        array_push($heading_array, 'product_name');
        array_push($heading_array, 'product_code');
        array_push($heading_array, 'product_price');
        array_push($heading_array, 'buying_unit');
        array_push($heading_array, 'unit_conversion_rate');
        array_push($heading_array, 'selling_unit');
        array_push($heading_array, 'product_barcode');
        array_push($heading_array, 'product_category');
        array_push($heading_array, 'product_barcode_type');

        return $heading_array;
    }


    public function map($item) : array
    {
        $not_visible_arr = $this->not_visible_arr;
        $global_terminologies = $this->global_terminologies;
        $hide_hs_description=$this->hide_hs_description;
        $productObj=new Product;
        $data_array = [];
        $getProductDefaultSupplier = $item->supplier_products->where('supplier_id',$item->supplier_id)->first();


        array_push($data_array, $item->id);

        $data = $item->short_desc != null ? $item->short_desc : '--';
        array_push($data_array, $data);

        $data = $item->refrence_code != null ? $item->refrence_code : '--';
        array_push($data_array, $data);

        $data = $item->selling_price == null ? '--' : number_format($item->selling_price,2,'.','');
        array_push($data_array, $data);

        $data = $item->buyingUnits != null ? $item->buyingUnits->title : '--';
        array_push($data_array, $data);

        array_push($data_array, $item->unit_conversion_rate);

        $data = $item->sellingUnits != null ? $item->sellingUnits->title : '--';
        array_push($data_array, $data);

        array_push($data_array, $item->bar_code);

        $category = $item->productCategory ? $item->productCategory->title : '--';
        array_push($data_array, $category);

        array_push($data_array, $item->barcode_type);

        return $data_array;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
            },
        ];
    }


}
