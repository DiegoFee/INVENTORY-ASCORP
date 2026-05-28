<?php

namespace App\Policies;

use App\Models\Compra;
use App\Models\Role;
use App\Models\User;

class CompraPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Warehouse);
    }

    public function view(User $user, Compra $compra): bool
    {
        return $user->hasRole(Role::Admin, Role::Warehouse);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Warehouse);
    }

    public function update(User $user, Compra $compra): bool
    {
        return $user->hasRole(Role::Admin, Role::Warehouse);
    }

    public function delete(User $user, Compra $compra): bool
    {
        return $user->hasRole(Role::Admin, Role::Warehouse);
    }

    public function receive(User $user, Compra $compra): bool
    {
        return $user->hasRole(Role::Admin, Role::Warehouse);
    }

    public function restore(User $user, Compra $compra): bool
    {
        return $user->hasRole(Role::Admin);
    }

    public function forceDelete(User $user, Compra $compra): bool
    {
        return $user->hasRole(Role::Admin);
    }
}
