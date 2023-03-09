<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class purchasingReportMainExport implements FromView, ShouldAutoSize, WithEvents
{
    protected $query = null;
    protected $row_color = null;

     /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($query)
    {
        $this->query = $query;
    }

    public function view(): View
    {
        
    	$query = $this->query;
    	// dd($query);
        return view('users.exports.purchasing-report-main-exp', ['query' => $query]);
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

            },
        ];
    }
}
