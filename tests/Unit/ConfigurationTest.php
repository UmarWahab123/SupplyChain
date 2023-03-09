<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Backend\ConfigurationController;
use App\Models\Common\Configuration;

class ConfigurationTest extends TestCase
{
    public function setUp() : void
	{   parent::setUp();
     	$user = User::find(21);
		$this->actingAs($user);
    	}

    public function testExample()
    	{
        $this->assertTrue(true);
    	}

    public function test_save_woocom_warehouse_id__in_configurations_table()
    	{
    		$request = (new Request)->replace([
		  "configuration_id" => "1",
		  "company_name" => "Testing Ecom",
		  "currency_code" => "1",
		  "system_email" => "supplychain@pkteam.com",
		  "purchasing_email" => "daniyal@knowpakistan.net",
		  "billing_email" => "akif@knowpakistan.net",
		  "system_bg_color" => "#0a0a0a",
		  "system_bg_txt_color" => "#ffffff",
		  "btn_hover_color" => "#3f7362",
		  "btn_hover_txt_color" => "#fafafa",
		  "email_notification" => "on",
		  "woocommerce" => "on",
		  "woocom_warehouse_id" => "2",
		  "quotation_prefix" => "Quot",
		  "draft_invoice_prefix" => "Draft",
		  "invoice_prefix" => "Invoice"
    		]);
    	(new ConfigurationController)->update($request);

        $config = Configuration::find($request->configuration_id);
        $this->assertEquals($request->woocom_warehouse_id, $config->woocom_warehouse_id);
    	}
}
