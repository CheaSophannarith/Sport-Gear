<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\League;
use App\Models\Team;
use Inertia\Inertia;

class TeamController extends Controller
{
    /**
     * Display a listing of teams.
     */
    public function index()
    {
        $teams = Team::with('league')
            ->orderBy('name')
            ->paginate(15);

        // Ensure is_active is properly cast to boolean and add media URL
        $teams->through(function ($team) {
            $team->is_active = (bool) $team->is_active;
            $team->logo_url = $team->getFirstMediaUrl('logo');
            return $team;
        });

        return Inertia::render('Admin/Teams/Index', [
            'teams' => $teams,
        ]);
    }

    /**
     * Show the form for creating a new team.
     */
    public function create()
    {
        $leagues = League::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Teams/Create', [
            'leagues' => $leagues,
        ]);
    }

    /**
     * Store a newly created team in storage.
     */
    public function store(StoreTeamRequest $request)
    {
        $validated = $request->validated();

        // Create team without logo field
        $team = Team::create($validated);

        // Handle logo upload with MediaLibrary
        if ($request->hasFile('logo')) {
            $team->addMediaFromRequest('logo')
                ->toMediaCollection('logo');
        }

        return redirect()->route('admin.teams.index')
            ->with('success', 'Team created successfully.');
    }

    /**
     * Display the specified team.
     */
    public function show(Team $team)
    {
        $team->load('league');
        $team->logo_url = $team->getFirstMediaUrl('logo');

        return Inertia::render('Admin/Teams/Show', [
            'team' => $team,
        ]);
    }

    /**
     * Show the form for editing the specified team.
     */
    public function edit(Team $team)
    {
        $leagues = League::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Ensure is_active is properly cast to boolean and add media URL
        $team->is_active = (bool) $team->is_active;
        $team->logo_url = $team->getFirstMediaUrl('logo');

        return Inertia::render('Admin/Teams/Edit', [
            'team' => $team,
            'leagues' => $leagues,
        ]);
    }

    /**
     * Update the specified team in storage.
     */
    public function update(UpdateTeamRequest $request, Team $team)
    {
        $validated = $request->validated();

        // Update team without logo field
        $team->update($validated);

        // Handle logo upload with MediaLibrary
        if ($request->hasFile('logo')) {
            // Clear existing media and add new one (singleFile handles this automatically)
            $team->clearMediaCollection('logo');
            $team->addMediaFromRequest('logo')
                ->toMediaCollection('logo');
        }

        return redirect()->route('admin.teams.index')
            ->with('success', 'Team updated successfully.');
    }

    /**
     * Remove the specified team from storage.
     */
    public function destroy(Team $team)
    {
        // MediaLibrary will automatically delete associated media
        $team->delete();

        return redirect()->route('admin.teams.index')
            ->with('success', 'Team deleted successfully.');
    }
}
