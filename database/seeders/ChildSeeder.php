<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Child;

class ChildSeeder extends Seeder
{
    public function run(): void
    {
        Child::factory()->count(50)->create();
    }
}
