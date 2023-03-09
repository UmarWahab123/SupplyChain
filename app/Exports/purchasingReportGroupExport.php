<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class purchasingReportGroupExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithEvents
{
    protected $query = null;
    protected $global_terminologies = null;
    protected $row_color = null;

     /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($query, $global_terminologies)
    {
        $this->query = $query;
        $this->global_terminologies = $global_terminologies;
    }

    public function query()
    {
        $query = $this->query;
        return $query;
    }

    public function map($item) : array 
    {
        if($item->product_id != null)
        {
            $pf_no = $item->product->refrence_code;
        }
        else
        {
            $pf_no = 'N.A';
        }

        if($item->product_id !== null)
        {
            $desc = $item->product->short_desc;
        }
        else
        {
            $desc = 'N.A';
        }

        if($item->product_id !== null)
        {
            $billing_unit = $item->product->units->title;
        }
        else
        {
            $billing_unit = 'N.A';
        }

        if($item->product_id !== null)
        {
            $unit_mes_code = $item->product->sellingUnits->title;
        }
        else
        {
            $unit_mes_code = 'N.A';
        }

        if($item->TotalQuantity !== null)
        {
            $sum_qty = $item->TotalQuantity;
        }
        else
        {
            $sum_qty = 'N.A';
        }

        if($item->pod_unit_price !== null)
        {
            $cost_unit = number_format($item->pod_unit_price,3,'.','');
        }
        else
        {
            $cost_unit = 'N.A';
        }
        
        if($item->GrandTotalUnitPrice !== null)
        {
            $pod_total_unit = number_format($item->GrandTotalUnitPrice,3,'.','');
        }
        else
        {
            $pod_total_unit = 'N.A';
        }

        return [
            $pf_no,
            $desc,
            $billing_unit,
            $unit_mes_code,
            $sum_qty,
            $cost_unit,
            $pod_total_unit
        ];
    }

    public function headings(): array
    {
        $global_terminologies = $this->global_terminologies;
        return [
            $global_terminologies['our_reference_number'],
            $global_terminologies['product_description'], 
            'Billing Unit',
            $global_terminologies['selling_unit'],
            'Sum of' . $global_terminologies['qty'], 
            $global_terminologies['product_cost'],
            $global_terminologies['sum_pro_cost']
        ];
    }

    // public function view(): View
    // {
    //     $query = $this->query;
    //     $global_terminologies = $this->global_terminologies;
    //     return view('users.exports.purchasing-report-group-exp', ['query' => $query, 'global_terminologies' => $global_terminologies]);
    // }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:G1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);
            },
        ];
    }
}
