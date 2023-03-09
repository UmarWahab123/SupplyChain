<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class waitingConformationTDExport implements FromView, ShouldAutoSize, WithEvents
{
    protected $query = null;
    protected $is_bonded = null;
    protected $show_supplier_invoice_number = null;
    protected $allow_custom_invoice_number = null;
    protected $show_custom_line_number = null;

    public function __construct($query, $is_bonded, $show_supplier_invoice_number, $show_custom_line_number, $allow_custom_invoice_number)
    {
        $this->query = $query;
        $this->is_bonded = $is_bonded;
        $this->show_supplier_invoice_number = $show_supplier_invoice_number;
        $this->allow_custom_invoice_number = $allow_custom_invoice_number;
        $this->show_custom_line_number = $show_custom_line_number;
    }
    public function view(): View
    {
        
        $query = $this->query;
        $is_bonded = $this->is_bonded;
        $show_supplier_invoice_number = $this->show_supplier_invoice_number;
        $allow_custom_invoice_number = $this->allow_custom_invoice_number;
        $show_custom_line_number = $this->show_custom_line_number;
    	// dd($query);
        return view('users.exports.waiting-conf-td-exp', ['query' => $query, 'is_bonded' => $is_bonded, 'show_supplier_invoice_number' => $show_supplier_invoice_number, 'allow_custom_invoice_number' => $allow_custom_invoice_number, 'show_custom_line_number' => $show_custom_line_number]);
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
