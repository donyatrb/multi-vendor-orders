<?php

namespace App\Modules\Auth\Tests\Feature;

use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function user_cannot_be_created_with_less_than_8_char_password(): void
    {
        $response = $this->post('/auth/register', [
            'name' => 'donya',
            'email' => 'donya@gmail.com',
            'password' => '1234567',
        ]);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'status' => 'failed',
            'message' => [
                'password' => ['The password field must be at least 8 characters.'],
            ],
        ]);
        $this->assertDatabaseEmpty('users');
    }

    /** @test */
    public function user_cannot_be_created_with_wrong_email(): void
    {
        $response = $this->post('/auth/register', [
            'name' => 'donya',
            'email' => 'donya',
            'password' => '12345678',
        ]);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'status' => 'failed',
            'message' => [
                'email' => ['The email field must be a valid email address.'],
            ],
        ]);
    }

    /** @test */
    public function redundant_user_cannot_be_created()
    {
        User::factory()->create([
            'name' => 'donya',
            'email' => 'donya@gmail.com',
        ]);

        $response = $this->post('/auth/register', [
            'name' => 'donya',
            'email' => 'donya@gmail.com',
            'password' => '12345678',
        ]);

        $response->assertUnprocessable();
        $response->assertExactJson([
            'status' => 'failed',
            'message' => [
                'email' => ['The email has already been taken.',
                ],
            ],
        ]);
    }

    /** @test */
    public function user_is_being_registered_successfully()
    {
        $response = $this->post('/auth/register', [
            'name' => 'donya',
            'email' => 'donya@gmail.com',
            'password' => '12345678',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data',
            'message',
        ]);

        $jsonResponse = $response->json();
        $this->assertEquals(true, $jsonResponse['status']);
        $this->assertEquals('User Created Successfully', $jsonResponse['message']);
    }
}
