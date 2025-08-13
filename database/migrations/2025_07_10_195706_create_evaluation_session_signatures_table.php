<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_session_signatures', function (Blueprint $table) {
            $table->id();

            // FK a la sesión de evaluación (agrupa varias guías)
            $table->foreignId('session_id')
                ->constrained('evaluation_sessions')
                ->cascadeOnDelete();

            // FK al usuario firmante
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('signer_role');          // observer, observed, approver
            $table->timestamp('signed_at')->nullable();
            $table->text('digital_signature')->nullable();
            $table->string('method')->nullable();
            $table->enum('status', ['signed', 'rejected'])->default('signed');
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            // Única firma por sesión-usuario-rol
            $table->unique(
                ['session_id', 'user_id', 'signer_role'],
                'ess_session_user_role_unique'
            );

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_session_signatures');
    }
};
