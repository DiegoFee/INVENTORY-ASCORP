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
        Schema::create('compras', function (Blueprint $table) {
            $table->id()->comment('Identificador unico de la compra');
            $table->foreignId('proveedor_id')
                ->constrained('proveedores')
                ->cascadeOnUpdate()
                ->restrictOnDelete()
                ->comment('Proveedor asociado a la compra');
            $table->string('codigo', 50)->unique()->comment('Codigo unico de la orden de compra');
            $table->string('estado', 20)->default('borrador')->comment('Estado de la compra');
            $table->date('fecha_compra')->nullable()->comment('Fecha de registro de la compra');
            $table->timestamp('fecha_recepcion')->nullable()->comment('Fecha de recepcion de mercaderia');
            $table->text('observaciones')->nullable()->comment('Observaciones adicionales');
            $table->decimal('total', 12, 2)->default(0)->comment('Total de la compra');
            $table->timestamps();
            $table->softDeletes()->comment('Fecha de eliminacion suave');

            $table->index(['proveedor_id', 'estado']);
            $table->index('fecha_compra');
        });
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
