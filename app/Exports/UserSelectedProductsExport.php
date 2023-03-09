<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;

class UserSelectedProductsExport implements ShouldAutoSize, WithEvents, FromQuery, WithHeadings, WithMapping
{
    protected $query = null;
    protected $data = null;
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct($query,$data)
    {
        $this->query = $query;
        $this->data = $data;
    }
    public function query()
    {
        $query = $this->query;
        return $query;
    }
    public function map($item) : array {
        $data_array = [];
        $ref_code = $item->refrence_code;
        array_push($data_array, $ref_code);

        $prod_name = $item->short_desc;
        $item_name = $item->name != null ? $item->name : $prod_name;
        array_push($data_array, $item_name);
        array_push($data_array, $prod_name);

        $long_desc = $item->long_desc;
        array_push($data_array, $long_desc);

        //min and max qty
        array_push($data_array, $item->min_o_qty);
        array_push($data_array, $item->max_o_qty);

        //regular price, discout price and discount expiry
        array_push($data_array, @$item->ecommerce_price);
        array_push($data_array, @$item->discount_price);
        array_push($data_array, @$item->discount_expiry_date);
        array_push($data_array, @$item->ecomSellingUnits->title);
        array_push($data_array, @$item->selling_unit_conversion_rate);
        array_push($data_array, number_format($item->selling_unit_conversion_rate * $item->selling_price,3,'.',''));
        array_push($data_array, $item->ecommerce_enabled == 0 ? "disabled" : "enabled");

        // array_push($data_array, $item->discount_price);

        //sale price from and to
        // array_push($data_array, '');
        // array_push($data_array, $item->discount_expiry_date);

        array_push($data_array, @$item->ecom_warehouse_stock->current_quantity);

        //weight length width height
        array_push($data_array, '');
        array_push($data_array, @$item->length != null ? @$item->length.' cm' : '');
        array_push($data_array, @$item->width != null ? @$item->width.' cm' : '');
        array_push($data_array, @$item->height != null ? @$item->height.' cm' : '');
        array_push($data_array, @$item->ecom_product_weight_per_unit != null ? @$item->ecom_product_weight_per_unit.' kg' : '');

        array_push($data_array, @$item->productCategory->title);
        array_push($data_array, @$item->productSubCategory->title);

        array_push($data_array, @$item->brand);

        //Volume country region
        array_push($data_array, '');
        array_push($data_array, @$item->def_or_last_supplier->getcountry->name);
        array_push($data_array, @$item->def_or_last_supplier->getstate->name);

        array_push($data_array, @$item->sellingUnits->title);

        //year
        // array_push($data_array, '');
        array_push($data_array, $item->product_temprature_c);
        array_push($data_array, $item->vat);
        array_push($data_array, @$item->productType->title);
        array_push($data_array, @$item->productType2->title);
        array_push($data_array, @$item->productType3->title);
        return $data_array;
    }
    public function headings(): array
    {
        return [
            'SKU-PF#',
            'PRODUCT NAME',
            'Product Note - Short Description',
            'Ecomm-Description',
            'Min Order QTY',
            'Max Order QTY',
            'REGULAR PRICE',
            'DISCOUNT PRICE',
            'DISCOUNT Expiry',
            'E-Com Selling Unit',
            'E-Com Selling Unit Conversion Rate',
            'E-Com COGS Price',
            'E-Com Status',
            // 'Sales Price',
            // 'Sale Price FROM',
            // 'Sales Price To',
            'Quantity STOCK',
            'Weight',
            'LENGTH',
            'Width',
            'Height',
            'E-commerce Product weight per unit',
            'Main Category',
            'Sub Category',
            'Brand',
            'Vol.',
            'Country',
            'Region',
            'Selling Unit',
            // 'Year',
            'Temperature',
            'VAT',
            'Type 1',
            'Type 2',
            'Type 3'
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:V1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);

            },
        ];
    }

}
