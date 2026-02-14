<?php

namespace Tests\Feature\Api\V1;

use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_201(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Auth User',
            'email' => 'auth.register@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '0601234567',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'auth.register@example.com');
    }

    public function test_register_ignores_role_and_always_creates_customer(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Auth User Role',
            'email' => 'auth.register.role@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '0601234567',
            'role' => 'admin',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', 'customer');
    }

    public function test_login_returns_token(): void
    {
        $user = new User();
        $user->name = 'Login User';
        $user->email = 'auth.login@example.com';
        $user->password = Hash::make('password123');
        $user->role = 'customer';
        $user->save();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'auth.login@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['user', 'token'],
                'errors',
            ]);
    }

    public function test_me_returns_current_user_when_authenticated(): void
    {
        $user = new User();
        $user->name = 'Me User';
        $user->email = 'auth.me@example.com';
        $user->password = Hash::make('password123');
        $user->role = 'customer';
        $user->save();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/user/me');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'auth.me@example.com');
    }

    public function test_logout_invalidates_token(): void
    {
        $user = new User();
        $user->name = 'Logout User';
        $user->email = 'auth.logout@example.com';
        $user->password = Hash::make('password123');
        $user->role = 'customer';
        $user->save();

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'auth.logout@example.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('data.token');

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertNull(PersonalAccessToken::findToken($token));
    }
}
