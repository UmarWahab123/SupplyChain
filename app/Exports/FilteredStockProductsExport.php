<?php

namespace App\Exports;

use App\Models\Common\Product;
use App\Models\Common\Warehouse;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class FilteredStockProductsExport implements FromView, ShouldAutoSize, WithEvents
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
        $data=$this->data;
        $global_terminologies=$this->global_terminologies;
        $products = Product::query();
        $products->where('status',1);
        if(array_key_exists('suppliers', $data) && $data['suppliers'] != null){
        	$products->where('supplier_id',$data['suppliers']);
        }
        if(array_key_exists('primary_category', $data) && $data['primary_category'] != null){
        	$products->where('primary_category',$data['primary_category']);
        }
        if(array_key_exists('sub_category', $data) && $data['sub_category'] != null){
            $products->where('category_id',$data['sub_category']);
        }
        if(array_key_exists('types', $data) && $data['types'] != null){
            $products->where('type_id',$data['types']);
        }
        if(array_key_exists('types_2', $data) && $data['types_2'] != null){
            $products->where('type_id_2',$data['types_2']);
        }
        $products = $products->join('warehouse_products','warehouse_products.product_id','=','products.id')->where('warehouse_products.warehouse_id',$data['warehouses']);
        $products = $products->select('products.id','products.refrence_code','products.supplier_id','products.primary_category','products.category_id','products.type_id','products.short_desc','products.selling_unit','products.min_stock','warehouse_products.current_quantity','products.brand', 'warehouse_products.reserved_quantity', 'warehouse_products.ecommerce_reserved_quantity','products.type_id_2')->with('def_or_last_supplier','productCategory','productSubCategory','productType','supplier_products','sellingUnits','stock_in.stock_out','productType2')->get();
        $warehouse = Warehouse::where('id',$data['warehouses'])->first();
        return view('users.exports.stock-products',compact('products','warehouse','global_terminologies'));
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
