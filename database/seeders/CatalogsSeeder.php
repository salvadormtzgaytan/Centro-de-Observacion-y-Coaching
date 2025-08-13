<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;
use App\Models\Level;
use App\Models\Channel;

class CatalogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Divisiones
        Division::insert([
            ['key' => 'dermatologia',       'name' => 'Dermatología',       'created_at' => now(), 'updated_at' => now()],
            ['key' => 'cardiometabolismo',  'name' => 'Cardiometabolismo',  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'neurociencias',      'name' => 'Neurociencias',      'created_at' => now(), 'updated_at' => now()],
            ['key' => 'infectologia',       'name' => 'Infectología',       'created_at' => now(), 'updated_at' => now()],
            ['key' => 'ginecologia',        'name' => 'Ginecología',        'created_at' => now(), 'updated_at' => now()],
            ['key' => 'traumatologia',      'name' => 'Traumatología',      'created_at' => now(), 'updated_at' => now()],
            ['key' => 'otc',                'name' => 'Medicamentos De Venta Libre (OTC)', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Niveles
        Level::insert([
            ['key' => 'basico',     'name' => 'Básico',     'created_at' => now(), 'updated_at' => now()],
            ['key' => 'intermedio', 'name' => 'Intermedio', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'avanzado',   'name' => 'Avanzado',   'created_at' => now(), 'updated_at' => now()],
            ['key' => 'general',   'name' => 'General',   'created_at' => now(), 'updated_at' => now()],
        ]);

        // Canales
        Channel::insert([
            ['key' => 'consultorio', 'name' => 'Consultorio',    'created_at' => now(), 'updated_at' => now()],
            ['key' => 'punto_venta', 'name' => 'Punto de Venta', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'farmacia', 'name' => 'Farmacia', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
