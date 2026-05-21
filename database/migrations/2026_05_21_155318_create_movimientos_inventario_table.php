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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id()->comment('Identificador unico del movimiento');
            $table->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Producto asociado');
            $table->foreignId('compra_id')
                ->nullable()
                ->constrained('compras')
                ->cascadeOnUpdate()
                ->nullOnDelete()
                ->comment('Compra origen si aplica');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete()
                ->comment('Usuario responsable');
            $table->string('tipo', 20)->comment('Tipo de movimiento');
            $table->string('origen', 30)->comment('Origen del movimiento');
            $table->unsignedInteger('cantidad')->comment('Cantidad movida');
            $table->unsignedInteger('stock_anterior')->default(0)->comment('Stock anterior');
            $table->unsignedInteger('stock_nuevo')->default(0)->comment('Stock nuevo');
            $table->decimal('costo_unitario', 12, 2)->nullable()->comment('Costo unitario aplicado');
            $table->text('observaciones')->nullable()->comment('Observaciones adicionales');
            $table->timestamps();

            $table->index(['producto_id', 'tipo']);
            $table->index('created_at');
        });
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
