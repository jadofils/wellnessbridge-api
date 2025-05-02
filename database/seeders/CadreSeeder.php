<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Cadre;

class CadreSeeder extends Seeder
{
    public function run(): void
    {
        Cadre::factory()->count(20)->create();
    }
}
