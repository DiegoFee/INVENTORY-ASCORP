<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * // Autor: Diego Méndez - Fecha: 27/05/2026
     */
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id()->comment('Identificador unico de la alerta');
            $table->string('tipo', 20)->comment('Tipo de alerta: stock_bajo, critico');
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete()->comment('Producto relacionado (opcional)');
            $table->text('mensaje')->comment('Mensaje descriptivo de la alerta');
            $table->boolean('leida')->default(false)->comment('Indica si la alerta fue leida');
            $table->timestamps();
            $table->softDeletes()->comment('Fecha de eliminacion suave');
            $table->index('tipo');
            $table->index('leida');
        });
    }

    /**
     * // Autor: Diego Méndez - Fecha: 27/05/2026
     */
    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
