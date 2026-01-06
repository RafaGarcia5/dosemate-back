<?php

namespace Database\Factories;

use App\Models\Treatment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TreatmentFactory extends Factory
{
    protected $model = Treatment::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', 'now');
        $endDate = $this->faker->dateTimeBetween($startDate, '+1 month');

        return [
            'patient_id' => User::factory()->create(['role' => 'patient'])->id,
            'name' => $this->faker->sentence(2),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'comment' => $this->faker->sentence,
        ];
    }
}
