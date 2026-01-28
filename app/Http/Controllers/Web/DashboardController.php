<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CommissionCalculation;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // 1. Sales Orders Query Base
        $salesQuery = SalesOrder::query()->where('state', 'sale');
        
        // Apply Role Filters
        if (!$user->can('view-all-sales-orders')) {
            if ($user->can('view-team-sales-orders')) {
                // Team Manager view
                $teamUserIds = $this->getTeamUserIds($user);
                
                $salesQuery->where(function($q) use ($teamUserIds) {
                    // Match by Odoo User ID
                    $odooUserIds = \App\Models\User::whereIn('id', $teamUserIds)
                        ->whereNotNull('odoo_user_id')
                        ->pluck('odoo_user_id')
                        ->toArray();
                        
                    if (!empty($odooUserIds)) {
                        $q->whereIn('user_id', $odooUserIds);
                    }
                    
                    // Or match by Name
                    $userNames = \App\Models\User::whereIn('id', $teamUserIds)
                        ->pluck('name')
                        ->toArray();
                        
                    if (!empty($userNames)) {
                        $q->orWhereIn('user_name', $userNames);
                    }
                });
            } else {
                // Salesperson view
                $salesQuery->where(function ($q) use ($user) {
                    if ($user->odoo_user_id) {
                        $q->where('user_id', $user->odoo_user_id);
                    }
                    if ($user->name) {
                        $q->orWhere('user_name', $user->name);
                    }
                });
            }
        }

        // 2. Commission Query Base
        $commissionQuery = CommissionCalculation::query();
        if (!$user->can('view-all-commissions')) {
            if ($user->can('view-team-commissions')) {
                $teamUserIds = $this->getTeamUserIds($user);
                $commissionQuery->whereIn('user_id', $teamUserIds);
            } else {
                $commissionQuery->where('user_id', $user->id);
            }
        }

        // 3. New Orders Logic
        $newOrdersCount = 0;
        if ($user->last_login_at) {
            $newOrdersCount = (clone $salesQuery)
                ->where('created_at', '>', $user->last_login_at)
                ->count();
        }

        // 4. Compile Stats
        
        // Paid Query: Based on Odoo field
        $paidStatsQuery = (clone $commissionQuery)->whereHas('salesOrder', function ($q) {
            $q->where('x_studio_commission_paid', true);
        });

        // Pending Query: Status is pending AND NOT paid in Odoo
        $pendingStatsQuery = (clone $commissionQuery)->where('status', 'pending')
            ->whereDoesntHave('salesOrder', function ($q) {
                $q->where('x_studio_commission_paid', true);
            });

        // Approved Query: Status is approved AND NOT paid in Odoo
        $approvedStatsQuery = (clone $commissionQuery)->where('status', 'approved')
            ->whereDoesntHave('salesOrder', function ($q) {
                $q->where('x_studio_commission_paid', true);
            });

        $stats = [
            'overview' => [
                'new_orders_count' => $newOrdersCount,
                'last_login' => $user->last_login_at,
            ],
            'commissions' => [
                'pending' => $pendingStatsQuery->sum('final_commission'),
                'pending_count' => $pendingStatsQuery->count(),
                'approved' => $approvedStatsQuery->sum('final_commission'),
                'approved_count' => $approvedStatsQuery->count(),
                'paid' => $paidStatsQuery->sum('final_commission'),
                'paid_count' => $paidStatsQuery->count(),
            ],
            'recent_orders' => $salesQuery->with(['commissionCalculation.user', 'commissionCalculation'])
                ->orderBy('create_date', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'name' => $order->name,
                        'partner_name' => $order->partner_name,
                        'amount_total' => $order->amount_total,
                        'create_date' => $order->create_date,
                        'state' => $order->state,
                        'status' => $order->commissionCalculation->status ?? 'pending',
                        'commission' => $order->commissionCalculation->final_commission ?? 0,
                        'owner' => $order->commissionCalculation->user->name ?? $order->user_name,
                    ];
                }),
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'isManager' => $user->hasRole(['Admin', 'Manager']), // Simple role check
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
            $team = \App\Models\Team::find($user->team_id); // Explicit load
            if ($team && $team->team_manager_id === $user->id) {
                $teamMemberIds = $team->members()->pluck('id')->toArray();
                $userIds = array_merge($userIds, $teamMemberIds);
            }
        }
        
        return array_unique($userIds);
    }
}
