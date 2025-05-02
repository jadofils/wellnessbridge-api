<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = \App\Models\Project::class;

    public function definition(): array
    {
        return [
            'cadID' => \App\Models\Cadre::factory(),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'startDate' => $this->faker->date(),
            'endDate' => $this->faker->optional()->date(),
            'status' => $this->faker->randomElement(['Pending', 'Ongoing', 'Completed']),
        ];
    }
}
