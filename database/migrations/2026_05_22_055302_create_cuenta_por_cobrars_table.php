<?php

/** Autor: Arandi Hurtado, Fecha: 21/05/2026, Descripción: Migracion para cuentas por cobrar asociadas a ventas. */

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
        Schema::create('cuenta_por_cobrar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_venta')
                ->constrained('ventas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->decimal('monto_original', 12, 2);
            $table->decimal('saldo', 12, 2);
            $table->string('estado', 20)->index();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->index(['id_venta']);
        });
    }

    /**
     * Funcionamiento: crea la tabla cuenta_por_cobrar con relacion a ventas y campos de control de saldo.
     * Tablas: cuenta_por_cobrar, ventas.
     * Flujo: define la estructura fisica para registrar saldos por venta y su estado.
     */

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuenta_por_cobrar');
    }

    /**
     * Funcionamiento: elimina la tabla cuenta_por_cobrar en rollback.
     * Tablas: cuenta_por_cobrar.
     * Flujo: revierte la estructura creada en up() de forma segura.
     */
};
