<?php

namespace App\Imports;

use App\Models\Common\Product;
use App\Models\Common\ProductCustomerFixedPrice;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class CustomerProductFixedPriceImport implements ToCollection ,WithStartRow
{
    protected $customer_id;

    public function __construct($customer_id)
    {
        $this->customer_id = $customer_id;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
        $row = $rows->toArray();
        
        if($rows[0][0] == 'Reference Code' && $rows[0][1] == 'Fixed Price' && $rows[0][2] == 'Expiration Date (Y-M-D)') {
            $remove = array_shift($row);
            if($row[0][0] != null && $row[0][1] != null && $row[0][2] != null){
            $product = Product::where('refrence_code',$row[0])->first();
            if($product != null)
            {
                $p_c_f_p = ProductCustomerFixedPrice::where('product_id',$product->id)->where('customer_id',$this->customer_id)->first();
                if($p_c_f_p == null)
                {
                    $p_c_f_p = new ProductCustomerFixedPrice;
                }

                $p_c_f_p->product_id      = $product->id;
                $p_c_f_p->customer_id     = $this->customer_id;
                $p_c_f_p->fixed_price     = $row[0][1];
                $p_c_f_p->expiration_date = $row[0][2];
                $p_c_f_p->save();

                return;
            }
        } else {
            throw new \ErrorException('Please do not upload empty file');
        }
    } else {
        throw new \ErrorException('Please Upload Valid File');
    }

    }

    public function startRow():int
    {
        return 1;
    }
}
