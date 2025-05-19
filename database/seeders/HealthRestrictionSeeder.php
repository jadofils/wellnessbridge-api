<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\HealthRestriction;

class HealthRestrictionSeeder extends Seeder
{
    public function run(): void
    {
        HealthRestriction::factory()->count(100)->create();
    }
}
