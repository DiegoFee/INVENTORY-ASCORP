<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Autor: Celvin
// Descripcion: Define la estructura de la tabla clientes.
return new class extends Migration
{
    // Autor: Celvin
    // Descripcion: Ejecuta la migracion de clientes.
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nit', 20);
            $table->string('nombre');
            $table->string('direccion')->nullable();
            $table->string('telefono', 25)->nullable();
            $table->timestamps();
        });
    }

    // Autor: Celvin
    // Descripcion: Revierte la migracion de clientes.
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
