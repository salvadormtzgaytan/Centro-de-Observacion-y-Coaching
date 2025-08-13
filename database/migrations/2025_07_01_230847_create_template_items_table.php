<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_section_id')
                ->constrained('template_sections')
                ->cascadeOnDelete();
            $table->text('label');
            $table->enum('type', ['text', 'select', 'checkbox', 'scale']);
            $table->text('help_text')->nullable();
            $table->json('options')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->decimal('score', 8, 2)->default(0.00);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['template_section_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_items');
    }
};
