<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Backend\ProductCategoryController;
use Illuminate\Http\Request;
use App\Models\WooCom\WebEcomProductCategory;

class EcomTest extends TestCase
{
    public function test_Add_Category_to_Woo_Com()
    {
    	$request = (new Request)->replace([
			'id' => '5'
    	]);
    	(new ProductCategoryController)->syncProductCategory($request);
        $check_cat = WebEcomProductCategory::where('web_category_id',$request->id)->latest()->first();
        $this->assertEquals($request->id, $check_cat->web_category_id);
    }
}
