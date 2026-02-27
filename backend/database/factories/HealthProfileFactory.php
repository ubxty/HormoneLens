<?php

namespace Database\Factories;

use App\Enums\PhysicalActivity;
use App\Enums\StressLevel;
use App\Models\HealthProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HealthProfileFactory extends Factory
{
    protected $model = HealthProfile::class;

    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'weight'            => $this->faker->randomFloat(2, 45, 120),
            'height'            => $this->faker->randomFloat(2, 140, 200),
            'avg_sleep_hours'   => $this->faker->randomFloat(1, 4, 10),
            'stress_level'      => $this->faker->randomElement(StressLevel::cases()),
            'physical_activity' => $this->faker->randomElement(PhysicalActivity::cases()),
            'eating_habits'     => $this->faker->sentence(4),
            'water_intake'      => $this->faker->randomFloat(2, 1, 5),
            'disease_type'      => 'diabetes',
        ];
    }
}
