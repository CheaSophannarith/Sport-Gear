<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Football Boots', 'slug' => 'football-boots', 'image' => 'Boots.jpg'],
            ['name' => 'Goalkeeper Gloves', 'slug' => 'goalkeeper-gloves', 'image' => 'Glvoes.jpg'],
            ['name' => 'Jerseys', 'slug' => 'jerseys', 'image' => 'Jersey.jpg'],
        ];

        foreach ($categories as $categoryData) {
            $category = Category::firstOrCreate(
                ['slug' => $categoryData['slug']],
                [
                    'name' => $categoryData['name'],
                    'is_active' => true,
                ]
            );

            $imagePath = database_path('seeders/images/categories/' . $categoryData['image']);
            if (file_exists($imagePath) && !$category->hasMedia('image')) {
                $category->addMedia($imagePath)
                    ->preservingOriginal()
                    ->toMediaCollection('image');
            }

            $this->command->info("✓ Created/Updated category: {$categoryData['name']}");
        }
    }
}
