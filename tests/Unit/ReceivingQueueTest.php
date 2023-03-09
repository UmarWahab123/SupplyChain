<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Warehouse\PoGroupsController;
use App\Models\Common\PoGroupProductDetail;
use App\Models\Common\PoGroup;

class ReceivingQueueTest extends TestCase
{
	public function setUp() : void
	{   parent::setUp();
     	$user = User::find(21);
		$this->actingAs($user);
    }

    public function test_Edit_Data_in_Receiving_Queue()
    {
    	// Change request data according to data entered i.e. quantity_received_1, quantity_received_2, expiration_date_1, expiration_date_2, etc
    	$request = (new Request)->replace([
			'p_g_p_d_id' => '19236',
			'quantity_received_1' => '1',
			'po_group_id' => '1045'
    	]);
    	(new PoGroupsController)->editPoGroupProductDetails($request);

       	$po_detail = PoGroupProductDetail::find($request->p_g_p_d_id);
        $this->assertEquals($request->quantity_received_1, $po_detail->quantity_received_1);
    }

    public function test_Full_Qty_For_Receiving()
    {
    	$request = (new Request)->replace([
			'id' => '1045'
    	]);
    	(new PoGroupsController)->fullQtyForReceiving($request);

       	$po_group_por_details = PoGroupProductDetail::where('status',1)->where('po_group_id',$request->id)->get(); 
		foreach($po_group_por_details as $item)
		{
        	$this->assertEquals($item->quantity_received_1, $item->quantity_inv);
		}
    }

    public function test_Confirm_PO_Group_Detail()
    {
    	$request = (new Request)->replace([
			"id" => "1045"
    	]);
    	(new PoGroupsController)->confirmPoGroupDetail($request);

       	$po_group = PoGroup::find($request->id);
        $this->assertEquals('1', $po_group->is_confirm);
    }
}
