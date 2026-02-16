<?php

namespace Tests\Feature\Modules\Auth;

use App\Modules\Auth\Application\DTO\LoginData;
use App\Modules\Auth\Application\DTO\RegisterData;
use App\Modules\User\Domain\Enums\Role;
use App\Modules\User\Domain\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthFeatureTest extends TestCase
{
    use RefreshDatabase;

    private const INPUT_PASSWORD_CONFIRMATION = 'password_confirmation';

    public function test_register_returns_201(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            RegisterData::INPUT_NAME => 'Auth User',
            RegisterData::INPUT_EMAIL => 'auth.register@example.com',
            RegisterData::INPUT_PASSWORD => 'password123',
            self::INPUT_PASSWORD_CONFIRMATION => 'password123',
            RegisterData::INPUT_PHONE => '0601234567',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'auth.register@example.com');
    }

    public function test_register_ignores_role_and_always_creates_customer(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            RegisterData::INPUT_NAME => 'Auth User Role',
            RegisterData::INPUT_EMAIL => 'auth.register.role@example.com',
            RegisterData::INPUT_PASSWORD => 'password123',
            self::INPUT_PASSWORD_CONFIRMATION => 'password123',
            RegisterData::INPUT_PHONE => '0601234567',
            User::COL_ROLE => Role::ADMIN->value,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', Role::CUSTOMER->value);
    }

    public function test_login_returns_token(): void
    {
        $user = new User();
        $user->{User::COL_NAME} = 'Login User';
        $user->{User::COL_EMAIL} = 'auth.login@example.com';
        $user->{User::COL_PASSWORD} = Hash::make('password123');
        $user->{User::COL_ROLE} = Role::CUSTOMER;
        $user->save();

        $response = $this->postJson('/api/v1/auth/login', [
            LoginData::INPUT_EMAIL => 'auth.login@example.com',
            LoginData::INPUT_PASSWORD => 'password123',
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
        $user->{User::COL_NAME} = 'Me User';
        $user->{User::COL_EMAIL} = 'auth.me@example.com';
        $user->{User::COL_PASSWORD} = Hash::make('password123');
        $user->{User::COL_ROLE} = Role::CUSTOMER;
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
        $user->{User::COL_NAME} = 'Logout User';
        $user->{User::COL_EMAIL} = 'auth.logout@example.com';
        $user->{User::COL_PASSWORD} = Hash::make('password123');
        $user->{User::COL_ROLE} = Role::CUSTOMER;
        $user->save();

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            LoginData::INPUT_EMAIL => 'auth.logout@example.com',
            LoginData::INPUT_PASSWORD => 'password123',
        ]);

        $token = $loginResponse->json('data.token');

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertNull(PersonalAccessToken::findToken($token));
    }
}
