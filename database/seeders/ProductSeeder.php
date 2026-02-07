<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\League;
use App\Models\Product;
use App\Models\SurfaceType;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Increase memory limit for image processing
        ini_set('memory_limit', '512M');

        // Get categories
        $bootsCategory = Category::where('slug', 'football-boots')->first();
        $glovesCategory = Category::where('slug', 'goalkeeper-gloves')->first();
        $jerseysCategory = Category::where('slug', 'jerseys')->first();

        if (!$bootsCategory || !$glovesCategory || !$jerseysCategory) {
            $this->command->error('! Categories not found. Please run CategorySeeder first.');
            return;
        }

        // Get brands
        $adidas = Brand::where('slug', 'adidas')->first();
        $nike = Brand::where('slug', 'nike')->first();
        $hummel = Brand::where('slug', 'hummel')->first();

        // Get surface types
        $firmGround = SurfaceType::where('slug', 'firm-ground')->first();
        $artificialGrass = SurfaceType::where('slug', 'artificial-grass')->first();

        // Seed Football Boots
        $this->seedBoots($bootsCategory, $adidas, $nike, $firmGround, $artificialGrass);

        // Seed Goalkeeper Gloves
        $this->seedGloves($glovesCategory, $adidas, $nike);

        // Seed Jerseys
        $this->seedJerseys($jerseysCategory, $adidas, $nike, $hummel);

        $this->command->info("\n✅ Product seeding completed!");
    }

    /**
     * Seed football boots products
     */
    private function seedBoots($category, $adidas, $nike, $firmGround, $artificialGrass): void
    {
        $boots = [
            [
                'name' => 'Adidas Copa (Immortal DNA)',
                'folder' => 'Adidas Copa ( Immotal DNA )',
                'brand' => $adidas,
                'surface_type' => $firmGround,
                'price' => 180.00,
            ],
            [
                'name' => 'Adidas F50 (Immortal DNA)',
                'folder' => 'Adidas F50 ( Immotal DNA )',
                'brand' => $adidas,
                'surface_type' => $firmGround,
                'price' => 220.00,
            ],
            [
                'name' => 'Adidas Predator 2026 (Immortal DNA)',
                'folder' => 'Adidas Predator 2026 ( Immotal DNA )',
                'brand' => $adidas,
                'surface_type' => $artificialGrass,
                'price' => 230.00,
            ],
            [
                'name' => 'Nike Phantom 6 (Scary Good)',
                'folder' => 'Nike Phantom 6 ( Scary Good )',
                'brand' => $nike,
                'surface_type' => $firmGround,
                'price' => 210.00,
            ],
            [
                'name' => 'Nike Phantom Legend 10 (Scary Good)',
                'folder' => 'Nike Phantom Legend 10 ( Scary Good )',
                'brand' => $nike,
                'surface_type' => $firmGround,
                'price' => 240.00,
            ],
            [
                'name' => 'Nike Vapor 16 (Scary Good)',
                'folder' => 'Nike Vapor 16 ( Scary Good )',
                'brand' => $nike,
                'surface_type' => $artificialGrass,
                'price' => 250.00,
            ],
        ];

        foreach ($boots as $bootData) {
            $product = Product::create([
                'category_id' => $category->id,
                'name' => $bootData['name'],
                'slug' => Str::slug($bootData['name']),
                'description' => "Experience superior performance with the {$bootData['name']}. Designed for professional players who demand excellence on the pitch.",
                'base_price' => $bootData['price'],
                'brand_id' => $bootData['brand']->id,
                'surface_type_id' => $bootData['surface_type']->id,
                'is_featured' => true,
                'is_active' => true,
            ]);

            // Add images
            $folderPath = database_path('seeders/images/products/boots/' . $bootData['folder']);

            if (is_dir($folderPath)) {
                $files = scandir($folderPath);
                $imageFiles = array_filter($files, function($file) {
                    return !in_array($file, ['.', '..']) && preg_match('/\.(jpg|jpeg|png|avif|webp)$/i', $file);
                });

                // Skip product if no images found
                if (empty($imageFiles)) {
                    $product->delete();
                    $this->command->warn("⚠ Skipped boot (no images): {$bootData['name']}");
                    continue;
                }

                // Sort files to ensure 1.jpg is first
                usort($imageFiles, function($a, $b) {
                    return strcmp($a, $b);
                });

                $isFirst = true;
                foreach ($imageFiles as $imageFile) {
                    $imagePath = $folderPath . '/' . $imageFile;

                    if ($isFirst) {
                        // First image (1.jpg) as featured
                        $product->addMedia($imagePath)
                            ->preservingOriginal()
                            ->toMediaCollection('featured_image');
                        $isFirst = false;
                    } else {
                        // Rest as additional images
                        $product->addMedia($imagePath)
                            ->preservingOriginal()
                            ->toMediaCollection('images');
                    }
                }
            }

            $this->command->info("✓ Created boot: {$bootData['name']}");
        }
    }

    /**
     * Seed goalkeeper gloves products
     */
    private function seedGloves($category, $adidas, $nike): void
    {
        $gloves = [
            [
                'name' => 'Adidas Glove (Predator 2026)',
                'folder' => 'Adidas Clove ( Predetor 2026 )',
                'brand' => $adidas,
                'price' => 90.00,
            ],
            [
                'name' => 'Adidas Gloves (Lucid Red)',
                'folder' => 'Adidas Cloves ( Lucide Red )',
                'brand' => $adidas,
                'price' => 85.00,
            ],
            [
                'name' => 'Nike Gloves (Scary Good)',
                'folder' => 'Nike Gloves ( Scary Good )',
                'brand' => $nike,
                'price' => 95.00,
            ],
            [
                'name' => 'Nike Gloves (Volt Victory)',
                'folder' => 'Nike Gloves ( Volt Victory )',
                'brand' => $nike,
                'price' => 100.00,
            ],
        ];

        foreach ($gloves as $gloveData) {
            $product = Product::create([
                'category_id' => $category->id,
                'name' => $gloveData['name'],
                'slug' => Str::slug($gloveData['name']),
                'description' => "Superior grip and protection with the {$gloveData['name']}. Perfect for goalkeepers who demand the best.",
                'base_price' => $gloveData['price'],
                'brand_id' => $gloveData['brand']->id,
                'is_featured' => false,
                'is_active' => true,
            ]);

            // Add images
            $folderPath = database_path('seeders/images/products/gloves/' . $gloveData['folder']);

            if (is_dir($folderPath)) {
                $files = scandir($folderPath);
                $imageFiles = array_filter($files, function($file) {
                    return !in_array($file, ['.', '..']) && preg_match('/\.(jpg|jpeg|png|avif|webp)$/i', $file);
                });

                // Skip product if no images found
                if (empty($imageFiles)) {
                    $product->delete();
                    $this->command->warn("⚠ Skipped glove (no images): {$gloveData['name']}");
                    continue;
                }

                // Sort files to ensure 1.jpg/1.png is first
                usort($imageFiles, function($a, $b) {
                    return strcmp($a, $b);
                });

                $isFirst = true;
                foreach ($imageFiles as $imageFile) {
                    $imagePath = $folderPath . '/' . $imageFile;

                    if ($isFirst) {
                        // First image (1.jpg/1.png) as featured
                        $product->addMedia($imagePath)
                            ->preservingOriginal()
                            ->toMediaCollection('featured_image');
                        $isFirst = false;
                    } else {
                        // Rest as additional images
                        $product->addMedia($imagePath)
                            ->preservingOriginal()
                            ->toMediaCollection('images');
                    }
                }
            }

            $this->command->info("✓ Created glove: {$gloveData['name']}");
        }
    }

    /**
     * Seed jersey products
     */
    private function seedJerseys($category, $adidas, $nike, $hummel): void
    {
        // Get leagues
        $premierLeague = League::where('slug', 'premier-league')->first();
        $laliga = League::where('slug', 'laliga')->first();
        $bundesliga = League::where('slug', 'bundesliga')->first();

        $jerseys = [
            [
                'team_slug' => 'arsenal',
                'folder' => 'Arsenal',
                'brand' => $adidas,
                'league' => $premierLeague,
                'price' => 85.00,
            ],
            [
                'team_slug' => 'atletico-madrid',
                'folder' => 'Atlectico Madrid',
                'brand' => $nike,
                'league' => $laliga,
                'price' => 90.00,
            ],
            [
                'team_slug' => 'barcelona',
                'folder' => 'Barcelona',
                'brand' => $nike,
                'league' => $laliga,
                'price' => 95.00,
            ],
            [
                'team_slug' => 'chelsea',
                'folder' => 'Chelsea',
                'brand' => $nike,
                'league' => $premierLeague,
                'price' => 85.00,
            ],
            [
                'team_slug' => 'liverpool',
                'folder' => 'Liverpool',
                'brand' => $adidas,
                'league' => $premierLeague,
                'price' => 90.00,
            ],
            [
                'team_slug' => 'manchester-united',
                'folder' => 'Man Chester',
                'brand' => $adidas,
                'league' => $premierLeague,
                'price' => 90.00,
            ],
            [
                'team_slug' => 'manchester-city',
                'folder' => 'Man City',
                'brand' => $adidas,
                'league' => $premierLeague,
                'price' => 95.00,
            ],
            [
                'team_slug' => 'real-betis',
                'folder' => 'Real Betis',
                'brand' => $hummel, // Real Betis uses Hummel
                'league' => $laliga,
                'price' => 80.00,
            ],
            [
                'team_slug' => 'real-madrid',
                'folder' => 'Real Madrid',
                'brand' => $adidas,
                'league' => $laliga,
                'price' => 95.00,
            ],
            [
                'team_slug' => 'eintracht-frankfurt',
                'folder' => 'The Red EFT',
                'brand' => $nike,
                'league' => $bundesliga,
                'price' => 999.00,
            ]
        ];

        foreach ($jerseys as $jerseyData) {
            $team = Team::where('slug', $jerseyData['team_slug'])->first();

            if (!$team) {
                $this->command->warn("! Team not found: {$jerseyData['team_slug']}");
                continue;
            }

            // Add "(2025-2026 Home Kit)" suffix to jersey name
            $productName = $team->name . ' (2025-2026 Home Kit)';

            $product = Product::create([
                'category_id' => $category->id,
                'name' => $productName,
                'slug' => Str::slug($productName),
                'description' => "Official {$team->name} home jersey for the 2025-2026 season. Show your support with authentic team colors and crest.",
                'base_price' => $jerseyData['price'],
                'brand_id' => $jerseyData['brand']->id,
                'league_id' => $jerseyData['league']->id,
                'team_id' => $team->id,
                'is_featured' => in_array($jerseyData['team_slug'], ['manchester-united', 'barcelona', 'real-madrid']),
                'is_active' => true,
            ]);

            // Add images (front.jpg as featured, back as additional)
            $folderPath = database_path('seeders/images/products/jersey/' . $jerseyData['folder']);

            if (is_dir($folderPath)) {
                // Add front image as featured
                $frontImage = null;
                if (file_exists($folderPath . '/front.jpg')) {
                    $frontImage = $folderPath . '/front.jpg';
                } elseif (file_exists($folderPath . '/front.png')) {
                    $frontImage = $folderPath . '/front.png';
                }

                // Skip product if no front image found
                if (!$frontImage) {
                    $product->delete();
                    $this->command->warn("⚠ Skipped jersey (no front image): {$productName}");
                    continue;
                }

                $product->addMedia($frontImage)
                    ->preservingOriginal()
                    ->toMediaCollection('featured_image');

                // Add back image as additional
                $backImage = null;
                if (file_exists($folderPath . '/back.jpg')) {
                    $backImage = $folderPath . '/back.jpg';
                } elseif (file_exists($folderPath . '/back.png')) {
                    $backImage = $folderPath . '/back.png';
                } elseif (file_exists($folderPath . '/back.jfif')) {
                    $backImage = $folderPath . '/back.jfif';
                }

                if ($backImage) {
                    $product->addMedia($backImage)
                        ->preservingOriginal()
                        ->toMediaCollection('images');
                }
            }

            $this->command->info("✓ Created jersey: {$productName}");
        }
    }
}
