<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Http\Controllers\Purchasing\PurchaseOrderController;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use DB;

class PurchaseListTest extends TestCase
{
    public function setUp() : void
	{   parent::setUp();
     	$user = User::find(21);
		$this->actingAs($user);
    }
   
    public function testCreatingPO()
    {
        $request = new \Illuminate\Http\Request();
        $last_order = PurchaseOrder::orderBy('created_at', 'desc')->first();
        $request->replace([
            "selected_ids" => [
                0 => "50260"
            ],
              "target_ship_date_status" => "1",
              "target_ship_date_required" => "1"
        ]);
        $response = (new PurchaseOrderController)->createPurchaseOrder($request);
        $result = json_decode($response->getContent(), true);
        if($result['po_id'] > $last_order->id) {
            $this->assertTrue(true);
        } else {
            $this->assertTrue(false);
        }
    }
}
