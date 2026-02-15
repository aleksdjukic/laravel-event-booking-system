<?php

namespace Tests\Concerns;

use App\Domain\User\Enums\Role;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\Hash;

trait CreatesUsers
{
    protected function createUser(Role $role, string $email): User
    {
        $user = new User();
        $user->name = ucfirst($role->value).' User';
        $user->email = $email;
        $user->password = Hash::make('password123');
        $user->role = $role;
        $user->save();

        return $user;
    }
}
