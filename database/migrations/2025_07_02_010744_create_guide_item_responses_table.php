<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guide_item_responses', function (Blueprint $table) {
            $table->id();

            // Cuando se borra una respuesta de guía, sus respuestas de ítem se eliminan en cascada
            $table->foreignId('guide_response_id')
                ->constrained('guide_responses')
                ->cascadeOnDelete();

            // Si existe alguna respuesta para este ítem, no se permite borrar el ítem
            $table->foreignId('template_item_id')
                ->constrained('template_items')
                ->restrictOnDelete();

            $table->json('answer')->nullable();
            $table->decimal('score_obtained', 8, 2)->default(0.00);
            $table->timestamps();

            $table->index(['guide_response_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_item_responses');
    }
};
