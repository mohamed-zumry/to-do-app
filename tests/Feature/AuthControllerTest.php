<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_token()
    {
        // Arrange: Create a user
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('password123'),
        ]);



        // Act: Attempt to log in
        $response = $this->postJson('api/login', [
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ]);


        // Assert: Check if the response contains a token
        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }
}
