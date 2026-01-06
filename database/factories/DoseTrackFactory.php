<?php

namespace Database\Factories;

use App\Models\DoseTrack;
use App\Models\Medicament;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoseTrackFactory extends Factory
{
    protected $model = DoseTrack::class;

    public function definition(): array
    {
        return [
            'medicament_id' => Medicament::factory(),
            'schedule' => $this->faker->dateTimeBetween('now', '+3 days')->format('Y-m-d H:i:s'),
            'taken_dose' => false,
            'taken_time' => null,
        ];
    }
}
