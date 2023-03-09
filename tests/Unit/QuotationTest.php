<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Sales\OrderController;
use App\User;
use App\Models\Common\Order\DraftQuotationProduct;
use App\Models\Common\Product;
use App\Models\Common\Order\OrderProduct;
use Excel;
use App\Exports\DraftQuotationExport;

class QuotationTest extends TestCase
{
    public function setUp() : void
	{   parent::setUp();
     	$user = User::find(21);
		$this->actingAs($user);
    }


    // DRAFT QUOTATION TESTING START
    public function test_update_quoatation_quantity()
    {
        
        $request = new \Illuminate\Http\Request();
        $request->replace(['draft_quotation_id' => 45601,'quantity' => 74,'old_value'=> 12]);
        $response = (new OrderController)->UpdateQuotationData($request);
        $result = json_decode($response->getContent());
        $product = DraftQuotationProduct::where('draft_quotation_id','=',27976)->first();
        $this->assertEquals('74',$product['quantity']);
    }

    public function test_update_quoatation_supplier()
    {
        
        $request = new \Illuminate\Http\Request();
        $request->replace(['draft_quotation_id' => 45601,'from_warehouse_id' => 'w-1','old_value'=> "Antony Fromagerie"]);
        $response = (new OrderController)->UpdateQuotationData($request);
        $result = json_decode($response->getContent());
        $product = DraftQuotationProduct::where('draft_quotation_id','=',27976)->first();
        $this->assertEquals('1',$product['from_warehouse_id']);
    }

    public function test_update_quoatation_description()
    {
        
        $request = new \Illuminate\Http\Request();
        $request->replace(['draft_quotation_id' => 45601,'short_desc' => 'testing','old_value'=> "testinggggg"]);
        $response = (new OrderController)->UpdateQuotationData($request);
        $result = json_decode($response->getContent());
        $product = DraftQuotationProduct::where('draft_quotation_id','=',27976)->first();
        $this->assertEquals('testing',$product['short_desc']);
    }

    public function test_update_quoatation_type()
    {
        
        $request = new \Illuminate\Http\Request();
        $request->replace(['draft_quotation_id' => 45601,'type_id' => '3','old_value'=> "4"]);
        $response = (new OrderController)->UpdateQuotationData($request);
        $result = json_decode($response->getContent());
        $product = DraftQuotationProduct::where('draft_quotation_id','=',27976)->first();
        $this->assertEquals('3',$product['type_id']);
    }

    public function test_update_quoatation_brand()
    {
        
        $request = new \Illuminate\Http\Request();
        $request->replace(['draft_quotation_id' => 45601,'brand' => 'hasnain','old_value'=> null]);
        $response = (new OrderController)->UpdateQuotationData($request);
        $result = json_decode($response->getContent());
        $product = DraftQuotationProduct::where('draft_quotation_id','=',27976)->first();
        $this->assertEquals('hasnain',$product['brand']);
    }

    public function test_update_quoatation_pieces()
    {
        
        $request = new \Illuminate\Http\Request();
        $request->replace(['draft_quotation_id' => 45601,'number_of_pieces' => '74','old_value'=> null]);
        $response = (new OrderController)->UpdateQuotationData($request);
        $result = json_decode($response->getContent());
        $product = DraftQuotationProduct::where('draft_quotation_id','=',27976)->first();
        $this->assertEquals('74',$product['number_of_pieces']);
    }

    public function test_update_quoatation_discount()
    {
        
        $request = new \Illuminate\Http\Request();
        $request->replace(['draft_quotation_id' => 45601,'discount' => '100','old_value'=> null]);
        $response = (new OrderController)->UpdateQuotationData($request);
        $result = json_decode($response->getContent());
        $product = DraftQuotationProduct::where('draft_quotation_id','=',27976)->first();
        $this->assertEquals('100',$product['discount']);
    }

    public function test_removing_product()
    {
        
        $request = new \Illuminate\Http\Request();
        $request->replace(['id' => 45601]);
        $response = (new OrderController)->removeProduct($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }


    public function test_adding_product_by_reference_number()
    {
        
        $request = new \Illuminate\Http\Request();
        $request->replace([
            'refrence_number' => 'bc20',
            'id' => ['id' => '27976']
        ]);
        $response = (new OrderController)->addByRefrenceNumber($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_quotation_import()
    {
        $row = [
            0 => [
              "quotation_file" => null,
              "pf" => null,
              "description" => "testinggggg",
              "note" => null,
              "category" => "CHOCOLATE",
              "type" => 1.0,
              "brand" => "--",
              "supply_from" => "Antony Fromagerie",
              "availableqty" => 11055.13,
              "customerlastprice" => 2292.06,
              "qtyordered" => "#NAME?",
              "piecesordered" => "--",
              "reference_price" => 2292.05,
              "default_price_type" => "Reference Price",
              "default_price_wo_vat" => 2292.06,
              "price_date" => 44594.0,
              "discount" => "--",
              "unit_price_with_discount" => 2292.06,
              "vat" => 7.0,
              "unit_pricevat" => 2452.5,
              "total_price_after_discount_wo_vat" => "N.A",
              "total_amount_inc_vat" => 0.0,
              "restaurant_price" => 0.0,
              "product_note" => "--",
              "draft_quotation_id" => null,
            ]
            ];

            foreach($row as $_row){
                $draft_quotation_id=(array_key_exists('draft_quotation_id', $_row)) ? $_row['draft_quotation_id'] : null;
                $pf = (array_key_exists('pf', $_row)) ? $_row['pf'] : $_row['reference_no'];
                if ($draft_quotation_id == null || $draft_quotation_id == '') {
                    $request = new \Illuminate\Http\Request();
                    $request->replace(['refrence_number' => $pf, 'id' => ['id' => '27975']]);
                    $result = app('App\Http\Controllers\Sales\OrderController')->addByRefrenceNumber($request);
        
                    
                    if ($result->original['success']) {
                        $draft_quotation_id = $result->original['getColumns']['id'];
                    }
                    else{
                        $this->errors .= $result->original['successmsg'];
                    }
                }
                    $QuantityOrder = (array_key_exists('qtyordered', $_row)) ? $_row['qtyordered'] : $_row['quantity_ordered'];
                    $QuantityOrder = (int) filter_var($QuantityOrder, FILTER_SANITIZE_NUMBER_INT);
    
                    $number_of_pieces = (array_key_exists('piecesordered', $_row)) ? $_row['piecesordered'] : $_row['pieces_ordered'];
                    $number_of_pieces = (int) filter_var($number_of_pieces, FILTER_SANITIZE_NUMBER_INT);
                
                    if($pf == 'N.A' || $pf == '') {
                        $draft_quotation_item = DraftQuotationProduct::where('id',$draft_quotation_id)->first(['quantity','discount','unit_price','number_of_pieces','unit_price_with_vat','vat']);
                    }
                    else{
                        $product_id=Product::where('refrence_code',$pf)->pluck('id')->toArray();
                        $product_id=$product_id[0];
                        $draft_quotation_item = DraftQuotationProduct::where(['id'=>$draft_quotation_id,'product_id'=>$product_id])->first(['quantity','discount','unit_price','number_of_pieces','unit_price_with_vat','vat']);
                    }
                    
    
                    $old_quantity=$draft_quotation_item['quantity'];
                    $old_vat=$draft_quotation_item['vat'];
                    $old_unit_price_with_vat=$draft_quotation_item['unit_price_with_vat'];
                    $old_number_of_pieces=$draft_quotation_item['number_of_pieces'];
                    $old_price_without_vat=$draft_quotation_item['unit_price'];
                    $old_discount=$draft_quotation_item['discount'];
    
                    if($old_quantity!=$QuantityOrder){
                        $request = new \Illuminate\Http\Request();
                            $request->replace(['draft_quotation_id' => $draft_quotation_id, 'quantity' => $QuantityOrder, 'old_value' => $old_quantity]);
                            app('App\Http\Controllers\Sales\OrderController')->UpdateQuotationData($request);
                    }
    
    
    
    
                    if((is_numeric($_row['vat'])) && $old_vat!=$_row['vat']){
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['draft_quotation_id' => $draft_quotation_id, 'vat' => $_row['vat'], 'old_value' => $old_vat]);
                        app('App\Http\Controllers\Sales\OrderController')->UpdateQuotationData($request);
                    }
    
    
                    $unit_price_with_vat = (array_key_exists('unit_pricevat', $_row)) ? $_row['unit_pricevat'] : $_row['unit_price_vat'];
                    if((is_numeric($unit_price_with_vat))&&($old_unit_price_with_vat != $unit_price_with_vat)){
                    $request = new \Illuminate\Http\Request();
                    $request->replace(['draft_quotation_id' => $draft_quotation_id, 'unit_price_with_vat' => $unit_price_with_vat, 'old_value' => $old_unit_price_with_vat]);
    
                    app('App\Http\Controllers\Sales\OrderController')->UpdateQuotationData($request);
                    }
    
                    $piecesordered = (array_key_exists('piecesordered', $_row)) ? $_row['piecesordered'] : $_row['pieces_ordered'];
                    if(is_numeric($old_number_of_pieces))
                    {
    
                    if($number_of_pieces!=$old_number_of_pieces){
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['draft_quotation_id' => $draft_quotation_id, 'number_of_pieces' => $piecesordered, 'old_value' => $old_number_of_pieces]);
                        app('App\Http\Controllers\Sales\OrderController')->UpdateQuotationData($request);
                    }
                    }
    
                    $default_price_wo_vat = (array_key_exists('default_price_wo_vat', $_row)) ? $_row['default_price_wo_vat'] : $_row['unit_price'];
                    if(is_numeric($old_price_without_vat)&&($old_price_without_vat!=$default_price_wo_vat)){
                    $request = new \Illuminate\Http\Request();
                    $request->replace(['draft_quotation_id' => $draft_quotation_id, 'unit_price' => $default_price_wo_vat, 'old_value' => $old_price_without_vat]);
                    app('App\Http\Controllers\Sales\OrderController')->UpdateQuotationData($request);
                    }
    
                    if(is_numeric($_row['discount'])&&($old_discount!=$_row['discount'])){
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['draft_quotation_id' => $draft_quotation_id, 'discount' => $_row['discount'], 'old_value' => $old_discount]);
                        app('App\Http\Controllers\Sales\OrderController')->UpdateQuotationData($request);
                    }
               
            }
        $this->assertTrue(true);
    }

       public function testUpdateQuoatationExport()
    {
        Excel::fake();
        $data = ['id'=> 27976];
        $response = $this->post(route('export-draft-quotation'),$data);
        // dd($response);
        Excel::assertDownloaded('DraftQuotationExport.xlsx', function(DraftQuotationExport $export) {
            return true;
        });
    }

    //DRAFT QUOTATION TESTING END



    //QUOTATION AND DRAFT INVOICE TESTNG START

    public function test_quotation_adding_product_by_reference_number()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace([
            'refrence_number' => 'bc20',
            'id' => ['id' => '4868']
        ]);
        $response = (new OrderController)->addToOrderByRefrenceNumber($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
        $first = OrderProduct::where('order_id',$request->id['id'])->latest()->first();
        $product = Product::find($first->product_id);
        $this->assertEquals($request->reference_code,$product->reference_code);
    }

    public function test_quoatation_supplier()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['order_id' => 52339,'from_warehouse_id' => 'w-1','old_value'=> 'Antony']);
        $response = (new OrderController)->UpdateOrderQuotationData($request);
        $result = json_decode($response->getContent());
        // dd($result);
        $product = OrderProduct::where('order_id','=',4868)->first();
        // dd($product);
        $this->assertEquals('1',$product['user_warehouse_id']);
    }

    public function test_quoatation_description()
    {
        
        $request = new \Illuminate\Http\Request();
        $request->replace(['order_id' => 52339,'short_desc' => 'testing','old_value'=> "Raclette Lustenberger 1/2 (Swiss)"]);
        $response = (new OrderController)->UpdateOrderQuotationData($request);
        $result = json_decode($response->getContent());
        $product = OrderProduct::where('order_id','=',4868)->first();
        // dd($product['short_desc']);
        $this->assertEquals('testing',$product['short_desc']);
    }


    public function test_quoatation_type()
    {
        
        $request = new \Illuminate\Http\Request();
        $request->replace(['order_id' => 52339,'type_id' => '1','old_value'=> "4"]);
        $response = (new OrderController)->UpdateOrderQuotationData($request);
        $result = json_decode($response->getContent());
        $product = OrderProduct::where('order_id','=',4868)->first();
        $this->assertEquals('1',$product['type_id']);
    }


    public function test_quoatation_brand()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['order_id' => 11181,'brand' => 'hasnain','old_value'=> 'Lustenberger']);
        $response = (new OrderController)->UpdateOrderQuotationData($request);
        $result = json_decode($response->getContent());
        $product = OrderProduct::where('order_id','=',4868)->first();
        // dd($product);
        $this->assertEquals('hasnain',$product['brand']);
    }

    public function test_quoatation_quantity()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['order_id' => 11181,'quantity' => 74,'old_value'=> '12']);
        $response = (new OrderController)->UpdateOrderQuotationData($request);
        $result = json_decode($response->getContent());
        $product = OrderProduct::where('order_id','=',4868)->first();
        $this->assertEquals('74',$product['quantity']);
    }

    public function test_quoatation_pieces()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['order_id' => 11181,'number_of_pieces' => '74','old_value'=> '1']);
        $response = (new OrderController)->UpdateOrderQuotationData($request);
        $result = json_decode($response->getContent());
        $product = OrderProduct::where('order_id','=',4868)->first();
        $this->assertEquals('74',$product['number_of_pieces']);
    }


    public function test_quoatation_discount()
    {
        
        $request = new \Illuminate\Http\Request();
        $request->replace(['order_id' => 11181,'discount' => '100','old_value'=> '12']);
        $response = (new OrderController)->UpdateOrderQuotationData($request);
        $result = json_decode($response->getContent());
        $product = OrderProduct::where('order_id','=',4868)->first();
        $this->assertEquals('100',$product['discount']);
    }

    public function test_quoatation_vat()
    {
        
        $request = new \Illuminate\Http\Request();
        $request->replace(['order_id' => 11181,'vat' => '74','old_value'=> '7']);
        $response = (new OrderController)->UpdateOrderQuotationData($request);
        $result = json_decode($response->getContent());
        $product = OrderProduct::where('order_id','=',4868)->first();
        $this->assertEquals('74',$product['vat']);
    }

 
    public function test_quotation_removing_product()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['id' => 11181]);
        $response = (new OrderController)->removeOrderProduct($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function test_quotation_confirm_quotation()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['inv_id' => 4868,'user_id' => '30']);
        $response = (new OrderController)->makeDraftInvoice($request);
        $result = json_decode($response->getContent());
        // dd($result);
        if($result->already_draft_invoice == 2)
        {
            $this->assertViewHas('title', 'Success');
        }
    }


    //QUOTATION AND DRAFT INVOICE TESTING END

    



}
