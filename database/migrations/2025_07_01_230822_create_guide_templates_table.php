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
        Schema::create('guide_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            // FKs opcionales para borrador <-> publicado
            $table->foreignId('division_id')
                ->nullable()
                ->constrained('divisions')
                ->nullOnDelete();

            $table->foreignId('level_id')
                ->nullable()
                ->constrained('levels')
                ->nullOnDelete();

            $table->foreignId('channel_id')
                ->nullable()
                ->constrained('channels')
                ->nullOnDelete();

            // Estado de la plantilla: draft o published
            $table->enum('status', ['draft', 'published'])
                ->default('draft');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guide_templates');
    }
};
