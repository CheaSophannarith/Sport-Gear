<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryFilter;
use App\Models\CategoryVariantSize;
use Illuminate\Database\Seeder;

class CategoryFiltersAndSizesSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Football Boots Category
        $footballBoots = Category::where('slug', 'football_boots')
            ->orWhere('slug', 'football-boots')
            ->orWhere('name', 'LIKE', '%Football Boots%')
            ->first();

        if ($footballBoots) {
            // Clear existing filters and sizes
            $footballBoots->categoryFilters()->delete();
            $footballBoots->variantSizes()->delete();

            // Add filters
            CategoryFilter::create([
                'category_id' => $footballBoots->id,
                'filter_type' => 'brand',
                'is_required' => true,
                'sort_order' => 1,
            ]);

            CategoryFilter::create([
                'category_id' => $footballBoots->id,
                'filter_type' => 'surface_type',
                'is_required' => true,
                'sort_order' => 2,
            ]);

            // Add variant sizes (39 to 45 with half sizes)
            $bootSizes = [
                ['value' => '39', 'label' => 'EU 39'],
                ['value' => '39.5', 'label' => 'EU 39.5'],
                ['value' => '40', 'label' => 'EU 40'],
                ['value' => '40.5', 'label' => 'EU 40.5'],
                ['value' => '41', 'label' => 'EU 41'],
                ['value' => '41.5', 'label' => 'EU 41.5'],
                ['value' => '42', 'label' => 'EU 42'],
                ['value' => '42.5', 'label' => 'EU 42.5'],
                ['value' => '43', 'label' => 'EU 43'],
                ['value' => '43.5', 'label' => 'EU 43.5'],
                ['value' => '44', 'label' => 'EU 44'],
                ['value' => '44.5', 'label' => 'EU 44.5'],
                ['value' => '45', 'label' => 'EU 45'],
            ];

            foreach ($bootSizes as $index => $size) {
                CategoryVariantSize::create([
                    'category_id' => $footballBoots->id,
                    'size_value' => $size['value'],
                    'display_label' => $size['label'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }

            $this->command->info("✓ Configured Football Boots category with filters and sizes");
        } else {
            $this->command->warn("! Football Boots category not found. Skipping.");
        }

        // Jerseys Category
        $jerseys = Category::where('slug', 'jerseys')
            ->orWhere('slug', 'jersey')
            ->orWhere('name', 'LIKE', '%Jersey%')
            ->first();

        if ($jerseys) {
            // Clear existing filters and sizes
            $jerseys->categoryFilters()->delete();
            $jerseys->variantSizes()->delete();

            // Add filters
            CategoryFilter::create([
                'category_id' => $jerseys->id,
                'filter_type' => 'brand',
                'is_required' => true,
                'sort_order' => 1,
            ]);

            CategoryFilter::create([
                'category_id' => $jerseys->id,
                'filter_type' => 'league',
                'is_required' => false,
                'sort_order' => 2,
            ]);

            CategoryFilter::create([
                'category_id' => $jerseys->id,
                'filter_type' => 'team',
                'is_required' => false,
                'sort_order' => 3,
            ]);

            // Add variant sizes (Kids to XXL)
            $jerseySizes = [
                ['value' => 'Kids', 'label' => 'Kids'],
                ['value' => 'XS', 'label' => 'Extra Small (XS)'],
                ['value' => 'S', 'label' => 'Small (S)'],
                ['value' => 'M', 'label' => 'Medium (M)'],
                ['value' => 'L', 'label' => 'Large (L)'],
                ['value' => 'XL', 'label' => 'Extra Large (XL)'],
                ['value' => 'XXL', 'label' => '2XL'],
            ];

            foreach ($jerseySizes as $index => $size) {
                CategoryVariantSize::create([
                    'category_id' => $jerseys->id,
                    'size_value' => $size['value'],
                    'display_label' => $size['label'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }

            $this->command->info("✓ Configured Jerseys category with filters and sizes");
        } else {
            $this->command->warn("! Jerseys category not found. Skipping.");
        }

        // Goalkeeper Gloves Category
        $gloves = Category::where('slug', 'goalkeeper-gloves')
            ->orWhere('slug', 'gloves')
            ->orWhere('name', 'LIKE', '%Gloves%')
            ->first();

        if ($gloves) {
            // Clear existing filters and sizes
            $gloves->categoryFilters()->delete();
            $gloves->variantSizes()->delete();

            // Add filters
            CategoryFilter::create([
                'category_id' => $gloves->id,
                'filter_type' => 'brand',
                'is_required' => true,
                'sort_order' => 1,
            ]);

            // Add variant sizes (4 to 7)
            $gloveSizes = [
                ['value' => '4', 'label' => 'Size 4'],
                ['value' => '5', 'label' => 'Size 5'],
                ['value' => '6', 'label' => 'Size 6'],
                ['value' => '7', 'label' => 'Size 7'],
            ];

            foreach ($gloveSizes as $index => $size) {
                CategoryVariantSize::create([
                    'category_id' => $gloves->id,
                    'size_value' => $size['value'],
                    'display_label' => $size['label'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }

            $this->command->info("✓ Configured Goalkeeper Gloves category with filters and sizes");
        } else {
            $this->command->warn("! Goalkeeper Gloves category not found. Skipping.");
        }

        $this->command->info("\n✅ Category filters and sizes seeder completed!");
        $this->command->info("You can now edit categories in the admin panel to modify filters and sizes.");
    }
}
