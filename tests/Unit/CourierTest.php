<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Backend\HomeController;

class CourierTest extends TestCase
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

    public function testEditCourierForCourierType()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['title' => 'Bollore Italia','courier_type_select' => 1,'editid'=> 5]);
        $response = (new HomeController)->editCourier($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }
}
