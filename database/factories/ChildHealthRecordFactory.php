<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ChildHealthRecordFactory extends Factory
{
    protected $model = \App\Models\ChildHealthRecord::class;

    public function definition(): array
    {
        return [
            'childID' => \App\Models\Child::factory(),
            'healthWorkerID' => \App\Models\HealthWorker::factory(),
            'checkupDate' => $this->faker->date(),
            'height' => $this->faker->randomFloat(2, 50, 150),
            'weight' => $this->faker->randomFloat(2, 2, 50),
            'vaccination' => $this->faker->word(),
            'diagnosis' => $this->faker->sentence(),
            'treatment' => $this->faker->paragraph(),
        ];
    }
}
