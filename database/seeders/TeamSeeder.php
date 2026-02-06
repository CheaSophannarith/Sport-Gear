<?php

namespace Database\Seeders;

use App\Models\League;
use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $premierLeague = League::where('slug', 'premier-league')->first();
        $laliga = League::where('slug', 'laliga')->first();
        $bundesliga = League::where('slug', 'bundesliga')->first();

        if (!$premierLeague || !$laliga || !$bundesliga) {
            $this->command->error('! Leagues not found. Please run LeagueSeeder first.');
            return;
        }

        $teams = [
            // Premier League teams
            ['name' => 'Arsenal', 'slug' => 'arsenal', 'league_id' => $premierLeague->id, 'image' => 'Arsenal.png'],
            ['name' => 'Chelsea', 'slug' => 'chelsea', 'league_id' => $premierLeague->id, 'image' => 'Chelsea.png'],
            ['name' => 'Liverpool', 'slug' => 'liverpool', 'league_id' => $premierLeague->id, 'image' => 'Liverpool.png'],
            ['name' => 'Manchester United', 'slug' => 'manchester-united', 'league_id' => $premierLeague->id, 'image' => 'Man Chester.png'],
            ['name' => 'Manchester City', 'slug' => 'manchester-city', 'league_id' => $premierLeague->id, 'image' => 'Man City.png'],

            // LaLiga teams
            ['name' => 'Atletico Madrid', 'slug' => 'atletico-madrid', 'league_id' => $laliga->id, 'image' => 'Atlectico Madrid.png'],
            ['name' => 'Barcelona', 'slug' => 'barcelona', 'league_id' => $laliga->id, 'image' => 'Barcelona.png'],
            ['name' => 'Real Betis', 'slug' => 'real-betis', 'league_id' => $laliga->id, 'image' => 'Real Betis.png'],
            ['name' => 'Real Madrid', 'slug' => 'real-madrid', 'league_id' => $laliga->id, 'image' => 'Real Madrid.png'],

            // Bundesliga teams
            ['name' => 'Bayern Munich', 'slug' => 'bayern-munich', 'league_id' => $bundesliga->id, 'image' => 'Bayern.png'],
            ['name' => 'Borussia Dortmund', 'slug' => 'borussia-dortmund', 'league_id' => $bundesliga->id, 'image' => 'BVB.png'],
            ['name' => 'Eintracht Frankfurt', 'slug' => 'eintracht-frankfurt', 'league_id' => $bundesliga->id, 'image' => 'Frankfurt.png'],
            ['name' => 'Bayer Leverkusen', 'slug' => 'bayer-leverkusen', 'league_id' => $bundesliga->id, 'image' => 'Leverkusen.png'],
        ];

        foreach ($teams as $teamData) {
            $team = Team::create([
                'name' => $teamData['name'],
                'slug' => $teamData['slug'],
                'league_id' => $teamData['league_id'],
                'is_active' => true,
            ]);

            $imagePath = database_path('seeders/images/teams/' . $teamData['image']);
            if (file_exists($imagePath)) {
                $team->addMedia($imagePath)
                    ->preservingOriginal()
                    ->toMediaCollection('logo');
            }

            $this->command->info("✓ Created team: {$teamData['name']}");
        }
    }
}
