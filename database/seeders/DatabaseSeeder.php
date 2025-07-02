<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatalogsSeeder::class,
            RoleSeeder::class,
            AdminUserSeeder::class,
            ScaleSeeder::class,
            // TemplateSeeder::class,
            // OtroSeeder::class,
        ]);
    }
}
