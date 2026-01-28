<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;

class MyTeamController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Use the new method in User model to get all team member IDs
        $teamUserIds = $user->getTeamUserIds();
        
        // Remove self from the list for "My Team" view? 
        // Usually managers want to see their subordinates, not themselves.
        // Let's filter out the current user ID.
        $teamUserIds = array_diff($teamUserIds, [$user->id]);

        $members = User::whereIn('id', $teamUserIds)
            ->with(['roles', 'team'])
            ->orderBy('name')
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'initials' => collect(explode(' ', $member->name))->map(fn($s) => strtoupper(substr($s, 0, 1)))->take(2)->join(''),
                    'role' => $member->roles->pluck('name')->implode(', '),
                    'team' => $member->team ? $member->team->name : '-',
                    'is_active' => $member->is_active,
                ];
            });

        return Inertia::render('MyTeam/Index', [
            'members' => $members,
            'canEdit' => $user->hasRole('Sales Manager') || $user->hasRole('Admin'),
        ]);
    }
}
