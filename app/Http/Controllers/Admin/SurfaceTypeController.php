<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurfaceTypeRequest;
use App\Http\Requests\UpdateSurfaceTypeRequest;
use App\Models\SurfaceType;
use Inertia\Inertia;

class SurfaceTypeController extends Controller
{
    /**
     * Display a listing of surface types.
     */
    public function index()
    {
        $surfaceTypes = SurfaceType::orderBy('name')
            ->paginate(15);

        // Ensure is_active is properly cast to boolean
        $surfaceTypes->through(function ($surfaceType) {
            $surfaceType->is_active = (bool) $surfaceType->is_active;
            return $surfaceType;
        });

        return Inertia::render('Admin/SurfaceTypes/Index', [
            'surfaceTypes' => $surfaceTypes,
        ]);
    }

    /**
     * Show the form for creating a new surface type.
     */
    public function create()
    {
        return Inertia::render('Admin/SurfaceTypes/Create');
    }

    /**
     * Store a newly created surface type in storage.
     */
    public function store(StoreSurfaceTypeRequest $request)
    {
        $validated = $request->validated();

        SurfaceType::create($validated);

        return redirect()->route('admin.surface-types.index')
            ->with('success', 'Surface type created successfully.');
    }

    /**
     * Display the specified surface type.
     */
    public function show(SurfaceType $surfaceType)
    {
        return Inertia::render('Admin/SurfaceTypes/Show', [
            'surfaceType' => $surfaceType,
        ]);
    }

    /**
     * Show the form for editing the specified surface type.
     */
    public function edit(SurfaceType $surfaceType)
    {
        // Ensure is_active is properly cast to boolean
        $surfaceType->is_active = (bool) $surfaceType->is_active;

        return Inertia::render('Admin/SurfaceTypes/Edit', [
            'surfaceType' => $surfaceType,
        ]);
    }

    /**
     * Update the specified surface type in storage.
     */
    public function update(UpdateSurfaceTypeRequest $request, SurfaceType $surfaceType)
    {
        $validated = $request->validated();

        $surfaceType->update($validated);

        return redirect()->route('admin.surface-types.index')
            ->with('success', 'Surface type updated successfully.');
    }

    /**
     * Remove the specified surface type from storage.
     */
    public function destroy(SurfaceType $surfaceType)
    {
        $surfaceType->delete();

        return redirect()->route('admin.surface-types.index')
            ->with('success', 'Surface type deleted successfully.');
    }
}
