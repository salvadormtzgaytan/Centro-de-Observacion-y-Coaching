<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
{
    Schema::table('evaluation_sessions', function (Blueprint $table) {
        $table->enum('status', ['draft', 'completed', 'signed'])
              ->default('draft')
              ->after('cycle');
    });
}

public function down(): void
{
    Schema::table('evaluation_sessions', function (Blueprint $table) {
        $table->dropColumn('status');
    });
}
};
