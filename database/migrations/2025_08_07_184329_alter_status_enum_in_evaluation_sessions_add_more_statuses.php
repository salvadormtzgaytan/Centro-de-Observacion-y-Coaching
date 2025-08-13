<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ampliamos el ENUM para permitir: draft, completed, signed, pending, in_progress, cancelled
        DB::statement("
            ALTER TABLE evaluation_sessions
            MODIFY COLUMN status ENUM(
                'draft',
                'completed',
                'signed',
                'pending',
                'in_progress',
                'cancelled'
            ) NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        // Revertimos al estado original (solo draft, completed, signed)
        // OJO: si existen filas con valores nuevos, esta operación fallará.
        // Asegúrate de normalizarlas antes (p. ej. pasar 'pending' -> 'draft').
        DB::statement("
            ALTER TABLE evaluation_sessions
            MODIFY COLUMN status ENUM(
                'draft',
                'completed',
                'signed'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};
