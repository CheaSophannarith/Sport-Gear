<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarouselRequest;
use App\Http\Requests\UpdateCarouselRequest;
use App\Models\Carousel;
use Inertia\Inertia;

class CarouselController extends Controller
{
    /**
     * Display a listing of carousels.
     */
    public function index()
    {
        $carousels = Carousel::orderBy('order')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Ensure is_active is properly cast to boolean and add media URL
        $carousels->through(function ($carousel) {
            $carousel->is_active = (bool) $carousel->is_active;
            $carousel->image_url = $carousel->getFirstMediaUrl('image');
            return $carousel;
        });

        return Inertia::render('Admin/Carousels/Index', [
            'carousels' => $carousels,
        ]);
    }

    /**
     * Show the form for creating a new carousel.
     */
    public function create()
    {
        return Inertia::render('Admin/Carousels/Create');
    }

    /**
     * Store a newly created carousel in storage.
     */
    public function store(StoreCarouselRequest $request)
    {
        $validated = $request->validated();

        // Create carousel without image field
        $carousel = Carousel::create($validated);

        // Handle image upload with MediaLibrary
        if ($request->hasFile('image')) {
            $carousel->addMediaFromRequest('image')
                ->toMediaCollection('image');
        }

        return redirect()->route('admin.carousels.index')
            ->with('success', 'Carousel created successfully.');
    }

    /**
     * Display the specified carousel.
     */
    public function show(Carousel $carousel)
    {
        $carousel->image_url = $carousel->getFirstMediaUrl('image');

        return Inertia::render('Admin/Carousels/Show', [
            'carousel' => $carousel,
        ]);
    }

    /**
     * Show the form for editing the specified carousel.
     */
    public function edit(Carousel $carousel)
    {
        // Ensure is_active is properly cast to boolean and add media URL
        $carousel->is_active = (bool) $carousel->is_active;
        $carousel->image_url = $carousel->getFirstMediaUrl('image');

        return Inertia::render('Admin/Carousels/Edit', [
            'carousel' => $carousel,
        ]);
    }

    /**
     * Update the specified carousel in storage.
     */
    public function update(UpdateCarouselRequest $request, Carousel $carousel)
    {
        $validated = $request->validated();

        // Update carousel without image field
        $carousel->update($validated);

        // Handle image upload with MediaLibrary
        if ($request->hasFile('image')) {
            // Clear existing media and add new one (singleFile handles this automatically)
            $carousel->clearMediaCollection('image');
            $carousel->addMediaFromRequest('image')
                ->toMediaCollection('image');
        }

        return redirect()->route('admin.carousels.index')
            ->with('success', 'Carousel updated successfully.');
    }

    /**
     * Remove the specified carousel from storage.
     */
    public function destroy(Carousel $carousel)
    {
        // MediaLibrary will automatically delete associated media
        $carousel->delete();

        return redirect()->route('admin.carousels.index')
            ->with('success', 'Carousel deleted successfully.');
    }
}
