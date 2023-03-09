<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTestPath()
    {
        $user = User::find(45);
        dd($user);
        $this->actingAs($user);
        $response = $this->get('/sales');
        $response->assertStatus(200);
    }

    public function testBasicTestLogin()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }
}
