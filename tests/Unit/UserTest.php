<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Backend\UsersController;
use App\User;

class UserTest extends TestCase
{
    public function setUp() : void
	{   parent::setUp();
     	$user = User::find(21);
		$this->actingAs($user);
    }

    public function testAddUser()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace([
            "_token" => "gn2WdinUCJg9yNLysrMxwT5pYuEeXZxiPirifvFk",
            "first_name" => "Hasnain",
            "user_name" => "Hasnain Khan",
            "phone_number" => "123",
            "email" => "hasnain123@gmail.com",
            "user_role" => "1",
            "user_company" => "1",
            "warehouse_id" => "1",
            "is_default" => null
        ]);
        $response = (new UsersController)->add($request);
        $result = json_decode($response->getContent());
        $user = User::where('user_name', '=', 'Hasnain Khan')->first();
        $this->assertEquals($request->user_name,$user->user_name);
    }

    public function testUpdatingName()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace([
            "user_id" => "1",
            "name" => "Tamara"
        ]);
        $response = (new UsersController)->saveUserDataUserDetailPage($request);
        $result = json_decode($response->getContent());
        $user = User::where('id', '=', '1')->first();
        $this->assertEquals($request->name,$user->name);
    }

    public function testUpdatingUserName()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace([
            "user_id" => "1",
            "user_name" => "Tamara@gmail.com"
        ]);
        $response = (new UsersController)->saveUserDataUserDetailPage($request);
        $result = json_decode($response->getContent());
        $user = User::where('id', '=', '1')->first();
        $this->assertEquals($request->user_name,$user->user_name);
    }

    public function testUpdatingBillingName()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace([
            "user_id" => "1",
            "company_id" => "3"
        ]);
        $response = (new UsersController)->saveUserDataUserDetailPage($request);
        $result = json_decode($response->getContent());
        $user = User::where('id', '=', '1')->first();
        $this->assertEquals($request->company_id,$user->company_id);
    }

    public function testUpdatingPassword()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace([
            "user_id" => "1",
            "new_password" => "Hasnain@2211",
            "confirm_new_password" => "Hasnain@2211"
        ]);
        $response = (new UsersController)->changeUserPassword($request);
        $result = json_decode($response->getContent());
        $this->assertTrue(true);
    }




}
