<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'Adidas', 'slug' => 'adidas', 'image' => 'Adidas.png'],
            ['name' => 'Hummel', 'slug' => 'hummel', 'image' => 'Hummel.png'],
            ['name' => 'Mizuno', 'slug' => 'mizuno', 'image' => 'Mizuno.png'],
            ['name' => 'New Balance', 'slug' => 'new-balance', 'image' => 'NB.png'],
            ['name' => 'Nike', 'slug' => 'nike', 'image' => 'Nike.png'],
            ['name' => 'Puma', 'slug' => 'puma', 'image' => 'Puma.png'],
        ];

        foreach ($brands as $brandData) {
            $brand = Brand::create([
                'name' => $brandData['name'],
                'slug' => $brandData['slug'],
                'is_active' => true,
            ]);

            $imagePath = database_path('seeders/images/brands/' . $brandData['image']);
            if (file_exists($imagePath)) {
                $brand->addMedia($imagePath)
                    ->preservingOriginal()
                    ->toMediaCollection('logo');
            }

            $this->command->info("✓ Created brand: {$brandData['name']}");
        }
    }
}
