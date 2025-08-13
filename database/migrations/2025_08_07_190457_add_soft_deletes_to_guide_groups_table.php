<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('guide_groups', 'deleted_at')) {
            Schema::table('guide_groups', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at'); // crea la columna deleted_at
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('guide_groups', 'deleted_at')) {
            Schema::table('guide_groups', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};

