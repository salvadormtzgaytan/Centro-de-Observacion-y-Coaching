<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_sessions', function (Blueprint $table) {
            $table->id();

            // Quién evalúa y quién es evaluado
            $table->foreignId('evaluator_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('participant_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Datos de la sesión
            $table->date('date')->nullable();
            $table->string('cycle', 50)->nullable();

            // PDF global de la sesión
            $table->string('pdf_path')->nullable();

            // Total acumulado de todas las guías de la sesión
            $table->decimal('total_score', 8, 2)->default(0.00);

            $table->softDeletes();
            $table->timestamps();

            // Índice para búsquedas frecuentes
            $table->index(['participant_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_sessions');
    }
};
