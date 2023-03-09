<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Purchasing\PurchaseOrderController;
use Illuminate\Http\Request;
use App\User;

class POTest extends TestCase
{
	public function setUp() : void
	{   parent::setUp();
     	$user = User::find(21);
		$this->actingAs($user);
    }

    // Draft PO tests
    public function test_Add_Product_Using_Refrence_No_in_Draft_PO()
    {
    	$request = new Request();
    	$request->replace([
    		'refrence_number' => 'sy11',
    		'draft_po_id' => [
    			'draft_po_id' => 1304
    		],
    		'purchasing_vat' => null
    	]);
    	$order = new PurchaseOrderController();
    	$response = $order->addProdByRefrenceNumber($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Save_Draft_PO_Product_Quantity()
    {
    	$request = new Request();
    	$request->replace([
    		'rowId' => 1446,
    		'draft_po_id' => 1304,
			'quantity' => 5,
			'old_value' => 2
    	]);
    	$order = new PurchaseOrderController();
    	$response = $order->SaveDraftPoProductQuantity($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Update_Draft_PO_Desired_Quantity()
    {
    	$request = new Request();
    	$request->replace([
    		'rowId' => 1446,
    		'draft_po_id' => 1304,
			'desired_qty' => 10,
			'old_value' => 5
    	]);
    	$order = new PurchaseOrderController();
    	$response = $order->updateDraftPoDesiredQuantity($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Update_Draft_PO_Unit_Gross_Weight()
    {
    	$request = new Request();
    	$request->replace([
    		'rowId' => 1446,
    		'draft_po_id' => 1304,
			'pod_gross_weight' => 20,
			'old_value' => 10
    	]);
    	$order = new PurchaseOrderController();
    	$response = $order->UpdateDraftPoUnitGrossWeight($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Update_Draft_Ordered_Quantity_Unit()
    {
    	$request = new Request();
    	$request->replace([
    		'rowId' => 1446,
    		'draft_po_id' => 1304,
			'billed_unit_per_package' => 1,
			'old_value' => 2
    	]);
    	$order = new PurchaseOrderController();
    	$response = $order->updateDraftPoBilledUnitPerPackage($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Update_Draft_PO_Unit_Price()
    {
    	$request = new Request();
    	$request->replace([
    		'rowId' => 1446,
    		'draft_po_id' => 1304,
			'unit_price' => 10,
			'old_value' => 30
    	]);
    	$order = new PurchaseOrderController();
    	$response = $order->UpdateDraftPoUnitPrice($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Update_Draft_PO_Purchasing_Vat()
    {
    	$request = new Request();
    	$request->replace([
    		'rowId' => 1446,
    		'draft_po_id' => 1304,
			'pod_vat_actual' => 5,
			'old_value' => 2
    	]);
    	$order = new PurchaseOrderController();
    	$response = $order->SaveDraftPoVatActual($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Update_Draft_PO_Unit_Price_With_Vat()
    {
    	$request = new Request();
    	$request->replace([
    		'rowId' => 1446,
    		'draft_po_id' => 1304,
			'pod_unit_price_with_vat' => 15,
			'old_value' => 20
    	]);
    	$order = new PurchaseOrderController();
    	$response = $order->UpdateDraftPoUnitPriceVat($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Delete_Product_From_Draft_PO()
    {
    	$request = new Request();
    	$request->replace([
    		"id" => "1447",
  			"draft_po_id" => "1304"
    	]);
    	$order = new PurchaseOrderController();
    	$response = $order->removeProductFromDraftPo($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Discard_Draft_PO()
    {
    	$request = new Request();
    	$request->replace([
    		"draft_po_id" => "325",
  			"action" => "discard"
    	]);
    	$order = new PurchaseOrderController();
    	$response = $order->doActionDraftPo($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Save_Draft_Purchase_Order()
    {
    	$request = new Request();
    	$request->replace([
    		"draft_po_id" => "330",
  			"action" => "save"
    	]);
    	$order = new PurchaseOrderController();
    	$response = $order->doActionDraftPo($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    // PO Detail Page Testing

    public function test_Add_Product_Using_Refrence_No_in_PO_Detail()
    {
        $request = new Request();
        $request->replace([
            "refrence_number" => "DB19",
              "po_id" => [
                "po_id" => "3613"
              ],
              "purchasing_vat" => null
        ]);
        $order = new PurchaseOrderController();
        $response = $order->addProdByRefrenceNumberInPoDetail($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Update_Unit_Gross_Weight_in_PO_Detail()
    {
        $request = new Request();
        $request->replace([
            "rowId" => "24214",
            "po_id" => "3613",
            "pod_gross_weight" => "1",
            "old_value" => "0.013"
        ]);
        $order = new PurchaseOrderController();
        $response = $order->updateUnitGrossWeight($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Update_Desired_QTY_in_PO_Detail()
    {
        $request = new Request();
        $request->replace([
            "rowId" => "24214",
            "po_id" => "3613",
            "desired_qty" => "5",
            "old_value" => "6"
        ]);
        $order = new PurchaseOrderController();
        $response = $order->UpdateDesireQty($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Update_Ordered_QTY_Unit_in_PO_Detail()
    {
        $request = new Request();
        $request->replace([
            "rowId" => "24214",
            "po_id" => "3613",
            "billed_unit_per_package" => "20",
            "old_value" => "10"
        ]);
        $order = new PurchaseOrderController();
        $response = $order->UpdateBilledUnitPerPackage($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Save_Product_QTY_in_PO_Detail()
    {
        $request = new Request();
        $request->replace([
            "rowId" => "24214",
            "po_id" => "3613",
            "quantity" => "10",
            "old_value" => "50"
        ]);
        $order = new PurchaseOrderController();
        $response = $order->SavePoProductQuantity($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Update_Unit_Price_in_PO_Detail()
    {
        $request = new Request();
        $request->replace([
            "rowId" => "24214",
            "po_id" => "3613",
            "unit_price" => "2",
            "old_value" => "0.130"
        ]);
        $order = new PurchaseOrderController();
        $response = $order->UpdateUnitPrice($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Save_Product_VAT_in_PO_Detail()
    {
        $request = new Request();
        $request->replace([
            "rowId" => "24214",
            "po_id" => "3613",
            "pod_vat_actual" => "2",
            "old_value" => "--"
        ]);
        $order = new PurchaseOrderController();
        $response = $order->SavePoProductVatActual($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Update_unit_Price_With_VAT_in_PO_Detail()
    {
        $request = new Request();
        $request->replace([
            "rowId" => "24214",
            "po_id" => "3613",
            "pod_vat_actual" => "2",
            "old_value" => "--"
        ]);
        $order = new PurchaseOrderController();
        $response = $order->UpdateUnitPriceWithVat($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Save_product_Discount_in_PO_Detail()
    {
        $request = new Request();
        $request->replace([
            "rowId" => "24214",
            "po_id" => "3613",
            "discount" => "2",
            "old_value" => "--"
        ]);
        $order = new PurchaseOrderController();
        $response = $order->SavePoProductDiscount($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Delete_Product_From_PO_Detail()
    {
        $request = new Request();
        $request->replace([
            "po_id" => "3613",
            "id" => "24225"
        ]);
        $order = new PurchaseOrderController();
        $response = $order->deleteProdFromPoDetail($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Save_PO_Warehouse_in_PO_Detail()
    {
        $request = new Request();
        $request->replace([
            "rowId" => "undefined",
            "selected_ids" => "3613",
            "warehouse_id" => "1",
            "pos_count" => "0"
        ]);
        $order = new PurchaseOrderController();
        $response = $order->SavePoProductWarehouse($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Save_Target_Receiving_Date_in_PO_Detail()
    {
        $request = new Request();
        $request->replace([
            "po_id" => "3613",
            "target_receive_date" => "03/02/2021"
        ]);
        $order = new PurchaseOrderController();
        $response = $order->SavePoNote($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Save_Invoice_Date_in_PO_Detail()
    {
        $request = new Request();
        $request->replace([
            "po_id" => "3613",
            "invoice_date" => "03/04/2022"
        ]);
        $order = new PurchaseOrderController();
        $response = $order->SavePoNote($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Save_Invoice_Exchange_Rate_in_PO_Detail()
    {
        $request = new Request();
        $request->replace([
            "po_id" => "3613",
            "exchange_rate" => "37.99969600243"
        ]);
        $order = new PurchaseOrderController();
        $response = $order->SavePoNote($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Save_Supplier_Invoice_Number_in_PO_Detail()
    {
        $request = new Request();
        $request->replace([
            "po_id" => "3613",
            'invoice_number' => '1234'
        ]);
        $order = new PurchaseOrderController();
        $response = $order->SavePoNote($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Save_Memo_in_PO_Detail()
    {
        $request = new Request();
        $request->replace([
            "po_id" => "3613",
            'memo' => 'Already Pre-Ordered'
        ]);
        $order = new PurchaseOrderController();
        $response = $order->SavePoNote($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_Save_Payment_Term_in_PO_Detail()
    {
        $request = new Request();
        $request->replace([
            'payment_terms_id' => '3',
            'po_id' => '3613'
        ]);
        $order = new PurchaseOrderController();
        $response = $order->paymentTermSaveInPo($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }
    
}
