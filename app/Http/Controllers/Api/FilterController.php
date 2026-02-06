<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\League;
use App\Models\SurfaceType;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    /**
     * Get teams filtered by league ID(s)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTeams(Request $request): JsonResponse
    {
        $query = Team::query()->where('is_active', true);

        // Filter by league_id if provided
        if ($request->has('league_id')) {
            $leagueIds = is_array($request->league_id)
                ? $request->league_id
                : [$request->league_id];

            $query->whereIn('league_id', $leagueIds);
        }

        $teams = $query->orderBy('name')->get();

        return response()->json($teams->map(fn($team) => [
            'id' => $team->id,
            'name' => $team->name,
            'league_id' => $team->league_id,
            'logo_url' => $team->getFirstMediaUrl('logo'),
        ]));
    }

    /**
     * Get category details including filters and variant sizes
     *
     * @param Category $category
     * @return JsonResponse
     */
    public function getCategoryDetails(Category $category): JsonResponse
    {
        $category->load(['categoryFilters', 'variantSizes']);

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
            'filters' => $category->categoryFilters->map(fn($filter) => [
                'type' => $filter->filter_type,
                'required' => $filter->is_required,
                'sort_order' => $filter->sort_order,
            ]),
            'variant_sizes' => $category->variantSizes->map(fn($size) => [
                'value' => $size->size_value,
                'label' => $size->display_label,
                'sort_order' => $size->sort_order,
            ]),
        ]);
    }

    /**
     * Get all active brands
     *
     * @return JsonResponse
     */
    public function getBrands(): JsonResponse
    {
        $brands = Brand::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json($brands->map(fn($brand) => [
            'id' => $brand->id,
            'name' => $brand->name,
            'logo_url' => $brand->getFirstMediaUrl('logo'),
        ]));
    }

    /**
     * Get all active leagues
     *
     * @return JsonResponse
     */
    public function getLeagues(): JsonResponse
    {
        $leagues = League::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json($leagues->map(fn($league) => [
            'id' => $league->id,
            'name' => $league->name,
            'logo_url' => $league->getFirstMediaUrl('logo'),
        ]));
    }

    /**
     * Get all active surface types
     *
     * @return JsonResponse
     */
    public function getSurfaceTypes(): JsonResponse
    {
        $surfaceTypes = SurfaceType::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($surfaceTypes);
    }
}
