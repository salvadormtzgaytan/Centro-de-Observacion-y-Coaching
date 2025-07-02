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
        Schema::create('template_items', function (Blueprint $table) {
            $table->id();

            // Relación con la sección de la plantilla
            $table->foreignId('template_section_id')
                  ->constrained('template_sections')
                  ->cascadeOnDelete();

            // Texto de la pregunta o criterio
            $table->string('label');

            // Tipo de campo: texto libre, selección, casilla, escala
            $table->enum('type', ['text','select','checkbox','scale']);

            // Texto de ayuda contextual
            $table->text('help_text')->nullable();

            // Opciones para select o valores de escala (JSON)
            $table->json('options')->nullable();

            // Orden dentro de la sección
            $table->unsignedInteger('order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_items');
    }
};
