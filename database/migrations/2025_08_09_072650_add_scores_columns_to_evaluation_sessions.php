<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends \Illuminate\Database\Migrations\Migration {
    public function up(): void
    {
        Schema::table('evaluation_sessions', function (Blueprint $t) {
            // ya existe total_score; agregamos:
            $t->decimal('overall_avg', 8, 4)->nullable()->after('total_score'); // 0..1
            $t->decimal('max_score',   10, 2)->nullable()->after('overall_avg'); // suma de máximos
            $t->decimal('answered_avg',8, 4)->nullable()->after('max_score');    // 0..1 (solo respondidas)
        });
    }
    public function down(): void
    {
        Schema::table('evaluation_sessions', function (Blueprint $t) {
            $t->dropColumn(['overall_avg', 'max_score', 'answered_avg']);
        });
    }
};

