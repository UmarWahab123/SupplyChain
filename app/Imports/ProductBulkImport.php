<?php

namespace App\Imports;
use App\Jobs\SupplierBulkProductsImportJob;
use App\Models\Common\Brand;
use App\Models\Common\Country;
use App\Models\Common\Currency;
use App\Models\Common\CustomerCategory;
use App\Models\Common\Product;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductType;
use App\Models\Common\Supplier;
use App\Models\Common\TempProduct;
use App\Models\Common\Unit;
use Auth;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class ProductBulkImport implements ToCollection, WithStartRow, WithHeadingRow, WithCalculatedFormulas
{
    private $supplier_id;
    public  $response;
    public  $result;
    public  $error_msgs;
    public $dispatch = false;

    protected $row_count = 0;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function __construct($supplier_id,$user_id)
    {

        $this->supplier_id = $supplier_id;
        $this->user_id     = $user_id;
    }

    public function collection(Collection $rows)
    {
        $supplier_id = $this->supplier_id;
        $user_id     = $this->user_id;

        $getAllRecord = TempProduct::where('supplier_id',$supplier_id)->delete();
        if($this->dispatch == false)
        {
            $result = SupplierBulkProductsImportJob::dispatch($rows,$supplier_id,$user_id);
            $this->dispatch = true;
        }

        // $html_string = '';
        // $error = 0;
        // if ($rows->count() > 1)
        // {
        //     $row1 = $rows->toArray();
        //     $remove = array_shift($row1);
        //     $increment = 2;
        //     foreach ($row1 as $row)
        //     {
        //         if ($row[2] == null)
        //         {
        //             $error = 1;
        //             $html_string .= '<li>Supplier Reference name is empty on row <b>'.$increment.'</b> </li>';
        //             continue;
        //         }
        //         else
        //         {
        //             $supplier = Supplier::find($this->supplier_id);
        //             if ( strtolower($supplier->reference_name) != strtolower($row[2]) )
        //             {
        //                 $error = 1;
        //                 $html_string .= '<li>Supplier Reference name is not matched with row <b>'.$increment.'</b> </li>';
        //                 continue;
        //             }
        //             else
        //             {
        //                 $has_error = 1;
        //                 $status = 1;
        //                 $supplier_id = null;

        //                 #row[0]  => Our Reference #
        //                 #row[1]  => System Code
        //                 #row[2]  => Supplier
        //                 $supplier = Supplier::where('reference_name', '=', $row[2])->first();
        //                 if ($supplier != null)
        //                 {
        //                     $supplier_id = $supplier->id;
        //                 }
        //                 else
        //                 {
        //                     $supplier_id = $row[2];
        //                     $status = 0;
        //                 }

        //                 #row[3]  => Supplier Description
        //                 #row[4]  => Supplier Packing Unit
        //                 #row[5]  => Supplier Billing Unit to Packing Unit
        //                 if (!is_numeric($row[5]))
        //                 {
        //                     $row[5] = null;
        //                 }
        //                 #row[6]  => Supplier MOQ (Minimum number of Buying Unit They Will Sell Us)
        //                 if (!is_numeric($row[6]))
        //                 {
        //                     $row[6] = null;
        //                 }

        //                 #row[7]  => Supplier Billed Unit
        //                 $buying_unit = Unit::where('title', $row[7])->first();
        //                 if ($buying_unit)
        //                 {
        //                     $buying_unit_id = $buying_unit->id;
        //                 }
        //                 else
        //                 {
        //                     $buying_unit_id = $row[7];
        //                     $has_error = 1;
        //                     $status = 0;
        //                 }

        //                 #row[8]  => Buying Price
        //                 $buying_price = $row[8];

        //                 if (!is_numeric($buying_price))
        //                 {
        //                     $buying_price = null;
        //                 }
        //                 #row[9]  => Gross Weight
        //                 if (!is_numeric($row[9]))
        //                 {
        //                     $row[10] = null;
        //                 }

        //                 #row[10]  => Freight Per Billed Unit
        //                 if (!is_numeric($row[10]) || $row[8] == null)
        //                 {
        //                     $row[10] = null;
        //                 }
        //                 #row[11]  => Landing Per Billed Unit
        //                 if (!is_numeric($row[11]) || $row[8] == null)
        //                 {
        //                     $row[11] = null;
        //                 }

        //                 #row[12]  => Import Tax Actual (updated)
        //                 $import_tax_actual = $row[12];

        //                 if (!is_numeric($import_tax_actual) || $row[8] == null)
        //                 {
        //                     $import_tax_actual = null;
        //                 }

        //                 #row[13]  => Extra Cost Per Billed Unit
        //                 if (!is_numeric($row[13]) || $row[8] == null)
        //                 {
        //                     $row[13] = null;
        //                 }

        //                 #row[14]  => Extra Tax THB
        //                 if (!is_numeric($row[14]) || $row[8] == null)
        //                 {
        //                     $row[14] = null;
        //                 }

        //                 #row[15]  => Selling Unit
        //                 $selling_unit = Unit::where('title', $row[15])->first();
        //                 if ($selling_unit != null)
        //                 {
        //                     $selling_unit_id = $selling_unit->id;
        //                 }
        //                 else
        //                 {
        //                     $selling_unit_id = $row[15];
        //                     $has_error = 1;
        //                     $status = 0;
        //                 }

        //                 #row[16]  => Unit Conversion Rate
        //                 if ($row[16] != null)
        //                 {
        //                     $unit_conversion_rate = $row[16];
        //                 }
        //                 else
        //                 {
        //                     if ($buying_unit_id == $selling_unit_id)
        //                     {
        //                         $unit_conversion_rate = 1;
        //                     }
        //                     else
        //                     {
        //                         $unit_conversion_rate = null;
        //                         $status = 0;
        //                     }
        //                 }
        //                 if (!is_numeric($unit_conversion_rate))
        //                 {
        //                     $unit_conversion_rate = null;
        //                 }

        //                 #row[17] => Expected Lead Time (days)
        //                 if (!is_numeric($row[17]))
        //                 {
        //                     $row[17] = null;
        //                 }

        //                 #row[18] => Suppliers Product Reference No.
        //                 #row[19] => Brand
        //                 #row[20] => Product Description
        //                 if ($row[20] == null)
        //                 {
        //                     $status = 0;
        //                 }
        //                 #row[21] => Avg Weight per piece or box
        //                 #row[22]  => Stock Unit
        //                 $stock_unit = Unit::where('title', $row[22])->first();
        //                 if ($stock_unit != null)
        //                 {
        //                     $stock_unit_id = $stock_unit->id;
        //                 }
        //                 else
        //                 {
        //                     $stock_unit_id = null;
        //                     $has_error = 1;
        //                 }

        //                 #row[23] => MINIMUM STOCK
        //                 #row[24] => Primary Category
        //                 $primary_category = ProductCategory::where('title', $row[24])->where('parent_id', 0)->first();
        //                 if ($primary_category != null)
        //                 {
        //                     $primary_category_id = $primary_category->id;
        //                 }
        //                 else
        //                 {
        //                     $primary_category_id = $row[24];
        //                     $has_error = 1;
        //                     $status = 0;
        //                 }

        //                 #row[25]  => Category id #
        //                 $category = ProductCategory::where('title', $row[25])->where('parent_id', $primary_category_id)->first();
        //                 if ($category != null)
        //                 {
        //                     $category_id = $category->id;
        //                 }
        //                 else
        //                 {
        //                     $category_id = $row[25];
        //                     $has_error = 1;
        //                     $status = 0;
        //                 }

        //                 #row[26]  => Good Type
        //                 $type = ProductType::where('title', $row[26])->first();
        //                 if ($type != null)
        //                 {
        //                     $type_id = $type->id;
        //                 }
        //                 else
        //                 {
        //                     $type_id = $row[26];
        //                     $has_error = 1;
        //                     $status = 0;
        //                 }

        //                 #row[27]  => Good Temprature C
        //                 #row[28]  => Size/Note

        //                 // creating an array of a fixed prices
        //                 $fixed_prices_array = array();
        //                 $custCats = CustomerCategory::where('is_deleted',0)->orderBy('id', 'ASC')->get();
        //                 $i = 29;
        //                 foreach ($custCats as $cat) {
        //                     array_push($fixed_prices_array, $row[$i]);
        //                     $i++;
        //                 }

        //                 $new_data = new TempProduct([
        //                     'refrence_code'          => $row[0],
        //                     'system_code'            => $row[1],
        //                     'supplier_description'   => $row[3],
        //                     'supplier_packaging'     => $row[4],
        //                     'billed_unit'            => $row[5],
        //                     'm_o_q'                  => $row[6],
        //                     'buying_price'           => $buying_price,
        //                     'gross_weight'           => $row[9],
        //                     'freight'                => $row[10],
        //                     'landing'                => $row[11],
        //                     'import_tax_actual'      => $row[12],
        //                     'extra_cost'             => $row[13],
        //                     'extra_tax'              => $row[14],
        //                     'leading_time'           => $row[17],
        //                     'p_s_r'                  => $row[18],
        //                     'brand'                  => $row[19],
        //                     'short_desc'             => $row[20],
        //                     'weight'                 => $row[21],
        //                     'min_stock'              => $row[23],
        //                     'primary_category'       => $primary_category_id,
        //                     'category_id'            => $category_id ,
        //                     'type_id'                => $type_id,
        //                     'product_temprature_c'   => $row[27],
        //                     'product_notes'          => $row[28],
        //                     'buying_unit'            => $buying_unit_id,
        //                     'selling_unit'           => $selling_unit_id,
        //                     'stock_unit'             => $stock_unit_id,
        //                     'unit_conversion_rate'   => $unit_conversion_rate,
        //                     'supplier_id'            => $supplier_id,
        //                     'hasError'               => $has_error,
        //                     'status'                 => $status,
        //                     'created_by'             => Auth::user()->id,
        //                     'fixed_prices_array'     => serialize($fixed_prices_array),
        //                 ]);

        //                 $new_data->save();
        //                 $this->row_count++;
        //             }
        //         }
        //     }
        // }
        // else
        // {
        //     $success = 'fail';
        //     $error = 2;
        //     $this->error_msgs = $html_string;
        //     $this->filterFunction($success);
        // }

        // if($error == 1)
        // {
        //     $success = 'hasError';
        //     $this->error_msgs = $html_string;
        //     $this->filterFunction($success);
        // }
        // elseif($error == 2)
        // {
        //     $success = 'fail';
        //     $this->error_msgs = $html_string;
        //     $this->filterFunction($success);
        // }
        // elseif($error == 3)
        // {
        //     $success = 'invalid';
        //     $this->error_msgs = $html_string;
        //     $this->filterFunction($success);
        // }
        // else
        // {
        //     if($error == 0)
        //     {
        //         $html_string = '';
        //     }
        //     $success = 'pass';
        //     $this->error_msgs = $html_string;
        //     $this->filterFunction($success);
        // }
    }



    // protected function generateRandomString($length)
    // {
    //     $characters = '0123456789';
    //     $charactersLength = strlen($characters);
    //     $randomString = '';
    //     for ($i = 0; $i < $length; $i++) {
    //         $randomString .= $characters[rand(0, $charactersLength - 1)];
    //     }
    //     return $randomString;
    // }

    // public function doProductCompleted($id)
    // {
    //     if($id)
    //     {
    //         $product = TempProduct::find($id);
    //         // dd($product);
    //         $missingPrams = array();

    //         // if($product->refrence_code == null)
    //         // {
    //         //     $missingPrams[] = 'Product Reference Code';
    //         // }

    //         if($product->short_desc == null)
    //         {
    //             $missingPrams[] = 'Short Description';
    //         }

    //         // if($product->weight == null)
    //         // {
    //         //     $missingPrams[] = 'Weight/Avg. Unit Price';
    //         // }

    //         if($product->primary_category == null)
    //         {
    //             $missingPrams[] = 'Primary Category';
    //         }

    //         if($product->category_id == 0)
    //         {
    //             $missingPrams[] = 'Sub Category';
    //         }

    //         if($product->type_id == null)
    //         {
    //             $missingPrams[] = 'Product Type';
    //         }

    //         // if($product->product_temprature_c == null)
    //         // {
    //         //     $missingPrams[] = 'Product Tempratue';
    //         // }

    //         if($product->buying_unit == null)
    //         {
    //             $missingPrams[] = 'Billed Unit';
    //         }

    //         if($product->selling_unit == null)
    //         {
    //             $missingPrams[] = 'Selling Unit';
    //         }

    //         if($product->unit_conversion_rate == null)
    //         {
    //             $missingPrams[] = 'Unit Conversion Rate';
    //         }

    //         // if($product->import_tax_book == null)
    //         // {
    //         //     $missingPrams[] = 'Import Tax Book';
    //         // }

    //         // if($product->vat == null)
    //         // {
    //         //     $missingPrams[] = 'Vat';
    //         // }

    //         if($product->supplier_id == null)
    //         {
    //             $missingPrams[] = 'Supplier Id';
    //         }

    //         if(sizeof($missingPrams) == 0)
    //         {
    //             $product->status = 1;
    //             $product->save();
    //             $message = "completed";

    //             return response()->json(['success' => true, 'message' => $message]);
    //         }
    //         else
    //         {
    //             $message = implode(', ', $missingPrams);
    //             return response()->json(['success' => false, 'message' => $message]);
    //         }
    //     }
    // }

    public function startRow():int
    {
        return 1;
    }

    // public function filterFunction($success = null)
    // {
    //     if($success == 'fail')
    //     {
    //         $this->response = "File is Empty Please Upload Valid File !!!";
    //         $this->result   = "true";
    //     }
    //     elseif($success == 'pass')
    //     {
    //         $this->response = "Products Updated Successfully !!!";
    //         $this->result   = "false";
    //     }
    //     elseif($success == 'hasError')
    //     {
    //         $this->response = "Products Updated Successfully, But Some Of Them Has Issues !!!";
    //         $this->result   = "withissues";
    //     }
    //     elseif($success == 'invalid')
    //     {
    //         $this->response = "File you are uploading is not a valid file, please upload a valid file !!!";
    //         $this->result   = "true";
    //     }
    // }
}
