<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class HealthRestrictionFactory extends Factory
{
    protected $model = \App\Models\HealthRestriction::class;

    public function definition(): array
    {
        return [
            'recordID' => \App\Models\ChildHealthRecord::factory(),
            'description' => $this->faker->sentence(),
            'severity' => $this->faker->randomElement(['Mild', 'Moderate', 'Severe']),
        ];
    }
}
