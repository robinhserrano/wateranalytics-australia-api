<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

/**
 * @group Sales Orders
 *
 * APIs for managing sales orders
 */
class SalesOrderController extends Controller
{
    /**
     * List Sales Orders
     *
     * Get a paginated list of sales orders with role-based filtering.
     *
     * @queryParam page int The page number. Example: 1
     * @queryParam per_page int The number of items per page. Example: 15
     * @queryParam search string Search term for order name, customer, or salesperson. Example: SO001
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = SalesOrder::query();

        // Apply role-based filtering (same logic as web)
        if ($user->hasPermissionTo('view-all-sales-orders')) {
            // Admin - see all orders
        } elseif ($user->hasPermissionTo('view-team-sales-orders')) {
            // Manager - see team orders
            $teamUserIds = $this->getTeamUserIds($user);
            
            $query->where(function ($q) use ($teamUserIds) {
                $odooUserIds = \App\Models\User::whereIn('id', $teamUserIds)
                    ->whereNotNull('odoo_user_id')
                    ->pluck('odoo_user_id')
                    ->toArray();
                
                if (!empty($odooUserIds)) {
                    $q->whereIn('user_id', $odooUserIds);
                }
                
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

        $perPage = $request->input('per_page', 15);
        $orders = $query->with(['lines', 'commissionCalculation'])
            ->orderBy('create_date', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Get Sales Order
     *
     * Get a specific sales order with full details including lines and commission.
     *
     * @urlParam id int required The ID of the sales order. Example: 1
     */
    public function show($id)
    {
        $order = SalesOrder::with([
            'lines.product',
            'lines.landingPrice',
            'commissionCalculation.user',
            'commissionCalculation.salesManager',
        ])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Get team user IDs recursively
     */
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
