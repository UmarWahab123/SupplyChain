<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class draftPOExport implements FromView, ShouldAutoSize, WithEvents
{
    protected $query = null;
    protected $not_visible_arr = null;
    public function __construct($query,$not_visible_arr)
    {
        $this->query = $query;
        $this->not_visible_arr = $not_visible_arr;
    }
    
    public function view(): View
    {
        
        $query = $this->query;
        $not_visible_arr = $this->not_visible_arr;
        return view('users.exports.draft-po-exp', ['query' => $query,'not_visible_arr' => $not_visible_arr]);
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
                $event->sheet->getColumnDimension('O')->setVisible(false);
                $event->sheet->getColumnDimension('A')->setVisible(false);
            },
        ];
    }
}
