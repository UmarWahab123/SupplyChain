<?php

namespace Tests\Unit;

use App\Http\Controllers\SystemConfigurationController;
use App\User;
use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SystemConfigurationTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function setUp() : void
	{   
        parent::setUp();
     	$user = User::find(72);
		$this->actingAs($user);
    }
    public function test_store_system_configuration()
    {
        $request = new Request();
        $request->replace([
            'type' => 'type2',
			'subject' => 'subj2',
			'detail' => 'detail2'
        ]);
        $response = (new SystemConfigurationController())->store($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }
    public function test_delete_system_configuration()
    {
        $id = 17;
        $response = (new SystemConfigurationController())->deleteSystemConfigurations($id);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }
    public function test_update_system_configuration()
    {
        $request = new Request();
        $id = 15;
        $data = $request->replace([
            'type' => 'type_new',
			'subject' => 'subj_new',
			'detail' => 'detail_new'
        ]);
        $response = (new SystemConfigurationController())->updateSystemConfiguration($data,$id);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }
}
