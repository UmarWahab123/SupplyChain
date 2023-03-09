<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class purchaseListExport implements FromView, ShouldAutoSize, WithEvents
{
    protected $query = null;
    protected $tsd = null;
    protected $row_color = null; 
    protected $not_visible_arr = null;
    protected $getWarehouses = null;
    protected $global_terminologies = null;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($query,$not_visible_arr,$global_terminologies,$tsd,$getWarehouses)
    {
        $this->query = $query;
        $this->tsd = $tsd;
        $this->not_visible_arr = $not_visible_arr;
        $this->global_terminologies = $global_terminologies;
        $this->getWarehouses = $getWarehouses;   
    }

    public function view(): View
    {
        
        $query = $this->query;
        $tsd = $this->tsd;
        $not_visible_arr = $this->not_visible_arr;
        $global_terminologies = $this->global_terminologies;
        $getWarehouses = $this->getWarehouses;
    	// dd($query);
        return view('users.exports.purchase-list-exp', ['query' => $query, 'not_visible_arr' => $not_visible_arr,'global_terminologies' => $global_terminologies,'tsd' => $tsd,'getWarehouses' => $getWarehouses]);
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
