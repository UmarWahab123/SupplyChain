<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Sales\OrderController;
class QuotationTesting extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testExampleQuotation()
    {
        $this->assertTrue(true);
    }

    public function testUpdateQuoatation()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['draft_quotation_id' => 45569,'quantity' => 74,'customer_id'=> 12]);
        $response = (new OrderController)->UpdateQuotationData($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }
}
