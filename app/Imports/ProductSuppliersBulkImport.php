<?php

namespace App\Imports;
use Auth;
use App\Models\Common\Product;
use App\Models\Common\SupplierProducts;
use App\Models\Common\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ProductSuppliersBulkImport implements ToModel ,WithStartRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        if($row[0] == null)
        {
            return false;
        }

        $product = new Product();

        $product->refrence_code        = $row[0];
        $product->hs_code              = $row[1];
        $product->name                 = $row[2];
        $product->short_desc           = $row[3];
        $product->long_desc            = $row[4];
        $product->weight               = $row[5];
        $product->primary_category     = $row[6];
        $product->category_id          = $row[7];
        $product->product_type         = $row[8];
        $product->product_temprature_c = $row[9];
        $product->buying_unit          = $row[10];
        $product->selling_unit         = $row[11];
        $product->unit_conversion_rate = $row[12];
        $product->import_tax_book      = $row[13];
        $product->vat                  = $row[14];

        $product->save();

    }

    protected function generateRandomString($length)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function startRow():int
    {
        return 2;
    }
}
