<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_template_id')
                ->constrained('guide_templates')
                ->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('order')->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->index(['guide_template_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_sections');
    }
};
