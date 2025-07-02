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
        Schema::create('guide_item_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_response_id')
                ->constrained('guide_responses')
                ->cascadeOnDelete();
            $table->foreignId('template_item_id')
                ->constrained('template_items')
                ->cascadeOnDelete();
            $table->string('value');            // e.g. 'Cumple 0.5'
            $table->decimal('score_obtained', 5, 2); // e.g. 0.5
            $table->text('observation')->nullable(); // Observaciones libres
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guide_item_responses');
    }
};
