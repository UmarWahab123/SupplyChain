<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ProductReceivingRecord implements  FromView, ShouldAutoSize, WithEvents
{

    protected $query = null;
    protected $not_in_arr;
    public function __construct($query, $not_in_arr)
    {
        $this->query = $query;
        $this->not_in_arr = $not_in_arr;
    }

    public function view(): View
    {
        
    	$query = $this->query;
        $not_in_arr = $this->not_in_arr;
        return view('warehouse.po-groups.exports.product-receiving-record', ['query' => $query,'not_in_arr' => $not_in_arr]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle("A1:".$event->sheet->getHighestDataColumn()."1")->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);
            // $query = $this->query;
            // $sheet_rows = 2;
            // foreach ($query as $q) 
            // {
            //     if($q->occurrence > 1)
            //     {
            //         $count = $sheet_rows+$q->occurrence+1;
            //         for ($i=$sheet_rows+1; $i < $count ; $i++) 
            //         {
            //             $event->sheet->getStyle('A'.$i.':W'.$i.'')->applyFromArray([            
            //                 'fill' => [
            //                 'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            //                 'color' => ['argb' => 'CCCCCC']
            //                 ]
            //             ]);
            //         }
            //         $sheet_rows += $q->occurrence;
            //     }
            //     $sheet_rows++;
            // }

            },
        ];
    }
}
