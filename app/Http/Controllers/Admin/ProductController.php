<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\League;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SurfaceType;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index()
    {
        $products = Product::with(['category', 'variants'])
            ->withCount('variants')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Add additional data for each product
        $products->through(function ($product) {
            $product->is_active = (bool) $product->is_active;
            $product->is_featured = (bool) $product->is_featured;
            $product->featured_image_url = $product->getFirstMediaUrl('featured_image');
            $product->total_stock = $product->variants->sum('stock_quantity');
            return $product;
        });

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Products/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

        // dd($validated);

        DB::beginTransaction();
        try {
            // Create product (foreign keys are now included in fillable)
            $productData = collect($validated)->except([
                'featured_image',
                'images',
                'variants'
            ])->toArray();

            $product = Product::create($productData);

            // Handle featured image
            if ($request->hasFile('featured_image')) {
                $product->addMediaFromRequest('featured_image')
                    ->toMediaCollection('featured_image');
            }

            // Handle additional images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $product->addMedia($image)
                        ->toMediaCollection('images');
                }
            }

            // Create product variants
            if (isset($validated['variants']) && is_array($validated['variants'])) {
                foreach ($validated['variants'] as $variant) {
                    $product->variants()->create([
                        'sku' => $variant['sku'] ?? null,
                        'size' => $variant['size'],
                        'price_adjustment' => $variant['price_adjustment'] ?? 0,
                        'stock_quantity' => $variant['stock_quantity'] ?? 0,
                        'low_stock_threshold' => $variant['low_stock_threshold'] ?? 5,
                        'is_active' => $variant['is_active'] ?? true,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create product: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        $product->load([
            'category',
            'variants',
            'brand',
            'league',
            'team',
            'surfaceType'
        ]);

        // Get categories
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Add media URLs
        $product->featured_image_url = $product->getFirstMediaUrl('featured_image');
        $product->images_urls = $product->getMedia('images')->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'name' => $media->file_name,
            ];
        });

        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Update product (foreign keys are now included in fillable)
            $productData = collect($validated)->except([
                'featured_image',
                'images',
                'variants',
                'delete_images'
            ])->toArray();

            $product->update($productData);

            // Handle featured image
            if ($request->hasFile('featured_image')) {
                $product->clearMediaCollection('featured_image');
                $product->addMediaFromRequest('featured_image')
                    ->toMediaCollection('featured_image');
            }

            // Handle additional images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $product->addMedia($image)
                        ->toMediaCollection('images');
                }
            }

            // Delete specified images
            if (isset($validated['delete_images']) && is_array($validated['delete_images'])) {
                foreach ($validated['delete_images'] as $mediaId) {
                    $product->deleteMedia($mediaId);
                }
            }

            // Update or create variants
            if (isset($validated['variants']) && is_array($validated['variants'])) {
                // Get existing variant IDs
                $existingVariantIds = $product->variants->pluck('id')->toArray();
                $updatedVariantIds = [];

                foreach ($validated['variants'] as $variantData) {
                    if (isset($variantData['id']) && in_array($variantData['id'], $existingVariantIds)) {
                        // Update existing variant
                        $variant = ProductVariant::find($variantData['id']);
                        $variant->update([
                            'sku' => $variantData['sku'] ?? null,
                            'size' => $variantData['size'],
                            'price_adjustment' => $variantData['price_adjustment'] ?? 0,
                            'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                            'low_stock_threshold' => $variantData['low_stock_threshold'] ?? 5,
                            'is_active' => $variantData['is_active'] ?? true,
                        ]);
                        $updatedVariantIds[] = $variant->id;
                    } else {
                        // Create new variant
                        $variant = $product->variants()->create([
                            'sku' => $variantData['sku'] ?? null,
                            'size' => $variantData['size'],
                            'price_adjustment' => $variantData['price_adjustment'] ?? 0,
                            'stock_quantity' => $variantData['stock_quantity'] ?? 0,
                            'low_stock_threshold' => $variantData['low_stock_threshold'] ?? 5,
                            'is_active' => $variantData['is_active'] ?? true,
                        ]);
                        $updatedVariantIds[] = $variant->id;
                    }
                }

                // Delete variants that were removed
                $variantsToDelete = array_diff($existingVariantIds, $updatedVariantIds);
                ProductVariant::whereIn('id', $variantsToDelete)->delete();
            }

            DB::commit();

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();

            return redirect()
                ->route('admin.products.index')
                ->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to delete product: ' . $e->getMessage()]);
        }
    }
}
