<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Warehouse\HomeController;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Order\Order;

class PITest extends TestCase
{
    public function setUp() : void
	{   parent::setUp();
     	$user = User::find(21);
		$this->actingAs($user);
    }

    public function test_Edit_Pick_Instruction()
    {
    	// change request according to relevant field i.e pcs_shipped, qty_shipped, expiration_date etc
    	$request = (new Request)->replace([
			'order_product_id' => '11181',
			'pcs_shipped' => '1'
    	]);
    	(new HomeController)->editPickInstruction($request);

        $order_product = OrderProduct::find($request->order_product_id);
        $this->assertEquals($request->pcs_shipped, $order_product->pcs_shipped);
    }

    public function test_Full_Qty_Button()
    {
    	$request = (new Request)->replace([
			'id' => '4868'
    	]);
    	(new HomeController)->fullQtyShipImporting($request);

        $query = OrderProduct::where('order_id', $request->id)->get();
        foreach($query as $item){
	        $this->assertEquals($item->qty_shipped, $item->quantity);
        }
    }

    public function test_Confirm_PI()
    {
    	$request = (new Request)->replace([
			"order_id" => "6977",
			"page_info" => "pickInstruction"
    	]);
    	(new HomeController)->confirmPickInstruction($request);

        $order = Order::find($request->order_id);
	    $this->assertEquals('3', $order->primary_status);
    }
}
