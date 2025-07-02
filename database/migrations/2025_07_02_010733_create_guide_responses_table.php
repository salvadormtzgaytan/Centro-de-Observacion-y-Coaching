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
        Schema::create('guide_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_template_id')
                ->constrained('guide_templates')
                ->cascadeOnDelete();
            // Firma y evidencias van aquí:
            $table->string('evaluator_name')->nullable();
            $table->string('participant_name')->nullable();
            $table->date('date')->nullable();
            $table->string('cycle')->nullable();
            $table->decimal('total_score', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guide_responses');
    }
};
