<?php

namespace Database\Seeders;

use App\Models\SurfaceType;
use Illuminate\Database\Seeder;

class SurfaceTypeSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $surfaceTypes = [
            ['name' => 'Firm Ground (FG)', 'slug' => 'firm-ground'],
            ['name' => 'Artificial Grass (AG)', 'slug' => 'artificial-grass'],
            ['name' => 'Soft Ground (SG)', 'slug' => 'soft-ground'],
            ['name' => 'Indoor Court (IC)', 'slug' => 'indoor-court'],
            ['name' => 'Turf (TF)', 'slug' => 'turf'],
        ];

        foreach ($surfaceTypes as $surfaceTypeData) {
            SurfaceType::create([
                'name' => $surfaceTypeData['name'],
                'slug' => $surfaceTypeData['slug'],
                'is_active' => true,
            ]);

            $this->command->info("✓ Created surface type: {$surfaceTypeData['name']}");
        }
    }
}
