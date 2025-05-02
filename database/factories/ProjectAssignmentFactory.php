<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectAssignmentFactory extends Factory
{
    protected $model = \App\Models\ProjectAssignment::class;

    public function definition(): array
    {
        return [
            'hwID' => \App\Models\HealthWorker::factory(),
            'prjID' => \App\Models\Project::factory(),
            'assignedDate' => $this->faker->date(),
            'endDate' => $this->faker->optional()->date(),
            'role' => $this->faker->randomElement(['Supervisor', 'Coordinator', 'Volunteer']),
        ];
    }
}
