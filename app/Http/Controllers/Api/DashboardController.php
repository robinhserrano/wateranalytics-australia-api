<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommissionCalculation;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get user's sales orders count
        $salesQuery = SalesOrder::query()->where('state', 'sale');
        if (!$user->hasPermissionTo('view-all-sales-orders')) {
            if ($user->hasPermissionTo('view-team-sales-orders')) {
                $teamUserIds = $this->getTeamUserIds($user);
                $odooUserIds = \App\Models\User::whereIn('id', $teamUserIds)
                    ->whereNotNull('odoo_user_id')
                    ->pluck('odoo_user_id');
                $salesQuery->whereIn('user_id', $odooUserIds);
            } else {
                $salesQuery->where('user_id', $user->odoo_user_id);
            }
        }

        // Get commission stats
        $commissionQuery = CommissionCalculation::query();
        if (!$user->hasPermissionTo('view-all-commissions')) {
            if ($user->hasPermissionTo('view-team-commissions')) {
                $teamUserIds = $this->getTeamUserIds($user);
                $commissionQuery->whereIn('user_id', $teamUserIds);
            } else {
                $commissionQuery->where('user_id', $user->id);
            }
        }

        $stats = [
            'sales_orders' => [
                'total' => $salesQuery->count(),
                'this_month' => (clone $salesQuery)->whereMonth('create_date', now()->month)->count(),
                'total_amount' => $salesQuery->sum('amount_total'),
            ],
            'commissions' => [
                'total' => $commissionQuery->sum('final_commission'),
                'pending' => (clone $commissionQuery)->where('status', 'pending')->sum('final_commission'),
                'approved' => (clone $commissionQuery)->where('status', 'approved')->sum('final_commission'),
                'paid' => (clone $commissionQuery)->where('status', 'paid')->sum('final_commission'),
                'this_month' => (clone $commissionQuery)->whereMonth('created_at', now()->month)->sum('final_commission'),
            ],
            'recent_orders' => $salesQuery->with('commissionCalculation.user')
                ->orderBy('create_date', 'desc')
                ->limit(5)
                ->get(),
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
