<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Get featured products by category slug
     *
     * @param string $slug Category slug
     * @return JsonResponse
     */
    public function featuredProducts(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$category) {
            return response()->json([
                'message' => 'Category not found or inactive',
                'data' => []
            ], 404);
        }

        $products = Product::where('category_id', $category->id)
            ->where('is_featured', true)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(
            $products->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'base_price' => $product->base_price,
                'featured_image' => $product->getFirstMediaUrl('featured_image'),
            ])
        );
    }

    /**
     * Get all products by category slug with filters
     *
     * @param string $slug Category slug
     * @return JsonResponse
     */
    public function allProducts(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$category) {
            return response()->json([
                'message' => 'Category not found or inactive',
                'data' => []
            ], 404);
        }

        $query = Product::where('category_id', $category->id)
            ->where('is_active', true);

        // Get filter parameters
        $brandId = request()->query('brand_id');
        $leagueId = request()->query('league_id');
        $surfaceTypeId = request()->query('surface_type_id');
        $teamId = request()->query('team_id');
        $name = request()->query('name');

        // Apply filters
        if ($brandId) {
            $query->where('brand_id', $brandId);
        }

        if ($leagueId) {
            $query->where('league_id', $leagueId);
        }

        if ($surfaceTypeId) {
            $query->where('surface_type_id', $surfaceTypeId);
        }

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        // Apply sorting
        $sortBy = request()->query('sort_by', 'created_at');
        $sortDir = request()->query('sort_dir', 'desc');
        $allowedSorts = ['name', 'base_price', 'created_at', 'view_count'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $products = $query->with([
            'brand:id,name,slug',
            'league:id,name,slug',
            'team:id,name,slug,league_id',
            'surfaceType:id,name,slug,code',
        ])->paginate(request()->query('per_page', 21))->withQueryString();

        return response()->json([
            'data' => $products->through(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'base_price' => $product->base_price,
                'is_featured' => $product->is_featured,
                'brand' => $product->brand,
                'league' => $product->league,
                'team' => $product->team,
                'surface_type' => $product->surfaceType,
                'featured_image' => $product->getFirstMediaUrl('featured_image'),
            ]),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function getFilters(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->with(['categoryFilters' => fn($q) => $q->orderBy('sort_order')])
            ->first();

        if (!$category) {
            return response()->json([
                'message' => 'Category not found or inactive',
                'data' => []
            ], 404);
        }

        return response()->json([
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ],
            'filters' => $category->categoryFilters->map(fn($filter) => [
                'filter_type' => $filter->filter_type,
                'is_required' => $filter->is_required,
                'sort_order' => $filter->sort_order,
            ]),
        ]);
    }

    public function details(Product $product): JsonResponse
    {
        if (!$product->is_active) {
            return response()->json([
                'message' => 'Product not found or inactive',
                'data' => []
            ], 404);
        }

        $product->load(['brand', 'league', 'team', 'surfaceType', 'variants', 'category']);

        // Get similar products (same category, different product)
        $similarProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->inRandomOrder()
            ->limit(8)
            ->get()
            ->map(fn($similarProduct) => [
                'id' => $similarProduct->id,
                'name' => $similarProduct->name,
                'slug' => $similarProduct->slug,
                'base_price' => $similarProduct->base_price,
                'featured_image' => $similarProduct->getFirstMediaUrl('featured_image'),
            ]);

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'base_price' => $product->base_price,
            'is_featured' => $product->is_featured,
            'brand' => $product->brand,
            'league' => $product->league,
            'team' => $product->team,
            'surface_type' => $product->surfaceType,
            'category' => [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ],
            'featured_image' => $product->getFirstMediaUrl('featured_image'),
            'gallery_images' => $product->getMedia('images')->map(fn($media) => [
                'url' => $media->getUrl(),
                'thumbnail_url' => $media->getUrl('thumb'),
            ]),
            'variants' => $product->variants->map(fn($variant) => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'size' => $variant->size,
                'price_adjustment' => $variant->price_adjustment,
                'stock_quantity' => $variant->stock_quantity,
                'is_active' => $variant->is_active,
                'is_available' => $variant->stock_quantity > 0 && $variant->is_active,
            ]),
            'similar_products' => $similarProducts,
        ]);
    }
}
