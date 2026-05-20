<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            [
                'name' => 'Administrador ASCORP',
                'email' => 'admin@ascorp.test',
                'role' => Role::Admin,
            ],
            [
                'name' => 'Vendedor MaxCar',
                'email' => 'vendedor@ascorp.test',
                'role' => Role::Seller,
            ],
            [
                'name' => 'Bodeguero MaxCar',
                'email' => 'bodeguero@ascorp.test',
                'role' => Role::Warehouse,
            ],
        ])->each(function (array $user): void {
            $role = Role::query()->where('name', $user['role'])->firstOrFail();

            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'role_id' => $role->id,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        });
    }
}
