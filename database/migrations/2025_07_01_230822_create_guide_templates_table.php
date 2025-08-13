<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guide_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('division_id')
                ->constrained('divisions')
                ->restrictOnDelete();
            $table->foreignId('level_id')
                ->constrained('levels')
                ->restrictOnDelete();
            $table->foreignId('channel_id')
                ->constrained('channels')
                ->restrictOnDelete();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['division_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_templates');
    }
};
