<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            Role::Admin => ['*'],
            Role::Seller => ['sales.access', 'clients.access', 'invoicing.access', 'accounts_receivable.access'],
            Role::Warehouse => ['inventory.access', 'products.access', 'suppliers.access', 'alerts.access'],
        ])->each(function (array $permissions, string $name): void {
            Role::query()->updateOrCreate(
                ['name' => $name],
                ['permissions' => $permissions]
            );
        });
    }
}
