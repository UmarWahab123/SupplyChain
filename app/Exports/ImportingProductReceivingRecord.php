<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Variable;


class ImportingProductReceivingRecord implements  FromView, ShouldAutoSize, WithEvents
{
	protected $query = null;
    protected $not_visible_arr = null;
    protected $col_display_pref = null;

    public function __construct($query,$not_visible_arr,$col_display_pref)
    {
        $this->query = $query;
        $this->not_visible_arr = $not_visible_arr;
        $this->col_display_pref = $col_display_pref;
    }

    public function view(): View
    {
        
    	$query = $this->query;
        $not_visible_arr = $this->not_visible_arr;
        $col_display_pref = $this->col_display_pref;

        $vairables=Variable::select('slug','standard_name','terminology')->get();
        $global_terminologies=[];
        foreach($vairables as $variable)
        {
            if($variable->terminology != null)
            {
                $global_terminologies[$variable->slug]=$variable->terminology;
            }
            else
            {
                $global_terminologies[$variable->slug]=$variable->standard_name;
            }
        }
        return view('importing.po-groups.exports.product-receiving-record', ['query' => $query,'global_terminologies'=>$global_terminologies, 'not_visible_arr' => $not_visible_arr, 'col_display_pref' => $col_display_pref]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle("A1:".$event->sheet->getHighestDataColumn()."2")->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);

                // if($this->not_visible_arr[0] == "")
                // {
                //     $not_visible = 0;
                // }
                // else
                // {
                //     $not_visible = sizeof($this->not_visible_arr);
                // }

                // $total_rows = 29;
                // $subtracted = ($total_rows - $not_visible);

                // $event->sheet->getRowDimension('27')->setVisible(false);
                $hc = $event->sheet->getHighestColumn();
                // $first_row = $event->sheet->getRowDimension(1);
                // $event->sheet->getHighestColumn()->setVisible(false);
                $event->sheet->getColumnDimension($hc)->setVisible(false);
                $event->sheet->getRowDimension(1)->setVisible(false);
            },
        ];
    }
}
