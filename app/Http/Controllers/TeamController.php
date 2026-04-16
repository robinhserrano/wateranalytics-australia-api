<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TeamController extends Controller
{
    use AuthorizesRequests;
    
    public function index()
    {
        $this->authorize('manage-teams');

        $teams = Team::with(['teamManager.salesManager.roles', 'teamManager.team', 'members'])
            ->withCount('members')
            ->addSelect([
                'latest_sale_date' => SalesOrder::query()
                    ->select('create_date')
                    ->whereIn('salesperson_partner_id', function ($query) {
                        $query->select('odoo_id')
                            ->from('contacts')
                            ->join('users', 'users.id', '=', 'contacts.user_id')
                            ->whereColumn('users.team_id', 'teams.id');
                    })
                    ->orderBy('create_date', 'desc')
                    ->limit(1),
            ])
            ->orderBy('name')
            ->get()
            ->map(function ($team) {
                $latestSaleDate = $team->latest_sale_date 
                    ? \Illuminate\Support\Carbon::parse($team->latest_sale_date) 
                    : null;
                
                $sixMonthsAgo = now()->subMonths(6);
                $isSalesActive = $latestSaleDate && $latestSaleDate->greaterThan($sixMonthsAgo);

                $managerHierarchy = null;
                
                // Be more explicit about finding the manager's hierarchy
                $tm = $team->teamManager;
                if ($tm) {
                    // Determine the parent (manager) for the hierarchy
                    $sm = $tm->salesManager;
                    
                    // Fallback: If no direct sales manager, use the Team Manager of the team they BELONG to
                    if (!$sm && $tm->team_id && $tm->team) {
                        $teamManagerId = $tm->team->team_manager_id;
                        // Only follow if it's not themselves
                        if ($teamManagerId && $teamManagerId !== $tm->id) {
                            $sm = User::with('roles')->find($teamManagerId);
                        }
                    }

                    if ($sm) {
                        $managerHierarchy = [
                            'id' => $sm->id,
                            'name' => $sm->name,
                            'initials' => collect(explode(' ', (string) $sm->name))
                                ->filter()
                                ->map(fn ($s) => mb_strtoupper(mb_substr($s, 0, 1, 'UTF-8'), 'UTF-8'))
                                ->take(2)
                                ->join(''),
                            'role' => $sm->roles->pluck('name')->first() ?? 'Sales Manager',
                        ];
                    }
                }

                return [
                    'id' => $team->id,
                    'name' => $team->name,
                    'is_active' => $team->is_active,
                    'members_count' => $team->members_count,
                    'team_manager' => $tm ? [
                        'id' => $tm->id,
                        'name' => $tm->name,
                        'role' => $tm->roles->pluck('name')->first() ?? '-',
                    ] : null,
                    'manager_hierarchy' => $managerHierarchy,
                    'latest_sale_date' => $latestSaleDate ? $latestSaleDate->toIso8601String() : null,
                    'is_sales_active' => $isSalesActive,
                ];
            });

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

        $team->load(['teamManager', 'members.roles']);

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

    public function hierarchy(Request $request)
    {
        $this->authorize('manage-teams');

        $focusId = $request->query('focus_id');
        $allowedUserIds = null;

        if ($focusId) {
            $focusUser = User::find($focusId);
            if ($focusUser) {
                $ancestors = $focusUser->getAncestorUserIds();
                $descendants = $focusUser->getTeamUserIds();
                $allowedUserIds = array_unique(array_merge([$focusUser->id], $ancestors, $descendants));
            }
        }

        $query = User::with(['roles', 'team'])
            ->where('is_active', true);

        if ($allowedUserIds !== null) {
            $query->whereIn('id', $allowedUserIds);
        }

        $users = $query->get()
            ->map(function ($user) {
                // Determine the parent (manager) for the hierarchy
                $parentId = $user->sales_manager_id;
                
                // Fallback: If no direct sales manager, use the Team Manager
                if (!$parentId && $user->team_id && $user->team) {
                    $teamManagerId = $user->team->team_manager_id;
                    // Only set as parent if it's not the user themselves
                    if ($teamManagerId && $teamManagerId !== $user->id) {
                        $parentId = $teamManagerId;
                    }
                }

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'initials' => collect(explode(' ', (string) $user->name))
                        ->filter()
                        ->map(fn ($s) => mb_strtoupper(mb_substr($s, 0, 1, 'UTF-8'), 'UTF-8'))
                        ->take(2)
                        ->join(''),
                    'role' => $user->roles->pluck('name')->first() ?? '-',
                    'parent_id' => $parentId,
                    'team' => $user->team ? $user->team->name : null,
                ];
            });

        return Inertia::render('Teams/Hierarchy', [
            'users' => $users,
            'focusId' => $focusId ? (int) $focusId : null,
        ]);
    }
}
