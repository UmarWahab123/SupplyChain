<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Api\PosController;
use App\Models\Pos\PosIntegration;


class PosTest extends TestCase
{
    public function testStoreData()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace([
            'device_name' => 'iPhone',
            'warehouse_id' => 1
        ]);
        $response = (new PosController)->store($request);
        $result = json_decode($response->getContent());
        $insertion = PosIntegration::where('device_name', '=', 'iPhone')->first();
        $this->assertEquals($request->device_name,$insertion->device_name);
    }

    public function testGetCategories()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace([
            'token' => 'unBLcnv7dH0NQkRWLg27qza36OjpLqgKesPtkA40GV8j4lvdJ42wPGvCcqLJmz5ygi1pGP3P0tgxiomnpseLGaqZqtbUwOVXt7fHV0ltyFlfY277JD2Cq15LGovV9a9c'
        ]);
        $response = (new PosController)->get_categories($request);
        $result = json_decode($response->getContent());
        if(count($result->categories) == 0) {
            $this->assertEquals(false, $result->success);
        } else {
            $this->assertEquals(true, $result->success);
        }
    }

    public function testGetProducts()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace([
            'token' => 'unBLcnv7dH0NQkRWLg27qza36OjpLqgKesPtkA40GV8j4lvdJ42wPGvCcqLJmz5ygi1pGP3P0tgxiomnpseLGaqZqtbUwOVXt7fHV0ltyFlfY277JD2Cq15LGovV9a9c'
        ]);
        $response = (new PosController)->get_products($request);
        $result = json_decode($response->getContent());
        if(count($result->products) == 0) {
            $this->assertEquals(false, $result->success);
        } else {
            $this->assertEquals(true, $result->success);
        }
    }
}
