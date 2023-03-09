<?php

namespace App\Exports;

use App\Models\Common\Product;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class FilteredProductsExport implements FromView, ShouldAutoSize, WithEvents
{
	protected $supplier_id = null;
	protected $primary_category = null;
    protected $sub_category = null;
    protected $global_terminologies = null;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($supplier_id,$primary_category,$sub_category,$global_terminologies)
    {
        $this->supplier_id = $supplier_id;
        $this->primary_category = $primary_category;
        $this->sub_category = $sub_category;
        $this->global_terminologies = $global_terminologies;
    }
 
    public function view(): View
    {
        $products = Product::query();
        $global_terminologies = $this->global_terminologies;
        // $products->where('status',1);
        if($this->supplier_id != null){
        	$products->where('supplier_id',$this->supplier_id);
        }
        if($this->primary_category != null){
        	$products->where('primary_category',$this->primary_category);
        }
        if($this->sub_category != null){
            $products->where('category_id',$this->sub_category);
        }
        $products = $products->get();
        // dd($products[0]->def_or_last_supplier);

        return view('users.exports.allproducts', ['products' => $products,'global_terminologies' => $global_terminologies]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);
            },
        ];
    }
}
