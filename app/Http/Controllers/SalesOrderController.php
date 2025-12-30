<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalesOrderController extends Controller
{
    protected $calculator;

    public function __construct(\App\Services\CommissionCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $query = SalesOrder::query();

        // Apply role-based filtering
        if ($user->hasPermissionTo('view-all-sales-orders')) {
            // Admin and Account Officer - see all orders
            // No filter needed
        } elseif ($user->hasPermissionTo('view-team-sales-orders')) {
            // Sales Manager or Sales Team Manager - see team orders
            $teamUserIds = $this->getTeamUserIds($user);
            
            // Filter by user_id (Odoo user ID) or user_name
            $query->where(function ($q) use ($teamUserIds, $user) {
                // Match by Odoo user IDs
                $odooUserIds = \App\Models\User::whereIn('id', $teamUserIds)
                    ->whereNotNull('odoo_user_id')
                    ->pluck('odoo_user_id')
                    ->toArray();
                
                if (!empty($odooUserIds)) {
                    $q->whereIn('user_id', $odooUserIds);
                }
                
                // Also match by user names
                $userNames = \App\Models\User::whereIn('id', $teamUserIds)
                    ->pluck('name')
                    ->toArray();
                
                if (!empty($userNames)) {
                    $q->orWhereIn('user_name', $userNames);
                }
            });
        } else {
            // Salesperson - see only own orders
            $query->where(function ($q) use ($user) {
                if ($user->odoo_user_id) {
                    $q->where('user_id', $user->odoo_user_id);
                }
                if ($user->name) {
                    $q->orWhere('user_name', $user->name);
                }
            });
        }

        // Apply search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('partner_name', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        $salesOrders = $query->with('commissionCalculation')
            ->orderBy('create_date', 'desc')
            ->paginate(80)
            ->withQueryString();

        // Determine view scope for UI
        $viewScope = 'My Orders';
        if ($user->hasPermissionTo('view-all-sales-orders')) {
            $viewScope = 'All Orders';
        } elseif ($user->hasPermissionTo('view-team-sales-orders')) {
            $viewScope = 'Team Orders';
        }

        return Inertia::render('SalesOrders/Index', [
            'salesOrders' => $salesOrders,
            'filters' => $request->only(['search']),
            'viewScope' => $viewScope,
        ]);
    }

    /**
     * Get all user IDs in the team hierarchy (recursive)
     */
    private function getTeamUserIds($user): array
    {
        $userIds = [$user->id];
        
        // Get direct reports (users who have this user as sales_manager_id)
        $directReports = \App\Models\User::where('sales_manager_id', $user->id)->get();
        
        foreach ($directReports as $report) {
            // Recursively get their team members
            $userIds = array_merge($userIds, $this->getTeamUserIds($report));
        }
        
        // Also get team members if user is a team manager
        if ($user->team_id) {
            $team = $user->team;
            if ($team && $team->team_manager_id === $user->id) {
                $teamMemberIds = $team->members()->pluck('id')->toArray();
                $userIds = array_merge($userIds, $teamMemberIds);
            }
        }
        
        return array_unique($userIds);
    }

    public function show(SalesOrder $salesOrder)
    {
        $calculationError = null;

        // Auto-calculate commission if missing and possible
        if (!$salesOrder->commissionCalculation) {
            try {
                // Only attempt if we have enough info (e.g. user assigned)
                // The calculator handles validation internally usually, or throws exception
                $this->calculator->calculateCommission($salesOrder);
                $salesOrder->refresh(); // Refresh to get the new relation
            } catch (\Exception $e) {
                // Log warning and capture error for UI
                \Log::warning("Auto-calculation failed for order {$salesOrder->id}: " . $e->getMessage());
                $calculationError = $e->getMessage();
            }
        }

        $salesOrder->load([
            'lines.product',
            'lines.landingPrice',
            'commissionCalculation.user',
            'commissionCalculation.salesManager',
            'commissionCalculation.adjustments.adjuster',
            'commissionCalculation.approvals.approver',
        ]);

        return Inertia::render('SalesOrders/Show', [
            'salesOrder' => $salesOrder,
            'calculationError' => $calculationError,
        ]);
    }
}
