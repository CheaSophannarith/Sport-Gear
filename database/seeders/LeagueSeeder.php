<?php

namespace Database\Seeders;

use App\Models\League;
use Illuminate\Database\Seeder;

class LeagueSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $leagues = [
            ['name' => 'Premier League', 'slug' => 'premier-league', 'image' => 'Premier-League.png'],
            ['name' => 'LaLiga', 'slug' => 'laliga', 'image' => 'LaLiga.png'],
            ['name' => 'Bundesliga', 'slug' => 'bundesliga', 'image' => 'Bundesliga.png'],
        ];

        foreach ($leagues as $leagueData) {
            $league = League::create([
                'name' => $leagueData['name'],
                'slug' => $leagueData['slug'],
                'is_active' => true,
            ]);

            $imagePath = database_path('seeders/images/leagues/' . $leagueData['image']);
            if (file_exists($imagePath)) {
                $league->addMedia($imagePath)
                    ->preservingOriginal()
                    ->toMediaCollection('logo');
            }

            $this->command->info("✓ Created league: {$leagueData['name']}");
        }
    }
}
