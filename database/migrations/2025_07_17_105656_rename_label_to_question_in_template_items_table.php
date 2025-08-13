<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('template_items', function (Blueprint $table) {
            $table->renameColumn('label', 'question');
        });
    }

    public function down(): void
    {
        Schema::table('template_items', function (Blueprint $table) {
            $table->renameColumn('question', 'label');
        });
    }
};
