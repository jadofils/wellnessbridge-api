<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// use Database\Seeders\CadreHealthWorkerSeeder; // Remove this line if the class does not exist or is not needed

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
 

        // First, seed independent tables
        $this->call([
            CadreSeeder::class,
            ChildSeeder::class,
        ]);
    


        // Then, seed tables that rely on the above
        $this->call([
            HealthWorkerSeeder::class,
            BirthPropertySeeder::class,
        ]);

        // Finally, seed tables with the most dependencies
        $this->call([
            ChildHealthRecordSeeder::class,
            HealthRestrictionSeeder::class,
            ProjectSeeder::class,
            ProjectAssignmentSeeder::class,
        ]);
    }
}
