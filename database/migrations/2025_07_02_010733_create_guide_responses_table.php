<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guide_responses', function (Blueprint $table) {
            $table->id();

            // → Vinculación a la sesión de evaluación que agrupa varias guías
            $table->foreignId('session_id')
                ->constrained('evaluation_sessions')
                ->cascadeOnDelete();

            // → Plantilla concreta que se está evaluando
            $table->foreignId('guide_template_id')
                ->constrained('guide_templates')
                ->restrictOnDelete();

            // Total de puntaje para ESTA guía dentro de la sesión
            $table->decimal('total_score', 8, 2)->default(0.00);

            // Su propio borrado lógico
            $table->softDeletes();
            $table->timestamps();

            // Índice para búsquedas por sesión y plantilla
            $table->index(['session_id', 'guide_template_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_responses');
    }
};
