<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * // Autor: Diego Méndez - Fecha: 20/05/2026
 */
return new class extends Migration
{
    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id()->comment('Identificador unico del producto');
            $table->string('sku', 50)->unique()->comment('SKU unico del producto');
            $table->string('nombre', 255)->comment('Nombre comercial del producto');
            $table->unsignedInteger('stock_actual')->default(0)->comment('Stock disponible actual');
            $table->unsignedInteger('stock_minimo')->default(0)->comment('Stock minimo permitido');
            $table->decimal('precio_costo', 12, 2)->default(0)->comment('Precio de costo');
            $table->decimal('precio_venta', 12, 2)->default(0)->comment('Precio de venta');
            $table->string('unidad_medida', 50)->comment('Unidad de medida');
            $table->string('categoria', 100)->comment('Categoria del producto');
            $table->boolean('activo')->default(true)->comment('Producto activo');
            $table->timestamps();
            $table->softDeletes()->comment('Fecha de eliminacion suave');

            $table->index('nombre');
            $table->index('categoria');
        });
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
