<?php

namespace App\Imports;

use App\Models\Common\CustomerTypeCategoryMargin;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Product;
use App\Models\Common\ProductCustomerFixedPrice;
use App\Models\Common\Order\Order;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Auth;

class AddProductToOrder implements ToModel, WithStartRow
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
            //dd($refrence_number);
            $product = Product::where('refrence_code',$refrence_number)->where('status',1)->first();
            $this->product_id = $product->id;

            $order = Order::find($this->inv_id);
            $exp_unit_cost = $product->selling_price;
            $price_calculate_return = $product->price_calculate($product,$order);   
            $unit_price = $price_calculate_return[0];
            $marginValue = $price_calculate_return[1];
            $exp_unit_cost = $product->selling_price;
                
            $supplier_id = $product->supplier_id;
            $total_price_with_vat = ((($product->vat/100)*$unit_price)+$unit_price)*$row[9];
            $order->total_amount += ($total_price_with_vat);
            $order->save();
        }
            $salesWarehouse_id = Auth::user()->get_warehouse->users->id;


        return new OrderProduct([
            'order_id'   => $this->inv_id,
            'product_id'           =>  $this->product_id,
            'supplier_id'          => @$supplier_id,
            'number_of_pieces'     => $row[10], 
            'quantity'             => $row[9], 
            'exp_unit_cost'        => @$exp_unit_cost, 
            'margin'               => @$marginValue, 
            'unit_price'           => @$unit_price, 
            'total_price'          => (@$unit_price*$row[2]), 
            'total_price_with_vat' => (@$total_price_with_vat), 
            'warehouse_id'         => $salesWarehouse_id, 
            'status'               => 6, 
        ]);
}

    public function startRow():int
    {
        // TODO: Implement startRow() method.
        return 2;
    }
}
