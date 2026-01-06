<?php

namespace Database\Factories;

use App\Models\Medicament;
use App\Models\Treatment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class MedicamentFactory extends Factory
{
    protected $model = Medicament::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 week', 'now');
        $endDate = (clone $startDate)->modify('+5 days');

        return [
            'name' => $this->faker->word,
            'dosage' => $this->faker->randomElement(['5mg', '10mg', '250mg']),
            'interval_hours' => $this->faker->randomElement([4, 6, 8, 12, 24]),
            'start_date' => $startDate->format('Y-m-d H:i:s'),
            'end_date' => $endDate->format('Y-m-d'),
            'comment' => $this->faker->sentence,
            'treatment_id' => Treatment::factory(),
        ];
    }
}
