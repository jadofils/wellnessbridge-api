<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\HealthWorker;

class HealthWorkerSeeder extends Seeder
{
    public function run(): void
    {
        HealthWorker::factory()->count(100)->create();
        
    }
}
