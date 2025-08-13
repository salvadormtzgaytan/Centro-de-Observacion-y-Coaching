<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guide_templates', function (Blueprint $table) {
            // Evita duplicados de (channel_id, name)
            $table->unique(['channel_id', 'name'], 'guide_templates_channel_id_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('guide_templates', function (Blueprint $table) {
            $table->dropUnique('guide_templates_channel_id_name_unique');
        });
    }
};
