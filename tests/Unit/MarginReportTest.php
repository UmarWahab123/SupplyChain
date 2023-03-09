<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use App\Http\Controllers\Purchasing\ProductController;

class MarginReportTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function testExportMarginReportByProduct()
    {
    	$request = new Request();
        $request->replace(['from_date_exp' => '01/10/2021', 
        					'to_date_exp' => '28/12/2021', 
        					'supplier_id' => null, 
        					'category_id' => null, 
        					'sale_id' => null, 
        					'filter' => 'product', 
        					'customer_selected' => null]);
        $responce = (new ProductController)->ExportMarginReportByProductName($request);
        $responce = json_decode($responce->getContent());
        $this->assertEquals(1, $responce->status);
    }
}
