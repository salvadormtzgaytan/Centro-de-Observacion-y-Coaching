<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Scale;

class ScaleSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['label' => 'Reforzar', 'value' => 0.00, 'order' => 1],
            ['label' => 'Cumple',    'value' => 0.50, 'order' => 2],
            ['label' => 'Excede',    'value' => 1.00, 'order' => 3],
        ];

        foreach ($defaults as $scale) {
            Scale::updateOrCreate(
                ['label' => $scale['label']],
                ['value' => $scale['value'], 'order' => $scale['order']]
            );
        }
    }
}
