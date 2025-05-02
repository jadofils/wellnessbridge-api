<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BirthPropertyFactory extends Factory
{
    protected $model = \App\Models\BirthProperty::class;

    public function definition(): array
    {
        return [
            'childID' => \App\Models\Child::factory(),
            'motherAge' => $this->faker->numberBetween(18, 45),
            'fatherAge' => $this->faker->numberBetween(18, 60),
            'numberOfChildren' => $this->faker->numberBetween(1, 10),
            'birthType' => $this->faker->randomElement(['Natural', 'C-Section']),
            'birthWeight' => $this->faker->randomFloat(2, 2.5, 4.5),
            'childCondition' => $this->faker->randomElement(['Healthy', 'Premature', 'Underweight']),
        ];
    }
}
