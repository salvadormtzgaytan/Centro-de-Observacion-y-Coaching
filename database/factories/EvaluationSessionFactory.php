<?php

namespace Database\Factories;

use App\Models\EvaluationSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvaluationSessionFactory extends Factory
{
    protected $model = EvaluationSession::class;

    public function definition(): array
    {
        return [
            'evaluator_id'   => User::factory(), // Relación con usuario evaluador
            'participant_id' => User::factory(), // Relación con usuario participante
            'date'           => $this->faker->date(),
            'cycle'          => $this->faker->randomElement(['2024-01', '2024-02', '2024-03']),
            'status'         => 'draft', // O 'completed' si necesitas
            'comments'       => $this->faker->sentence(),
            'total_score'    => $this->faker->randomFloat(2, 60, 100),
            'division_id'    => null, // O ajusta si tienes Division::factory()
        ];
    }
}
