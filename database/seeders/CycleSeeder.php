<?php

namespace Database\Seeders;

use App\Models\Cycle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CycleSeeder extends Seeder
{
    public function run(): void
    {
        $fy         = (int) config('coaching.default_fiscal_year', now()->year);
        $startMonth = (int) config('coaching.fiscal_year_start_month', 1);

        // Genera Q1..Q4 del año fiscal $fy (globales: division_id = null)
        for ($q = 1; $q <= 4; $q++) {
            // Desplazamiento en meses desde el inicio del año fiscal
            $offset = ($q - 1) * 3;

            // Mes calendario (1..12) para el arranque del quarter
            $startMonthQ = (($startMonth - 1 + $offset) % 12) + 1;

            // Año calendario real (si el FY no inicia en enero puede brincar a FY+1)
            $startYear = $fy + (int) floor(($startMonth - 1 + $offset) / 12);

            // Fechas
            $startsAt = Carbon::create($startYear, $startMonthQ, 1)->startOfDay();
            $endsAt   = (clone $startsAt)->addMonthsNoOverflow(2)->endOfMonth();

            Cycle::updateOrCreate(
                [
                    'fiscal_year' => $fy,
                    'quarter'     => $q,
                    'division_id' => null, // global
                ],
                [
                    'code'      => 'Q'.$q,                   // Q1|Q2|Q3|Q4
                    'key'       => Cycle::makeKey($fy, $q),  // p.ej. "FY2025-Q1"
                    'label'     => Cycle::makeLabel($fy, $q),// p.ej. "Q1 2025"
                    'starts_at' => $startsAt->toDateString(),
                    'ends_at'   => $endsAt->toDateString(),
                    'is_open'   => now()->between($startsAt, $endsAt),
                ]
            );
        }
    }
}
