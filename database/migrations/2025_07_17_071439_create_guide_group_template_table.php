<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('guide_group_template', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guide_template_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_group_template');
    }
};

