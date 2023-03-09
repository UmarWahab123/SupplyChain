<?php

namespace App\Exports;

use App\Models\Common\Product;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\Common\CustomerCategory;
use App\QuotationConfig;

class BulkProducts implements FromView, ShouldAutoSize, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $customerCategory = CustomerCategory::where('is_deleted',0)->get();
        return view('users.exports.bulkUploadProducts', ['customerCategory' => $customerCategory]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $event->sheet->getStyle('A1:'.$event->sheet->getHighestColumn().'2')->getFont()->setBold(true);

                $event->sheet->getRowDimension(1)->setVisible(false);

                $globalAccessConfig2 = QuotationConfig::where('section','products_management_page')->first();
                if($globalAccessConfig2)
                {
                    $globalaccessForConfig = unserialize($globalAccessConfig2->print_prefrences);
                    foreach ($globalaccessForConfig as $val)
                    {
                        if($val['slug'] === "allow_custom_code_edit")
                        {
                            $allow_custom_code_edit = $val['status'];
                        }
                    }
                }
                else
                {
                    $allow_custom_code_edit = '';
                }

                if(@$allow_custom_code_edit == 0)
                {
                    $event->sheet->getColumnDimension('A')->setVisible(false);
                }
            },
        ];
    }
}
