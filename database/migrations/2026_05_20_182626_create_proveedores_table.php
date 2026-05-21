<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Migración: Crear tabla proveedores
|--------------------------------------------------------------------------
| Autor: ASCORP
| Fecha: 2026-05-20
*/

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id()->comment('Identificador único auto-incremental del proveedor');

            $table->string('nombre', 255)
                ->comment('Nombre comercial o razón social del proveedor');

            $table->string('nit', 20)
                ->unique()
                ->comment('Número de Identificación Tributaria único del proveedor');

            $table->string('telefono', 20)
                ->comment('Número de teléfono principal del proveedor');

            $table->string('email', 255)
                ->unique()
                ->comment('Correo electrónico único del proveedor');

            $table->text('direccion')
                ->comment('Dirección física del proveedor');

            $table->string('contacto_nombre', 255)
                ->nullable()
                ->comment('Nombre de la persona de contacto del proveedor');

            $table->timestamps();

            $table->softDeletes()
                ->comment('Fecha de eliminación suave del registro');

            $table->index('nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
