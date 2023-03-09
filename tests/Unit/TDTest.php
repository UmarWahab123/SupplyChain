<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\Http\Controllers\Purchasing\PurchaseOrderController;
use App\Models\Common\Product;
use App\Models\Common\PurchaseOrders\DraftPurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\DraftPurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use Illuminate\Http\Request;
use App\Models\Common\Warehouse;


class TDTest extends TestCase
{
    public function setUp() : void
	{   parent::setUp();
     	$user = User::find(21);
		$this->actingAs($user);
    }

    public function test_Add_Product_Using_Refrence_No_in_Draft_TD()
    {
    	$request = new Request();
    	$request->replace([
    		'refrence_number' => 'bc7',
    		'draft_po_id' => [
    			'draft_po_id' => 521
    		]
    	]);
    	$order = new PurchaseOrderController();
    	$response = $order->addProdByRefrenceNumber($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
        $pod = DraftPurchaseOrderDetail::where('po_id', $request->draft_po_id['draft_po_id'])->first();
        $product = Product::find($pod->product_id);
        $this->assertEquals(strtolower($request->refrence_number), strtolower($product->refrence_code));
    }


    public function test_Add_Product_Using_Add_Item_Search_in_Draft_TD()
    {
    	$request = new Request();
    	$request->replace([
			'selected_products' => '4339',
			'draft_po_id' => 521
    	]);
    	$order = new PurchaseOrderController();
    	$response = $order->addProdToPo($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        $pod = DraftPurchaseOrderDetail::where('po_id', $request->draft_po_id)->latest()->first();
        $this->assertEquals($request->selected_products, $pod->product_id);
    }

    public function test_Save_QUuantity_in_Draft_TD()
    {
    	$request = new Request();
    	$request->replace([
			'rowId' => 1468,
			'draft_po_id' => 521,
			'quantity' => '3'
    	]);
    	$order = new PurchaseOrderController();
    	$response = $order->SaveDraftPoProductQuantity($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        // Check Quantity
        $pod = DraftPurchaseOrderDetail::find($request->rowId);
        $this->assertEquals($request->quantity, $pod->quantity);
    }

    public function test_Remove_Product_From_Draft_TD()
    {
    	$request = new Request();
    	$request->replace([
			'id' => 1468,
			'draft_po_id' => 521
    	]);
    	$order = new PurchaseOrderController();
    	$response = $order->removeProductFromDraftPo($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        $pod = DraftPurchaseOrderDetail::find($request->id);
        $this->assertEquals(null, $pod);
    }

    public function test_Save_Transfer_Date_in_Draft_TD()
    {
    	$request = new Request();
    	$request->replace([
			'draft_po_id' => 521,
			'transfer_date' => '05/04/2022'
    	]);
    	$order = new PurchaseOrderController();
    	$response = $order->SaveDraftPoDates($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        $pod = DraftPurchaseOrder::find($request->draft_po_id);
        $date = str_replace("/","-",$pod->transfer_date);
        $date = date('d/m/Y',strtotime($date));
        $this->assertEquals($request->transfer_date, $date);
    }

    public function test_Save_Target_Receiving_Date_in_Draft_TD()
    {
    	$request = new Request();
    	$request->replace([
			'draft_po_id' => 521,
			'target_receive_date' => '05/04/2022'
    	]);
    	$order = new PurchaseOrderController();
    	$response = $order->SaveDraftPoDates($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        $pod = DraftPurchaseOrder::find($request->draft_po_id);
        $date = str_replace("/","-",$pod->target_receive_date);
        $date = date('d/m/Y',strtotime($date));
        $this->assertEquals($request->target_receive_date, $date);
    }

    public function test_Save_Draft_TD()
    {
    	$po = PurchaseOrder::latest()->first();
    	$request = new Request();
    	$request->replace([
			"copy_and_update" => null,
			"selected_supplier_id" => null,
			"selected_warehouse_id" => "4",
			"supplier" => "w-4",
			"warehouse_id" => "1",
			"transfer_date" => "02/03/2022",
			"target_receive_date" => "2022-04-05",
			"payment_due_date" => null,
			"memo" => null,
			"quantity" => "3.000",
			"refrence_code" => null,
			"note" => null,
			"draft_po_id" => "521",
			"action" => "save"
    	]);
    	$order = new PurchaseOrderController();
    	$order->doActionDraftTd($request);

        $po_latest = PurchaseOrder::latest()->first();
        $this->assertNotEquals($po->id, $po_latest->id);
    }

}
