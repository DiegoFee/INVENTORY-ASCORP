<?php

/**
 * // Autor: Diego Méndez - Fecha: 26/05/2026
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * // Autor: Diego Méndez - Fecha: 26/05/2026
     */
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id()->comment('Identificador unico de la alerta');
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Producto asociado a la alerta');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete()
                ->comment('Usuario que genero la alerta');
            $table->string('tipo', 20)->comment('Tipo de alerta: bajo, critico');
            $table->string('mensaje', 500)->comment('Mensaje descriptivo de la alerta');
            $table->boolean('leida')->default(false)->comment('Indica si la alerta fue leida');
            $table->timestamps();

            $table->index(['producto_id', 'tipo']);
            $table->index('leida');
            $table->index('created_at');
        });
    }

    /**
     * // Autor: Diego Méndez - Fecha: 26/05/2026
     */
    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
