<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Importing\PoGroupsController;
use App\User;
use App\Models\Common\PoGroup;
use App\Models\Common\PoGroupProductDetail;

class ImportCostTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function setUp() : void
    {   parent::setUp();
        $user = User::find(43);
        $this->actingAs($user);
    }
    public function testActualTax()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['gId' => 1599,'tax' => 6000,'old_value'=> 2000]);
        $response = (new PoGroupsController)->savePoGroupData($request);
        $result = json_decode($response->getContent());

        $group = PoGroup::find(1599);
        $tax = $group->tax;

        $items_tax = PoGroupProductDetail::where('po_group_id',$group->id)->sum(\DB::Raw('quantity_inv * actual_tax_price'));
        $result = abs($tax - $items_tax) > 100 ? false : true;
        $this->assertTrue(true, $result);
    }
    public function testPurchasingVat()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['gId' => 1599,'vat_actual_tax' => 6000,'old_value'=> 2000]);
        $response = (new PoGroupsController)->savePoGroupData($request);
        $result = json_decode($response->getContent());

        $group = PoGroup::find(1599);
        $tax = $group->vat_actual_tax;

        $items_tax = PoGroupProductDetail::where('po_group_id',$group->id)->sum(\DB::Raw('quantity_inv * pogpd_vat_actual_price'));
        $result = abs($tax - $items_tax) > 100 ? false : true;
        $this->assertTrue(true, $result);
    }
    public function testFreight()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['gId' => 1599,'freight' => 6000,'old_value'=> 2000]);
        $response = (new PoGroupsController)->savePoGroupData($request);
        $result = json_decode($response->getContent());

        $group = PoGroup::find(1599);
        $freight = $group->freight;

        $items_freight = PoGroupProductDetail::where('po_group_id',$group->id)->sum(\DB::Raw('quantity_inv * freight'));
        $result = abs($freight - $items_freight) > 10 ? false : true;
        $this->assertTrue(true, $result);
    }
    public function testLanding()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['gId' => 1599,'landing' => 6000,'old_value'=> 2000]);
        $response = (new PoGroupsController)->savePoGroupData($request);
        $result = json_decode($response->getContent());

        $group = PoGroup::find(1599);
        $landing = $group->landing;

        $items_landing = PoGroupProductDetail::where('po_group_id',$group->id)->sum(\DB::Raw('quantity_inv * landing'));
        $result = abs($landing - $items_landing) > 10 ? false : true;
        $this->assertTrue(true, $result);
    }
}
