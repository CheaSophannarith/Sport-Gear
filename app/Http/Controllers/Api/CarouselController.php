<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carousel;
use Illuminate\Http\JsonResponse;

class CarouselController extends Controller
{
    /**
     * Get all active carousel items for homepage
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $carousels = Carousel::where('is_active', true)
            ->orderBy('order')
            ->get();

        return response()->json($carousels->map(fn($carousel) => [
            'id' => $carousel->id,
            'title' => $carousel->title,
            'description' => $carousel->description,
            'link' => $carousel->link,
            'image_url' => $carousel->getFirstMediaUrl('image'),
            'order' => $carousel->order,
        ]));
    }
}
