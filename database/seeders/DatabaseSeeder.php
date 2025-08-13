<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ShieldSeeder::class,
            CatalogsSeeder::class,
            RoleSeeder::class,
            AdminUserSeeder::class,
            ScaleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            CycleSeeder::class,
            // TemplateSeeder::class,
            // OtroSeeder::class,
        ]);
    }
}
