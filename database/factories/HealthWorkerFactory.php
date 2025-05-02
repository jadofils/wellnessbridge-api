<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class HealthWorkerFactory extends Factory
{
    protected $model = \App\Models\HealthWorker::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'dob' => $this->faker->date(),
            'role' => $this->faker->jobTitle(),
            'telephone' => $this->faker->unique()->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'image' => $this->faker->imageUrl(),
            'address' => $this->faker->address(),
            'cadreID' => \App\Models\Cadre::factory(),
        ];
    }
}
