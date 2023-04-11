<?php

namespace App\Exports;

use App\Models\Common\Configuration;
use App\Models\Common\CustomerCategory;
use App\Models\Common\Product;
use App\Models\Common\SupplierProducts;
use App\Models\Common\WarehouseProduct;
use App\QuotationConfig;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class SupplierAllProductsExport implements  FromView, ShouldAutoSize, WithEvents
{
    protected $supplier_id;
    protected $primary_category;
    protected $sub_category;
    protected $type;
    protected $type_2;
    protected $type_3;
    protected $global_terminologies = null;

    public function __construct($supplier_id, $primary_category, $sub_category, $type, $type_2, $type_3, $global_terminologies)
    {
        $this->supplier_id      = $supplier_id;
        $this->primary_category = $primary_category;
        $this->sub_category = $sub_category;
        $this->type = $type;
        $this->type_2 = $type_2;
        $this->type_3 = $type_3;
        $this->global_terminologies = $global_terminologies;
    }


    public function view(): View
    {
        $products = Product::query();
        $products->where('status',1);
        $global_terminologies = $this->global_terminologies;
        $sup_id = $this->supplier_id;
        $sub_category = $this->sub_category;
        $type = $this->type;
        $type_2 = $this->type_2;
        $type_3 = $this->type_3;

        if($this->supplier_id != null)
        {
            $getAllProducts = SupplierProducts::where('supplier_id',$this->supplier_id)->where('is_deleted',0)->pluck('product_id')->toArray();
            $products->whereIn('id',$getAllProducts);
        }
        else {
            $getAllProducts = SupplierProducts::where('is_deleted',0)->pluck('product_id')->toArray();
            $products->whereIn('id',$getAllProducts);
        }

        if($this->primary_category != null)
        {
            if (is_array($this->primary_category)) {
                $primary_ids = [];
                $sub_ids = [];
                foreach ($this->primary_category as $category) {
                    $id_split = explode('_', $category);
                    if ($id_split[0] == 'pri') {
                        array_push($primary_ids, $id_split[1]);
                    }
                    if($id_split[0] == 'sub'){
                        array_push($sub_ids, $id_split[1]);
                    }

                }

                if($primary_ids != null || $sub_ids != null)
                {
                    $products->where(function($q) use ($primary_ids, $sub_ids){
                        if($primary_ids != null)
                        {
                            $q->whereIn('primary_category',$primary_ids);
                        }
                        if($sub_ids != null)
                        {
                            $q->orWhereIn('category_id',$sub_ids);
                        }
                    });
                }
            }
            else {
                // For Multi Supllier Product Bulk Upload
                $products->where('primary_category',$this->primary_category);
            }
        }

        // For Multi Supllier Product Bulk Upload
        if($this->sub_category != null)
        {
            $products->where('category_id',$this->sub_category);
        }
        if ($type != null) {
            $products->where('type_id', $type);
        }
        if ($type_2 != null) {
            $products->where('type_id_2', $type_2);
        }
        if ($type_3 != null) {
            $products->where('type_id_3', $type_3);
        }

        $products = $products->with('supplier_products.supplier', 'units', 'sellingUnits', 'stockUnit', 'productCategory', 'productSubCategory', 'productType', 'productType2', 'productType3', 'product_fixed_price')->get();
        $customerCategory = CustomerCategory::where('is_deleted',0)->get();
        $configuration = Configuration::first();

        $warehouse = null;

        return view('users.exports.supplier-products',compact('products','warehouse','customerCategory','global_terminologies','sup_id', 'configuration'));
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
