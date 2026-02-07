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
}
