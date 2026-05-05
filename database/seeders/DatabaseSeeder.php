<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            FirmSeeder::class,        // Firm + Admin + Staff users
            ContactSeeder::class,     // Sample contacts
            PipelineSeeder::class,    // Pipelines + Stages
            LeadSeeder::class,        // Leads linked to contacts & pipeline
            CustomFieldSeeder::class, // Custom field categories & fields
        ]);
    }
}
