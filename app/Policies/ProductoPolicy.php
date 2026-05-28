<?php

namespace App\Policies;

use App\Models\Producto;
use App\Models\Role;
use App\Models\User;

class ProductoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Warehouse);
    }

    public function view(User $user, Producto $producto): bool
    {
        return $user->hasRole(Role::Admin, Role::Warehouse);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Warehouse);
    }

    public function update(User $user, Producto $producto): bool
    {
        return $user->hasRole(Role::Admin, Role::Warehouse);
    }

    public function delete(User $user, Producto $producto): bool
    {
        return $user->hasRole(Role::Admin, Role::Warehouse);
    }

    public function restore(User $user, Producto $producto): bool
    {
        return $user->hasRole(Role::Admin);
    }

    public function forceDelete(User $user, Producto $producto): bool
    {
        return $user->hasRole(Role::Admin);
    }
}
