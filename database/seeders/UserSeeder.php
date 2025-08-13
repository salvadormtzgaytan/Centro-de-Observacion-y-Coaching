<?php

// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles
        $coachRole = Role::firstOrCreate(['name' => 'coach']);
        $coacheeRole = Role::firstOrCreate(['name' => 'coachee']);

        // ===== SOLO 3 COACHES =====
        // 1. Goku - Coach principal
        $goku = User::firstOrCreate(
            ['email' => 'goku@coachingball.com'],
            [
                'name' => 'Son Goku',
                'password' => 'kamehameha',
                'email_verified_at' => now(),
            ]
        )->assignRole($coachRole);

        // 2. Piccolo - Coach estratégico
        $piccolo = User::firstOrCreate(
            ['email' => 'piccolo@coachingball.com'],
            [
                'name' => 'Piccolo',
                'password' => 'makankosappo',
                'email_verified_at' => now(),
            ]
        )->assignRole($coachRole);

        // 3. Whis - Coach divino
        $whis = User::firstOrCreate(
            ['email' => 'whis@coachingball.com'],
            [
                'name' => 'Whis',
                'password' => 'temporaldo',
                'email_verified_at' => now(),
            ]
        )->assignRole($coachRole);

        // ===== 12 COACHEES (ASIGNADOS A 1 DE LOS 3 COACHES) =====
        $coachees = [
            // Coachees de Goku
            [
                'name' => 'Son Gohan',
                'email' => 'gohan@coachingball.com',
                'password' => 'masenko',
                'coach' => $goku
            ],
            [
                'name' => 'Vegeta',
                'email' => 'vegeta@coachingball.com',
                'password' => 'principe-saiyajin',
                'coach' => $goku
            ],
            [
                'name' => 'Trunks',
                'email' => 'trunks@coachingball.com',
                'password' => 'espada-del-futuro',
                'coach' => $goku
            ],
            [
                'name' => 'Son Goten',
                'email' => 'goten@coachingball.com',
                'password' => 'fusion',
                'coach' => $goku
            ],
            
            // Coachees de Piccolo
            [
                'name' => 'Krillin',
                'email' => 'krillin@coachingball.com',
                'password' => 'kienzan',
                'coach' => $piccolo
            ],
            [
                'name' => 'Android 18',
                'email' => 'android18@coachingball.com',
                'password' => 'infinito',
                'coach' => $piccolo
            ],
            [
                'name' => 'Tien Shinhan',
                'email' => 'tien@coachingball.com',
                'password' => 'tribu-oculta',
                'coach' => $piccolo
            ],
            [
                'name' => 'Yamcha',
                'email' => 'yamcha@coachingball.com',
                'password' => 'wolf-fang',
                'coach' => $piccolo
            ],
            
            // Coachees de Whis
            [
                'name' => 'Majin Buu',
                'email' => 'buu@coachingball.com',
                'password' => 'chocolate',
                'coach' => $whis
            ],
            [
                'name' => 'Caulifla',
                'email' => 'caulifla@coachingball.com',
                'password' => 'saiyajin-legendario',
                'coach' => $whis
            ],
            [
                'name' => 'Kale',
                'email' => 'kale@coachingball.com',
                'password' => 'berserker',
                'coach' => $whis
            ],
            [
                'name' => 'Master Roshi',
                'email' => 'roshi@coachingball.com',
                'password' => 'kame-house',
                'coach' => $whis
            ]
        ];

        foreach ($coachees as $coachee) {
            User::firstOrCreate(
                ['email' => $coachee['email']],
                [
                    'name' => $coachee['name'],
                    'password' => $coachee['password'],
                    'parent_id' => $coachee['coach']->id,
                    'email_verified_at' => now(),
                ]
            )->assignRole($coacheeRole);
        }
    }
}
