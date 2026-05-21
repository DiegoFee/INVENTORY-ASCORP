<?php

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
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id_open')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->timestamp('opened_at');
            $table->decimal('saldo_apertura', 12, 2);
            $table->foreignId('user_id_close')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->decimal('saldo_cierre', 12, 2)->nullable();
            $table->string('status', 20);
            $table->softDeletes();
            $table->timestamps();
            $table->index(
                ['user_id_open', 'status'],
                'idx_cajas_usuario_estado'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
