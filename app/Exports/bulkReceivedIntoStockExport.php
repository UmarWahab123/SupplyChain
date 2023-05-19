<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;
use Maatwebsite\Excel\Events\AfterSheet;

class bulkReceivedIntoStockExport  implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithEvents
{
    protected $query = null;

    public function __construct($query)
    {
        $this->query = $query;
    }
    /**
    
    */
    // public function collection()
    // {
    //     //
    // }

    public function query()
    {
        $query = $this->query;
        return $query;
    } 

    public function map($item) : array
    {
        $data_array = [];
        array_push($data_array, @$item->PurchaseOrder->ref_id);
        array_push($data_array, @$item->product->refrence_code);
        array_push($data_array, @$item->product->short_desc);
        array_push($data_array, @$item->quantity);
        array_push($data_array, @$item->pod_unit_price); 
        array_push($data_array, @$item->pod_total_unit_price);
      

        
        return $data_array;
    }

    public function headings(): array
    {
        $headings_array = [];
        array_push($headings_array, 'PO #');
        array_push($headings_array, 'PF #');
        array_push($headings_array, 'Description');
        array_push($headings_array, 'QTY Inv');
        array_push($headings_array,'Unit Price');
        array_push($headings_array,'Total Amount');
        
        return $headings_array;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:S1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);

            },
        ];
    }


}
