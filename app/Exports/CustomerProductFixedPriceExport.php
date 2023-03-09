<?php

namespace App\Exports;

use App\Models\Common\Product;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class CustomerProductFixedPriceExport implements  FromView, ShouldAutoSize, WithEvents
{
	protected $supplier_id;
	protected $primary_category;
	protected $sub_category;
	protected $customer_id;

	public function __construct($supplier_id, $primary_category, $sub_category, $customer_id)
	{
		$this->supplier_id      = $supplier_id;
		$this->primary_category = $primary_category;
		$this->sub_category     = $sub_category;
		$this->customer_id 		= $customer_id;
	}

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $products = Product::query();
        $products->where('status',1);
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
        //dd($products);

        return view('sales.exports.product-fix-price', ['products' => $products,'customer_id' => $this->customer_id]);
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
