<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
return new class extends Migration
{
    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function up(): void
    {
        Schema::create('detalles_compra', function (Blueprint $table) {
            $table->id()->comment('Identificador unico del detalle');
            $table->foreignId('compra_id')
                ->constrained('compras')
                ->cascadeOnUpdate()
                ->cascadeOnDelete()
                ->comment('Compra asociada');
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Producto comprado');
            $table->unsignedInteger('cantidad')->comment('Cantidad comprada');
            $table->decimal('precio_costo', 12, 2)->comment('Precio de costo unitario');
            $table->decimal('subtotal', 12, 2)->default(0)->comment('Subtotal del detalle');
            $table->timestamps();

            $table->index(['compra_id', 'producto_id']);
        });
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function down(): void
    {
        Schema::dropIfExists('detalles_compra');
    }
};
