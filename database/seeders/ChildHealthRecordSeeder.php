<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\ChildHealthRecord;

class ChildHealthRecordSeeder extends Seeder
{
    public function run(): void
    {
        ChildHealthRecord::factory()->count(30)->create();
    }
}
