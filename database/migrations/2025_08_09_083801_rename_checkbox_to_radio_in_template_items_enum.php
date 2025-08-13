<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Actualiza datos existentes
        DB::statement("UPDATE `template_items` SET `type` = 'radio' WHERE `type` = 'checkbox'");

        // 2) Ajusta el ENUM: elimina 'checkbox', agrega 'radio'
        DB::statement("
            ALTER TABLE `template_items`
            MODIFY `type` ENUM('text','select','radio','scale')
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
        ");
    }

    public function down(): void
    {
        // Revertir: vuelve a poner 'checkbox' y quita 'radio'
        DB::statement("UPDATE `template_items` SET `type` = 'checkbox' WHERE `type` = 'radio'");

        DB::statement("
            ALTER TABLE `template_items`
            MODIFY `type` ENUM('text','select','checkbox','scale')
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
        ");
    }
};
