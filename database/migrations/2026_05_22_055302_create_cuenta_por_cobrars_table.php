<?php

/** Autor: Arandi Hurtado, Fecha: 21/05/2026, Descripción: Migración unificada para cuentas por cobrar asociadas a ventas y clientes. */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cuentas_por_cobrar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')
                ->constrained('ventas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->decimal('total', 12, 2);
            $table->decimal('saldo', 12, 2);
            $table->date('fecha_vencimiento')->index();
            $table->string('estado', 20)->default('pendiente')->index();
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Funcionamiento: crea la tabla cuentas_por_cobrar con relación a ventas, clientes y control de saldo fiscal.
     * Tablas: cuentas_por_cobrar, ventas, clientes.
     * Flujo: define la estructura física requerida por main y el flujo comercial.
     */

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas_por_cobrar');
    }

    /**
     * Funcionamiento: elimina la tabla cuentas_por_cobrar en rollback de forma segura.
     * Tablas: cuentas_por_cobrar.
     * Flujo: revierte la estructura física creada en el método up().
     */
};
