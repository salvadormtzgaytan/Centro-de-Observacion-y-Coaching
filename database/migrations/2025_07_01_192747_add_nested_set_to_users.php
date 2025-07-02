<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNestedSetToUsers extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Esto añade parent_id, _lft, _rgt y depth
            $table->nestedSet();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Para revertir: elimina las columnas que nestedSet() generó
            $table->dropColumn(['parent_id', '_lft', '_rgt', 'depth']);
        });
    }
}
