<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\BirthProperty;

class BirthPropertySeeder extends Seeder
{
    public function run(): void
    {
        BirthProperty::factory()->count(30)->create();
    }
}
