<?php

namespace App\Imports;

use App\Jobs\SupplierBulkPricesImportJob;
use App\Models\Common\Product;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierProducts;
use App\ProductHistory;
use Auth;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\ToModel;

class ProductPricesBulkImport implements ToCollection ,WithStartRow, WithHeadingRow
{
    private $supplier_id;
    public  $response;
    public  $result;
    public  $error_msgs;

    public function __construct($supplier_id,$user_id)
    {
        $this->supplier_id = $supplier_id;
        $this->user_id     = $user_id;
    }
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        $supplier_id = $this->supplier_id;
        $user_id     = $this->user_id;
        $result = SupplierBulkPricesImportJob::dispatch($rows,$supplier_id,$user_id);

        // $html_string = '';
        // $error = 0;
        // if ($rows->count() > 1) 
        // {
        //     $row1 = $rows->toArray();
        //     $remove = array_shift($row1);
        //     $increment = 2;
        //     foreach ($row1 as $row) 
        //     {
        //         // if(array_key_exists(8, $row))
        //         // {
        //         //     $error = 3;
        //         //     break;
        //         // }
                
        //         if($row[0] == null)
        //         {
        //             $error = 1;
        //             $html_string .= '<li>PF# is empty on row <b>'.$increment.'</b> </li>';
        //             continue;
        //         }
        //         else
        //         {
        //             $product  = Product::where('refrence_code', $row[0])->first();
        //             if($product)
        //             {
        //                 $product  = Product::where('refrence_code', $row[0])->first();
        //                 // $supplier = Supplier::where('company', $row[5])->first();
        //                 $supplier_product = SupplierProducts::where('product_id', $product->id)->where('supplier_id', $this->supplier_id)->first();
        //                 // this is the price of after conversion for THB
        //                 if($supplier_product != null) 
        //                 {
        //                     $old_price_value = $supplier_product->buying_price;
        //                     $supplier_conv_rate_thb = $supplier_product->supplier->getCurrency->conversion_rate;
        //                     $importTax = $supplier_product->import_tax_actual != null  ? $supplier_product->import_tax_actual : @$product->import_tax_book;

        //                     if (is_numeric($row[6])) 
        //                     {
        //                         $supplier_product->buying_price = $row[6];
        //                         $supplier_product->buying_price_in_thb = ($row[6] / $supplier_conv_rate_thb);
        //                         $price_calculation = $supplier_product->defaultSupplierProductPriceCalculation($product->id, $product->supplier_id, $row[6], $supplier_product->freight, $supplier_product->landing, $supplier_product->extra_cost, $importTax, $supplier_product->extra_tax);
        //                     }
        //                     else
        //                     {
        //                         $error = 1;
        //                         $html_string .= '<li>Invalid Price on row <b>'.$increment.'</b> </li>';
        //                         $increment++;
        //                         continue;
        //                     }

        //                     if (is_numeric($row[7])) 
        //                     {
        //                         $supplier_product->leading_time = $row[7];
        //                     }
        //                     $supplier_product->save();

        //                     if (is_numeric($row[6])) 
        //                     {
        //                         $product_history              = new ProductHistory;
        //                         $product_history->user_id     = Auth::user()->id;
        //                         $product_history->product_id  = $product->id;
        //                         $product_history->column_name = "Purchasing Price Update From Bulk Prices Upload"." (Supplier - ".$supplier_product->supplier->reference_name." )";
        //                         $product_history->old_value   = $old_price_value;
        //                         $product_history->new_value   = $row[6];
        //                         $product_history->save();
        //                     }
        //                 }
        //                 else
        //                 {
        //                     continue;
        //                 }
        //             }
        //             else
        //             {
        //                 $error = 1;
        //                 $html_string .= '<li>There is no such item exist in the system PF# <b>'.$row[0].'</b> on row <b>'.$increment.'</b> </li>';
        //                 $increment++;
        //                 continue;
        //             }
        //         }

        //         $increment++;
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
    //         $this->response = "Prices Updated Successfully !!!";
    //         $this->result   = "false";
    //     }
    //     elseif($success == 'hasError')
    //     {
    //         $this->response = "Prices Updated Successfully, But Some Of Them Has Issues !!!";
    //         $this->result   = "withissues";
    //     }
    //     elseif($success == 'invalid')
    //     {
    //         $this->response = "File you are uploading is not a valid file, please upload a valid file !!!";
    //         $this->result   = "true";
    //     }
    // }
}
