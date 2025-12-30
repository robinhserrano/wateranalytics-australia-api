<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TeamController extends Controller
{
    use AuthorizesRequests;
    
    public function index()
    {
        $this->authorize('manage-teams');

        $teams = Team::with(['teamManager', 'members'])
            ->withCount('members')
            ->orderBy('name')
            ->get();

        return Inertia::render('Teams/Index', [
            'teams' => $teams,
        ]);
    }

    public function create()
    {
        $this->authorize('manage-teams');

        $managers = User::role(['Admin', 'Sales Manager', 'Sales Team Manager'])
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Teams/Create', [
            'managers' => $managers,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('manage-teams');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'team_manager_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $team = Team::create($validated);

        return redirect()->route('teams.index')
            ->with('success', 'Team created successfully.');
    }

    public function edit(Team $team)
    {
        $this->authorize('manage-teams');

        $team->load(['teamManager', 'members']);

        $managers = User::role(['Admin', 'Sales Manager', 'Sales Team Manager'])
            ->orderBy('name')
            ->get(['id', 'name']);

        $availableUsers = User::whereDoesntHave('team')
            ->orWhere('team_id', $team->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'team_id']);

        return Inertia::render('Teams/Edit', [
            'team' => $team,
            'managers' => $managers,
            'availableUsers' => $availableUsers,
        ]);
    }

    public function update(Request $request, Team $team)
    {
        $this->authorize('manage-teams');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'team_manager_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $team->update($validated);

        return redirect()->route('teams.index')
            ->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team)
    {
        $this->authorize('manage-teams');

        // Remove team association from users
        $team->members()->update(['team_id' => null]);

        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Team deleted successfully.');
    }

    public function addMember(Request $request, Team $team)
    {
        $this->authorize('manage-team-members');

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->update(['team_id' => $team->id]);

        return back()->with('success', 'Member added to team successfully.');
    }

    public function removeMember(Team $team, User $user)
    {
        $this->authorize('manage-team-members');

        $user->update(['team_id' => null]);

        return back()->with('success', 'Member removed from team successfully.');
    }
}
