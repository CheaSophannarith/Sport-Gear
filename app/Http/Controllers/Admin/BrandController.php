<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;
use Inertia\Inertia;

class BrandController extends Controller
{
    /**
     * Display a listing of brands.
     */
    public function index()
    {
        $brands = Brand::orderBy('name')
            ->paginate(15);

        // Ensure is_active is properly cast to boolean and add media URL
        $brands->through(function ($brand) {
            $brand->is_active = (bool) $brand->is_active;
            $brand->logo_url = $brand->getFirstMediaUrl('logo');
            return $brand;
        });

        return Inertia::render('Admin/Brands/Index', [
            'brands' => $brands,
        ]);
    }

    /**
     * Show the form for creating a new brand.
     */
    public function create()
    {
        return Inertia::render('Admin/Brands/Create');
    }

    /**
     * Store a newly created brand in storage.
     */
    public function store(StoreBrandRequest $request)
    {
        $validated = $request->validated();

        // Create brand without logo field
        $brand = Brand::create($validated);

        // Handle logo upload with MediaLibrary
        if ($request->hasFile('logo')) {
            $brand->addMediaFromRequest('logo')
                ->toMediaCollection('logo');
        }

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand created successfully.');
    }

    /**
     * Display the specified brand.
     */
    public function show(Brand $brand)
    {
        $brand->logo_url = $brand->getFirstMediaUrl('logo');

        return Inertia::render('Admin/Brands/Show', [
            'brand' => $brand,
        ]);
    }

    /**
     * Show the form for editing the specified brand.
     */
    public function edit(Brand $brand)
    {
        // Ensure is_active is properly cast to boolean and add media URL
        $brand->is_active = (bool) $brand->is_active;
        $brand->logo_url = $brand->getFirstMediaUrl('logo');

        return Inertia::render('Admin/Brands/Edit', [
            'brand' => $brand,
        ]);
    }

    /**
     * Update the specified brand in storage.
     */
    public function update(UpdateBrandRequest $request, Brand $brand)
    {
        $validated = $request->validated();

        // Update brand without logo field
        $brand->update($validated);

        // Handle logo upload with MediaLibrary
        if ($request->hasFile('logo')) {
            // Clear existing media and add new one (singleFile handles this automatically)
            $brand->clearMediaCollection('logo');
            $brand->addMediaFromRequest('logo')
                ->toMediaCollection('logo');
        }

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    /**
     * Remove the specified brand from storage.
     */
    public function destroy(Brand $brand)
    {
        // MediaLibrary will automatically delete associated media
        $brand->delete();

        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand deleted successfully.');
    }
}
