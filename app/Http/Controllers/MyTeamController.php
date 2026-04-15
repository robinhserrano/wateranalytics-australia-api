<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Collection;

class MyTeamController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = auth()->user();

        $members = $this->membersForManagerView($user);

        return Inertia::render('MyTeam/Index', [
            'members' => $members,
            'canEdit' => $user->hasRole('Sales Manager') || $user->hasRole('Admin'),
            'preview' => null,
        ]);
    }

    /**
     * Admin: show the same My Team data the team's manager would see.
     */
    public function previewAsManager(Team $team)
    {
        $this->authorize('manage-teams');

        $team->load('teamManager');

        $manager = $team->teamManager;

        if (!$manager) {
            return Inertia::render('MyTeam/Index', [
                'members' => collect(),
                'canEdit' => false,
                'preview' => [
                    'teamId' => $team->id,
                    'teamName' => $team->name,
                    'managerName' => null,
                    'message' => 'This team has no assigned manager. Assign a team manager to mirror their My Team view.',
                ],
            ]);
        }

        $members = $this->membersForManagerView($manager);

        $canEdit = $manager->hasRole('Sales Manager') || $manager->hasRole('Admin');

        return Inertia::render('MyTeam/Index', [
            'members' => $members,
            'canEdit' => $canEdit,
            'preview' => [
                'teamId' => $team->id,
                'teamName' => $team->name,
                'managerName' => $manager->name,
                'message' => null,
            ],
        ]);
    }

    private function membersForManagerView(User $manager): Collection
    {
        $teamUserIds = $manager->getTeamUserIds();
        $teamUserIds = array_diff($teamUserIds, [$manager->id]);

        return User::whereIn('id', $teamUserIds)
            ->with(['roles', 'team'])
            ->select('users.*')
            ->addSelect([
                'latest_sale_date' => SalesOrder::query()
                    ->select('create_date')
                    ->whereIn('salesperson_partner_id', function ($query) {
                        $query->select('odoo_id')
                            ->from('contacts')
                            ->whereColumn('contacts.user_id', 'users.id');
                    })
                    ->orderBy('create_date', 'desc')
                    ->limit(1),
            ])
            ->orderBy('name')
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'initials' => collect(explode(' ', $member->name))->map(fn ($s) => strtoupper(substr($s, 0, 1)))->take(2)->join(''),
                    'role' => $member->roles->pluck('name')->implode(', '),
                    'team' => $member->team ? $member->team->name : '-',
                    'is_active' => $member->is_active,
                    'latest_sale_date' => $member->latest_sale_date
                        ? \Illuminate\Support\Carbon::parse($member->latest_sale_date)->toIso8601String()
                        : null,
                ];
            });
    }
}
