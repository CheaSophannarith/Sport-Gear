<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
        ]);

        // Seed base entities (brands, leagues, teams, surface types, categories)
        $this->call([
            BrandSeeder::class,
            LeagueSeeder::class,
            TeamSeeder::class,
            SurfaceTypeSeeder::class,
            CategorySeeder::class,
            CategoryFiltersAndSizesSeeder::class,
        ]);

        // Seed products with images
        $this->call([
            ProductSeeder::class,
        ]);

        // Optionally create test users
        // User::factory(10)->create();
    }
}
