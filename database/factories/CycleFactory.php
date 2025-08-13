<?php

namespace Database\Factories;

use App\Models\Cycle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CycleFactory extends Factory
{
    protected $model = Cycle::class;

    public function definition(): array
    {
        $fy = (int) now()->format('Y');
        $q  = $this->faker->numberBetween(1, 4);

        // Mes de inicio del año fiscal (configurable); por defecto 1=enero
        $startMonth = (int) config('coaching.fiscal_year_start_month', 1);

        // Calcula el mes de inicio del quarter con wrap de 12 meses
        $offset     = ($q - 1) * 3;
        $startMonthQ = (($startMonth - 1 + $offset) % 12) + 1;

        // Si el mes se "rebasa" a > 12, ajusta el año
        $fyAdjust = $fy + (int) floor(($startMonth - 1 + $offset) / 12);

        $start = Carbon::create($fyAdjust, $startMonthQ, 1)->startOfDay();
        $end   = (clone $start)->addMonths(2)->endOfMonth();

        return [
            'key'         => Cycle::makeKey($fy, $q),
            'label'       => Cycle::makeLabel($fy, $q),
            'fiscal_year' => $fy,
            'quarter'     => $q,
            'starts_at'   => $start->toDateString(),
            'ends_at'     => $end->toDateString(),
            'is_open'     => now()->between($start, $end),
            'division_id' => null, // por defecto, ciclo global
        ];
    }
}
