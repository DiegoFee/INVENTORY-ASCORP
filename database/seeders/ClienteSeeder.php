<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

// Autor: Celvin
// Descripcion: Seeder para clientes.
class ClienteSeeder extends Seeder
{
    // Autor: Celvin
    // Descripcion: Ejecuta el seeding de clientes.
    public function run(): void
    {
        Cliente::factory()->count(5)->create();
    }
}
