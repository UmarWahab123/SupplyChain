<?php

namespace App\Imports;

use App\Models\Common\CustomerTypeCategoryMargin;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\Order\DraftQuotation;
use App\Models\Common\Order\DraftQuotationProduct;
use App\Models\Common\Product;
use App\Models\Common\ProductCustomerFixedPrice;
use Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class AddProductToTempQuotation implements ToModel, WithStartRow
{
    protected $inv_id;
    protected $product_id = null;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function __construct($inv_id)
    {
        $this->inv_id = $inv_id;
    }
    public function model(array $row)
    {
        $this->product_id = null;
        if($row[0] !== null)
        {
            $refrence_number = $row[0];
            $product = Product::where('refrence_code',$refrence_number)->where('status',1)->first();
            $this->product_id = $product->id;

            $marginValue = null;        
            $order = DraftQuotation::find($this->inv_id);
            $exp_unit_cost = $product->selling_price;

            $price_calculate_return = $product->price_calculate($product,$order);   
            $unit_price = $price_calculate_return[0];
            $marginValue = $price_calculate_return[1];            
            $total_price_with_vat = (($product->vat/100)*$unit_price)+$unit_price;
        }
        $salesWarehouse_id = Auth::user()->get_warehouse->users->id;
        return new DraftQuotationProduct([
            'draft_quotation_id'   => $this->inv_id,
            'product_id'           =>  $this->product_id,
            'name'                 => $row[1], 
            'short_desc'           => $row[2], 
            'number_of_pieces'     => $row[3], 
            'quantity'             => $row[4], 
            'exp_unit_cost'        => @$exp_unit_cost, 
            'margin'               => @$marginValue, 
            'unit_price'           => @$unit_price, 
            'total_price'          => (@$unit_price*$row[4]), 
            'total_price_with_vat' => (@$total_price_with_vat*$row[4]), 
            'warehouse_id'         => $salesWarehouse_id, 
        ]);
    }

    public function startRow():int
    {
        // TODO: Implement startRow() method.
        return 2;
    }
}
