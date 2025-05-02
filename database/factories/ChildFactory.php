<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ChildFactory extends Factory
{
    protected $model = \App\Models\Child::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'gender' => $this->faker->randomElement(['Male', 'Female']),
            'dob' => $this->faker->date(),
            'image' => $this->faker->optional()->imageUrl(),
            'address' => $this->faker->address(),
            'parentName' => $this->faker->name(),
            'parentContact' => $this->faker->phoneNumber(),
        ];
    }
}
