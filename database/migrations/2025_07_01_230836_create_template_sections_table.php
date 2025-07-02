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
        Schema::create('template_sections', function (Blueprint $table) {
            $table->id();

            // Relación con la plantilla de guía
            $table->foreignId('guide_template_id')
                  ->constrained('guide_templates')
                  ->cascadeOnDelete();

            // Título de la sección (e.g. “Introducción”, “Diagnóstico”, etc.)
            $table->string('title');

            // Orden de presentación dentro de la plantilla
            $table->unsignedInteger('order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_sections');
    }
};
