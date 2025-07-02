<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@tu-dominio.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('secret1234#'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('super_admin');
    }
}
