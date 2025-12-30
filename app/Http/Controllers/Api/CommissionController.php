<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommissionCalculation;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    /**
     * List commission calculations
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = CommissionCalculation::query();

        // Role-based filtering
        if (!$user->hasPermissionTo('view-all-commissions')) {
            if ($user->hasPermissionTo('view-team-commissions')) {
                // Manager - see team commissions
                $teamUserIds = $this->getTeamUserIds($user);
                $query->whereIn('user_id', $teamUserIds);
            } else {
                // Salesperson - see own commissions
                $query->where('user_id', $user->id);
            }
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', 15);
        $commissions = $query->with(['salesOrder', 'user', 'salesManager'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $commissions->items(),
            'meta' => [
                'current_page' => $commissions->currentPage(),
                'last_page' => $commissions->lastPage(),
                'per_page' => $commissions->perPage(),
                'total' => $commissions->total(),
            ],
        ]);
    }

    /**
     * Get commission details
     */
    public function show($id)
    {
        $commission = CommissionCalculation::with([
            'salesOrder.lines',
            'user',
            'salesManager',
            'adjustments.adjuster',
            'approvals.approver',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $commission,
        ]);
    }

    /**
     * Get user commission statistics
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        
        $stats = [
            'total_commissions' => CommissionCalculation::where('user_id', $user->id)->sum('final_commission'),
            'pending_count' => CommissionCalculation::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approved_count' => CommissionCalculation::where('user_id', $user->id)->where('status', 'approved')->count(),
            'paid_count' => CommissionCalculation::where('user_id', $user->id)->where('status', 'paid')->count(),
            'this_month' => CommissionCalculation::where('user_id', $user->id)
                ->whereMonth('created_at', now()->month)
                ->sum('final_commission'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    private function getTeamUserIds($user): array
    {
        $userIds = [$user->id];
        $directReports = \App\Models\User::where('sales_manager_id', $user->id)->get();
        
        foreach ($directReports as $report) {
            $userIds = array_merge($userIds, $this->getTeamUserIds($report));
        }
        
        if ($user->team_id) {
            $team = $user->team;
            if ($team && $team->team_manager_id === $user->id) {
                $teamMemberIds = $team->members()->pluck('id')->toArray();
                $userIds = array_merge($userIds, $teamMemberIds);
            }
        }
        
        return array_unique($userIds);
    }
}
