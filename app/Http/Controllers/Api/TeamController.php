<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TeamController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $teams = Team::with(['teamManager', 'members'])
            ->withCount('members')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $teams,
        ]);
    }

    public function show(Team $team)
    {
        $team->load(['teamManager', 'members']);

        return response()->json([
            'success' => true,
            'data' => $team,
        ]);
    }
}
