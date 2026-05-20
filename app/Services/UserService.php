<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function createUser(array $data): User
    {
        return User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
            'is_active' => (bool) ($data['is_active'] ?? true),
            'email_verified_at' => now(),
        ]);
    }

    public function updateUser(User $user, array $data, User $actor): User
    {
        if ($actor->is($user) && array_key_exists('role_id', $data) && (int) $data['role_id'] !== (int) $user->role_id) {
            throw ValidationException::withMessages([
                'role_id' => 'No puedes cambiar tu propio rol.',
            ]);
        }

        $payload = Arr::only($data, ['name', 'email', 'role_id', 'is_active']);

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        return $user->refresh();
    }

    public function toggleStatus(User $user): User
    {
        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        return $user->refresh();
    }

    public function assignRole(User $user, int $roleId, User $actor): User
    {
        if ($actor->is($user) && (int) $roleId !== (int) $user->role_id) {
            throw ValidationException::withMessages([
                'role_id' => 'No puedes cambiar tu propio rol.',
            ]);
        }

        $user->update([
            'role_id' => $roleId,
        ]);

        return $user->refresh();
    }
}
