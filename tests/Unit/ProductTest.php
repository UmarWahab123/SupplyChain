<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Purchasing\ProductController;
use App\User;
use App\Models\Common\Product;
use App\Models\Common\SupplierProducts;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\ProductCustomerFixedPrice;
use App\Models\Common\StockManagementOut;
use App\Models\Common\StockManagementIn;

class ProductTest extends TestCase
{
    public function setUp() : void
    {   parent::setUp();
        $user = User::find(43);
        $this->actingAs($user);
    }
    /**
     * A basic unit test example.
     *
     * @return void
     */
    //Product Detail Page Testing
    public function testSetDimension()
    {
        //unit test for setting length width and height
        $request = new \Illuminate\Http\Request();
        $request->replace(['prod_detail_id' => 3912,'length' => 180,'old_value'=> 90]);
        $response = (new ProductController)->saveProdDataProdDetailPage($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        $product = Product::find($request->prod_detail_id);
        $this->assertEquals($request->length, $product->length);
    }

    public function test_Save_Product_Data_in_Product_Detail_page()
    {
        //change column in request accprding to data field saved e.g short_desc, brand, category etc and old value as well
        $request = new \Illuminate\Http\Request();
        $request->replace([
            'prod_detail_id' => 3912,
            'short_desc' => '1st choice black truffle "Tuber Melanosporum" juice (100g/tin) 123',
            'old_value'=> '1st choice black truffle "Tuber Melanosporum" juice (100g/tin)1'
        ]);
        $response = (new ProductController)->saveProdDataProdDetailPage($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        $product = Product::find($request->prod_detail_id);
        //change accordingly as request changes
        $this->assertEquals($request->short_desc, $product->short_desc);
    } 

    public function test_Edit_Product_Supplier_Data_in_Product_Detail_page()
    {
        //change column in request accprding to data field saved e.g buying_price and old value as well
        $request = new \Illuminate\Http\Request();
        $request->replace([
            "id" => "4585",
            "prod_detail_id" => "3912",
            "buying_price" => "100",
            "old_value" => "1000"
        ]);
        $response = (new ProductController)->editProdSuppData($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        $product_supp = SupplierProducts::find($request->id);
        //change accordingly as request changes
        $this->assertEquals($request->buying_price, $product_supp->buying_price);
    }

    public function test_Edit_Product_Margin_Data_in_Product_Detail_page()
    {
        //change column in request accprding to data field saved e.g buying_price and old value as well
        $request = new \Illuminate\Http\Request();
        $request->replace([
            'id' => '19499',
            'prod_detail_id' => '3912',
            'default_value' => '53.80',
            'old_value' => '53.85'
        ]);
        $response = (new ProductController)->editProdMarginData($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        $product_margins = CustomerTypeProductMargin::find($request->id);
        //change accordingly as request changes
        $this->assertEquals($request->default_value, $product_margins->default_value);
    }

    public function test_Add_Product_Customer_Fixed_Price_in_Product_Detail_page()
    {
        $last_id = ProductCustomerFixedPrice::latest()->first()->id;
        $request = new \Illuminate\Http\Request();
        $request->replace([
            "product_id" => "3912",
            "customers" => "1863",
            "default_price" => "65,182.823",
            "fixed_price" => "65182",
            "expiration_date" => "09/04/2022"
        ]);
        $response = (new ProductController)->addProductCustomerFixedPrice($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        $latest_id = ProductCustomerFixedPrice::latest()->first()->id;
        $this->assertNotEquals($last_id, $latest_id);
    }

    public function test_Edit_Product_Customer_Fixed_Price_in_Product_Detail_page()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace([
            'id' => '103',
            'prod_detail_id' => '3912',
            'fixed_price' => '6518',
            'old_value' => '65182.000000'
        ]);
        $response = (new ProductController)->editProdFixedPriceData($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        $fixed_price = ProductCustomerFixedPrice::find($request->id);
        $this->assertEquals($request->fixed_price, round($fixed_price->fixed_price, 2));
    }

    public function test_Make_Manual_Stock_Adjustment_in_Product_Detail_Page()
    {
        $last_stock_out_id = StockManagementOut::latest()->first()->id;
        $last_stock_in_id = StockManagementIn::latest()->first()->id;
        $request = new \Illuminate\Http\Request();
        $request->replace([
            "prod_id" => "3912",
            "warehouse_id" => "1",
            "stock_id" => "4047"
        ]);
        $response = (new ProductController)->makeManualStockAdjustment($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        if($request->stock_id == 'parent_stock')
        {
            $latest_stock_in_id = StockManagementIn::latest()->first()->id;
            $this->assertNotEquals($last_stock_in_id, $latest_stock_in_id);
        }
        else
        {
            $latest_stock_id = StockManagementOut::latest()->first()->id;
            $this->assertNotEquals($last_stock_out_id, $latest_stock_id);
        }
    }
    
    public function test_Update_Stock_Record_in_Product_Detail_Page()
    {
        $request = new \Illuminate\Http\Request();
        //change column name in request data according to column field entered
        $request->replace([
            'id' => '67083',
            'title' => 'Manual Adjustment',
            'old_value' => 'Lost'
        ]);
        $response = (new ProductController)->updateStockRecord($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        if ($request->has('expiration_date')) 
        {
            $stock_in = StockManagementIn::find($request->id);
            $expiration_date = str_replace("/","-",$stock_in->expiration_date);
            $expiration_date =  date('d-m-Y',strtotime($expiration_date));
            $this->assertEquals($request->expiration_date, $expiration_date);
        }
        else
        {
            $stock_out = StockManagementOut::find($request->id);

            $this->assertEquals($request->title, $stock_out->title);
        }
    }
    
    public function test_Delete_Stock_Record_in_Product_Detail_Page()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace([
            "id" => "67126"
        ]);
        $response = (new ProductController)->deleteStockRecord($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
        
        $stock_out = StockManagementOut::find($request->id);
        $this->assertEquals(null, $stock_out);
    }
    // End of Product Detail Page Testing


    // Complete Products and Incomplete Products Testing
    public function test_Incomplete_Products_Data_Entery()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace([
            'prod_detail_id' => '4360',
            'supplier_id' => '82',
            'old_value' => 0
        ]);
        (new ProductController)->saveProdDataIncomplete($request);
        
        foreach($request->except('prod_detail_id', 'old_value') as $key => $value)
        {
            if ($key == 'buying_price' || $key == 'supplier_description' || $key == 'supplier_id' || $key == 'import_tax_book' || $key == 'import_tax_actual' || $key == 'freight' || $key == 'landing' || $key == 'leading_time' || $key == 'product_supplier_reference_no')
            {
                $product_detail = Product::find($request->prod_detail_id);
                if ($product_detail->supplier_id != null || $product_detail->supplier_id != 0) 
                {
                    $getSupProdData = SupplierProducts::where('product_id', $request->prod_detail_id)->where('supplier_id', $product_detail->supplier_id)->first();
                    //change 2nd parameter according to request data
                    $this->assertEquals($value, $getSupProdData->supplier_id);
                }
                else
                {
                    $getSupProdData = SupplierProducts::where('product_id', $request->prod_detail_id)->first();
                    //change 2nd parameter according to request data
                    $this->assertEquals($value, $getSupProdData->supplier_id);
                }

            }
            elseif ($key == 'product_fixed_price') 
            {
                $get_product_fixed_prices = ProductFixedPrice::where('product_id',$prod->id)->get();
                foreach ($get_product_fixed_prices as $pf_data)
                {
                    $this->assertEquals($value, $getSupProdData->product_fixed_price);
                }
            }
            else
            {
                $product = Product::find($request->prod_detail_id);
                //change 2nd parameter according to request data
                $this->assertEquals($value, $product->buying_unit);
            }
        }
    }


}
