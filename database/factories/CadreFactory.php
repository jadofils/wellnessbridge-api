<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class CadreFactory extends Factory
{
    protected $model = \App\Models\Cadre::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->paragraph(),
            'qualification' => $this->faker->word(),
        ];
    }
}
