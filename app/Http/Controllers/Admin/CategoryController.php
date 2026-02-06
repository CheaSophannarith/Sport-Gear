<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\CategoryFilter;
use App\Models\CategoryVariantSize;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        $categories = Category::orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        // Ensure is_active is properly cast to boolean and add media URL
        $categories->through(function ($category) {
            $category->is_active = (bool) $category->is_active;
            $category->image_url = $category->getFirstMediaUrl('image');
            return $category;
        });

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {

        // dd($this->getAvailableFilters());

        return Inertia::render('Admin/Categories/Create', [
            'availableFilters' => $this->getAvailableFilters(),
        ]);
    }

    /**
     * Get available filter types
     */
    private function getAvailableFilters(): array
    {
        return [
            ['value' => 'brand', 'label' => 'Brand'],
            ['value' => 'league', 'label' => 'League'],
            ['value' => 'team', 'label' => 'Team'],
            ['value' => 'surface_type', 'label' => 'Surface Type'],
        ];
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();

        // Create category without image, filters, and variant_sizes fields
        $categoryData = collect($validated)->except(['image', 'filters', 'variant_sizes'])->toArray();
        $category = Category::create($categoryData);

        // Handle image upload with MediaLibrary
        if ($request->hasFile('image')) {
            $category->addMediaFromRequest('image')
                ->toMediaCollection('image');
        }

        // Save category filters
        if (isset($validated['filters']) && is_array($validated['filters'])) {
            foreach ($validated['filters'] as $filter) {
                CategoryFilter::create([
                    'category_id' => $category->id,
                    'filter_type' => $filter['type'],
                    'is_required' => $filter['required'] ?? false,
                    'sort_order' => $filter['sort_order'] ?? 0,
                ]);
            }
        }

        // Save variant sizes
        if (isset($validated['variant_sizes']) && is_array($validated['variant_sizes'])) {
            foreach ($validated['variant_sizes'] as $index => $size) {
                CategoryVariantSize::create([
                    'category_id' => $category->id,
                    'size_value' => $size['value'],
                    'display_label' => $size['label'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        $category->image_url = $category->getFirstMediaUrl('image');

        return Inertia::render('Admin/Categories/Show', [
            'category' => $category,
        ]);
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        // Load relationships
        $category->load(['categoryFilters', 'variantSizes']);

        // Transform to array to have full control over serialization
        $categoryData = [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'sort_order' => $category->sort_order,
            'is_active' => (bool) $category->is_active,
            'image_url' => $category->getFirstMediaUrl('image'),
            // Format filters for frontend
            'filters' => $category->categoryFilters->map(function ($filter) {
                return [
                    'type' => $filter->filter_type,
                    'required' => (bool) $filter->is_required,
                    'sort_order' => $filter->sort_order,
                ];
            })->values()->toArray(),
            // Format variant sizes for frontend
            'variant_sizes' => $category->variantSizes->map(function ($size) {
                return [
                    'value' => $size->size_value,
                    'label' => $size->display_label,
                ];
            })->values()->toArray(),
        ];

        return Inertia::render('Admin/Categories/Edit', [
            'category' => $categoryData,
            'availableFilters' => $this->getAvailableFilters(),
        ]);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $validated = $request->validated();

        // Update category without image, filters, and variant_sizes fields
        $categoryData = collect($validated)->except(['image', 'filters', 'variant_sizes'])->toArray();

        $category->update($categoryData);

        // Handle image upload with MediaLibrary
        if ($request->hasFile('image')) {
            // Clear existing media and add new one (singleFile handles this automatically)
            $category->clearMediaCollection('image');
            $category->addMediaFromRequest('image')
                ->toMediaCollection('image');
        }

        // Sync category filters
        if (isset($validated['filters'])) {
            // Delete existing filters
            $category->categoryFilters()->delete();

            // Create new filters
            if (is_array($validated['filters'])) {
                foreach ($validated['filters'] as $filter) {
                    CategoryFilter::create([
                        'category_id' => $category->id,
                        'filter_type' => $filter['type'],
                        'is_required' => $filter['required'] ?? false,
                        'sort_order' => $filter['sort_order'] ?? 0,
                    ]);
                }
            }
        }

        // Sync variant sizes
        if (isset($validated['variant_sizes'])) {
            // Delete existing sizes
            $category->variantSizes()->delete();

            // Create new sizes
            if (is_array($validated['variant_sizes'])) {
                foreach ($validated['variant_sizes'] as $index => $size) {
                    CategoryVariantSize::create([
                        'category_id' => $category->id,
                        'size_value' => $size['value'],
                        'display_label' => $size['label'],
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]);
                }
            }
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        // MediaLibrary will automatically delete associated media
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
