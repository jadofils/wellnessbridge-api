<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\ProjectAssignment;

class ProjectAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        ProjectAssignment::factory()->count(100)->create();
    }
}
