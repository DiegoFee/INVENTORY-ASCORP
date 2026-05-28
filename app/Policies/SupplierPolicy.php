<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Warehouse);
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $user->hasRole(Role::Admin, Role::Warehouse);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Warehouse);
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $user->hasRole(Role::Admin, Role::Warehouse);
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->hasRole(Role::Admin, Role::Warehouse);
    }

    public function restore(User $user, Supplier $supplier): bool
    {
        return $user->hasRole(Role::Admin);
    }

    public function forceDelete(User $user, Supplier $supplier): bool
    {
        return $user->hasRole(Role::Admin);
    }
}
