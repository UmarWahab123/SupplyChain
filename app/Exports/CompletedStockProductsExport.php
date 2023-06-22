<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\Common\Product;
use App\Models\Common\Warehouse;

class CompletedStockProductsExport implements FromView, ShouldAutoSize, WithEvents
{
    protected $data = null;
    protected $global_terminologies = null;


	public function __construct($data,$global_terminologies)
    {
        $this->data = $data;
        $this->global_terminologies = $global_terminologies;

    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $data = $this->data;
        $warehouse_id = @$data[0]['warehouse_id'];
        $products = Product::query();
        $products->whereIn('refrence_code',$data->pluck('product_id')->toArray());
        $products = $products->join('warehouse_products','warehouse_products.product_id','=','products.id')
                ->where('warehouse_products.warehouse_id',$warehouse_id);
        $products = $products->select('products.id','products.refrence_code','products.supplier_id','products.primary_category',
        'products.category_id','products.type_id','products.short_desc','products.selling_unit','products.min_stock',
        'warehouse_products.current_quantity','products.brand', 'warehouse_products.reserved_quantity', 
        'warehouse_products.ecommerce_reserved_quantity','products.type_id_2')->with('def_or_last_supplier','productCategory',
        'productSubCategory','productType','supplier_products','sellingUnits','stock_in.stock_out','productType2')->get();
        $global_terminologies=$this->global_terminologies;
        // $products = Product::query();
        $warehouse = Warehouse::where('id',$warehouse_id)->first();
        $custom_data = $this->data;
        // dd($custom_data[0]);
        return view('users.exports.completed-stock-products',compact('products','global_terminologies', 'warehouse', 'custom_data'));
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:U1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);
                $event->sheet->getColumnDimension('A')->setVisible(false);
            },
        ];
    }
}
