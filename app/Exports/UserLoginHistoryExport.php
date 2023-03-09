<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class UserLoginHistoryExport implements FromView, ShouldAutoSize, WithEvents
{
    protected $query = null;
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
        return view('users.exports.users-login-history-list-exp', ['query' => $query]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:D1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);
            },
        ];
    }
}
