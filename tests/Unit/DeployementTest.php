<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Backend\ConfigurationController;
use App\Models\Common\Deployment;

class DeployementTest extends TestCase
{
    public function setUp() : void
	{   parent::setUp();
     	$user = User::find(21);
		$this->actingAs($user);
    }

    public function test_Add_New_Deployment()
    {
    	$request = new Request();
    	$request->replace([
    		'type' => 'Woocom',
			'url' => 'abc.com',
			'price' => 1,
			'warehouse' => 1,
			'deployment_id' => null
    	]);
    	$config = new ConfigurationController();
    	$response = $config->SaveDeploymentsData($request);
    	$result = json_decode($response->getContent());

    	$deployment = Deployment::latest()->first();
        $this->assertEquals($request->url, $deployment->url);
    }

    public function test_Edit_Deployment()
    {
    	$request = new Request();
    	$request->replace([
    		'type' => 'Woocom',
			'url' => 'abc.com',
			'price' => 1,
			'warehouse' => 2,
			'deployment_id' => 7 //id that exists in db table
    	]);
    	$config = new ConfigurationController();
    	$response = $config->SaveDeploymentsData($request);
    	$result = json_decode($response->getContent());

    	$deployment = Deployment::find($request->deployment_id);
        $this->assertEquals($request->deployment_id, $deployment->id);
    }

    public function test_Delete_Deployment()
    {
    	$request = new Request();
    	$request->replace([
			'id' => 7 //id that exists in db table
    	]);
    	$config = new ConfigurationController();
    	$response = $config->DeleteDeploymentsData($request);
    	$result = json_decode($response->getContent());

    	$deployment = Deployment::find($request->id);
        $this->assertNotEquals($request->id, $deployment->id);
    }

}
