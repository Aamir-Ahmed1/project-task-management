<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Use the seeded MySQL database
        config()->set('database.default', 'mysql');
    }

    public function test_login_returns_token()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success', 'message', 'data' => ['user' => ['id', 'name', 'email', 'roles'], 'token']
        ]);
    }

    public function test_spa_page_loads()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Task Manager');
        $response->assertSee('app');
    }
}
