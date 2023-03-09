<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class AccTransactionExport implements FromView, ShouldAutoSize, WithEvents
{

    protected $query = null;
    protected $global_terminologies = null;
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct($query,$global_terminologies)
    {
        $this->query = $query;
        $this->global_terminologies = $global_terminologies;
    }

    public function view(): View
    {
        
    	$query = $this->query;
        $global_terminologies = $this->global_terminologies;
    	// dd($query);
        return view('users.exports.acc-transaction-exp', ['query' => $query,'global_terminologies'=>$global_terminologies]);
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
