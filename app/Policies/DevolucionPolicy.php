<?php

namespace App\Policies;

use App\Models\Devolucion;
use App\Models\Role;
use App\Models\User;

class DevolucionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Seller);
    }

    public function view(User $user, Devolucion $devolucion): bool
    {
        return $user->hasRole(Role::Admin, Role::Seller);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Admin, Role::Seller);
    }

    public function approve(User $user, Devolucion $devolucion): bool
    {
        return $user->hasRole(Role::Admin, Role::Seller);
    }

    public function reject(User $user, Devolucion $devolucion): bool
    {
        return $user->hasRole(Role::Admin, Role::Seller);
    }
}
