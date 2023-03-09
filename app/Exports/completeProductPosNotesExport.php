<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Common\Product;

class completeProductPosNotesExport implements  ShouldAutoSize, WithEvents, FromQuery, WithHeadings, WithMapping
{
     /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($query)
    {
        $this->query = $query;

    }

    public function query()
    {
        $query = $this->query;
        return $query;
    }

    public function headings() : array
    {
        $heading_array = [];

        array_push($heading_array, 'product_code');
        array_push($heading_array, 'product_name');
        array_push($heading_array, 'expiration_date');
        array_push($heading_array, 'quantity');
        array_push($heading_array, 'notes');


        return $heading_array;
    }


    public function map($item) : array
    {
        $data_array = [];


        if($item->stock_out_available->count() > 0) {
            $sum = $item->stock_out_available->sum('available_stock');

            array_push($data_array, $item->product['refrence_code']);
            array_push($data_array, $item->product['short_desc']);
            array_push($data_array, $item->expiration_date);
            array_push($data_array, round($sum, 2));
            array_push($data_array, $item->title);
        }

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
