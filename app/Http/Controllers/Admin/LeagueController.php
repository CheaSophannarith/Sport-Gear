<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeagueRequest;
use App\Http\Requests\UpdateLeagueRequest;
use App\Models\League;
use Inertia\Inertia;

class LeagueController extends Controller
{
    /**
     * Display a listing of leagues.
     */
    public function index()
    {
        $leagues = League::orderBy('name')
            ->paginate(15);

        // Ensure is_active is properly cast to boolean and add media URL
        $leagues->through(function ($league) {
            $league->is_active = (bool) $league->is_active;
            $league->logo_url = $league->getFirstMediaUrl('logo');
            return $league;
        });

        return Inertia::render('Admin/Leagues/Index', [
            'leagues' => $leagues,
        ]);
    }

    /**
     * Show the form for creating a new league.
     */
    public function create()
    {
        return Inertia::render('Admin/Leagues/Create');
    }

    /**
     * Store a newly created league in storage.
     */
    public function store(StoreLeagueRequest $request)
    {
        $validated = $request->validated();

        // Create league without logo field
        $league = League::create($validated);

        // Handle logo upload with MediaLibrary
        if ($request->hasFile('logo')) {
            $league->addMediaFromRequest('logo')
                ->toMediaCollection('logo');
        }

        return redirect()->route('admin.leagues.index')
            ->with('success', 'League created successfully.');
    }

    /**
     * Display the specified league.
     */
    public function show(League $league)
    {
        $league->logo_url = $league->getFirstMediaUrl('logo');

        return Inertia::render('Admin/Leagues/Show', [
            'league' => $league,
        ]);
    }

    /**
     * Show the form for editing the specified league.
     */
    public function edit(League $league)
    {
        // Ensure is_active is properly cast to boolean and add media URL
        $league->is_active = (bool) $league->is_active;
        $league->logo_url = $league->getFirstMediaUrl('logo');

        return Inertia::render('Admin/Leagues/Edit', [
            'league' => $league,
        ]);
    }

    /**
     * Update the specified league in storage.
     */
    public function update(UpdateLeagueRequest $request, League $league)
    {
        $validated = $request->validated();

        // Update league without logo field
        $league->update($validated);

        // Handle logo upload with MediaLibrary
        if ($request->hasFile('logo')) {
            // Clear existing media and add new one (singleFile handles this automatically)
            $league->clearMediaCollection('logo');
            $league->addMediaFromRequest('logo')
                ->toMediaCollection('logo');
        }

        return redirect()->route('admin.leagues.index')
            ->with('success', 'League updated successfully.');
    }

    /**
     * Remove the specified league from storage.
     */
    public function destroy(League $league)
    {
        // MediaLibrary will automatically delete associated media
        $league->delete();

        return redirect()->route('admin.leagues.index')
            ->with('success', 'League deleted successfully.');
    }
}
