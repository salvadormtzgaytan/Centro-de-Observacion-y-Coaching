<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cycles', function (Blueprint $table) {
            $table->id();

            // Código corto, ej: Q1, Q2, Q3, Q4
            $table->string('code');

            // Identificador legible y único, ej: FY2025-Q1
            $table->string('key')->unique();

            // Etiqueta para UI, ej: "Q1 2025" o "FY25 Q1"
            $table->string('label');

            // Año fiscal (no asumimos calendario; lo decide el negocio)
            $table->unsignedSmallInteger('fiscal_year');

            // Trimestre (1–4). Nullable por si después hay ciclos atípicos (H1/H2, bimestres, etc.)
            $table->unsignedTinyInteger('quarter')->nullable();

            // Rango de fechas del ciclo
            $table->date('starts_at');
            $table->date('ends_at');

            // Control operativo: si el ciclo está abierto para crear sesiones nuevas
            $table->boolean('is_open')->default(true);

            // Opcional: si algún día quieres ciclos por división (catálogo global si es null)
            $table->foreignId('division_id')
                ->nullable()
                ->constrained('divisions')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Evita duplicados del mismo ciclo por división (NULLs no chocan entre sí en MySQL)
            $table->unique(['fiscal_year', 'quarter', 'division_id']);

            // Búsquedas por rango
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cycles');
    }
};

