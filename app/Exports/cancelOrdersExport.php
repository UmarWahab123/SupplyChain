<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;

class cancelOrdersExport implements FromView, ShouldAutoSize, WithEvents
{
    protected $query = null;
    protected $role_id = null;
    protected $global_terminologies = null;

    public function __construct($query,$global_terminologies,$role_id)
    {
        $this->query = $query;
        $this->role_id = $role_id;
        $this->global_terminologies = $global_terminologies;
    }

    public function view(): View
    {
        
        $query = $this->query;
        $role_id = $this->role_id;
        $global_terminologies = $this->global_terminologies;
        return view('users.exports.cancel_orders_exp', ['query' => $query,'global_terminologies' => $global_terminologies,'role_id' => $role_id]);
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
